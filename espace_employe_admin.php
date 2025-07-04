<?php
// espace_employe_admin.php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['employe', 'admin'])) {
    header("Location: login.php");
    exit;
}


$role = $_SESSION['role'];


// --- EMPLOYE ---
if ($role === 'employe') {
    // Validation/refus d'avis
    if (isset($_POST['valider_avis'])) {
        $stmt = $pdo->prepare("UPDATE avis SET statut = 'valide' WHERE avis_id = ?");
        $stmt->execute([$_POST['valider_avis']]);
        $message_validation="Commentaire validé";
    } elseif (isset($_POST['refuser_avis'])) {
        $stmt = $pdo->prepare("UPDATE avis SET statut = 'refuse' WHERE avis_id = ?");
        $stmt->execute([$_POST['refuser_avis']]);
        $message_refus="Commentaire refusé";
    }

    // Avis à valider
$avis = $pdo->query("
    SELECT 
        a.avis_id, 
        a.commentaire, 
        a.note, 
        passager.pseudo AS passager_pseudo,
        chauffeur.pseudo AS chauffeur_pseudo
    FROM avis a
    JOIN participation p ON p.avis_id = a.avis_id
    JOIN utilisateur passager ON p.utilisateur_id = passager.utilisateur_id
    JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
    JOIN utilisateur chauffeur ON c.utilisateur_id_chauffeur = chauffeur.utilisateur_id
    WHERE a.statut = 'en_attente'
")->fetchAll();
    // Covoiturages problématiques (avis note <= 2)
    $problemes = $pdo->query("SELECT c.covoiturage_id, u1.pseudo AS chauffeur, u1.email AS email_chauffeur, u2.pseudo AS passager, u2.email AS email_passager, c.date_depart, c.date_arrivee, c.lieu_depart, c.lieu_arrivee
    FROM avis a
    JOIN utilisateur u2 ON a.id_utilisateur = u2.utilisateur_id
    JOIN participation p ON a.avis_id = p.avis_id
    JOIN covoiturage c ON c.covoiturage_id = p.covoiturage_id
    JOIN utilisateur u1 ON u1.utilisateur_id = c.utilisateur_id_chauffeur
    WHERE a.note <= 2 AND a.statut = 'valide'")->fetchAll();
} 

// --- ADMINISTRATEUR ---
if ($role === 'admin') {
    // Création employé
    if (isset($_POST['creer_employe'])) {
        $email = $_POST['email'];
        $pseudo=$_POST['pseudo'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, password, pseudo, role_id) VALUES ('Employe', 'Auto', ?, ?,?, (SELECT role_id FROM role WHERE libelle = 'employe'))");
        $stmt->execute([$email, $password,$pseudo]);
        $message="Le compte employé a bien été crée";
    }

    // Suspension de compte
if (isset($_POST['suspendre'])) {
    // Récupérer les infos de l'utilisateur avant suppression
    $stmt = $pdo->prepare("SELECT email, prenom FROM utilisateur WHERE utilisateur_id = ?");
    $stmt->execute([$_POST['suspendre']]);
    $user = $stmt->fetch();

    if ($user) {
        // Envoi du mail de suspension via PHPMailer
        require 'vendor/autoload.php'; // Assure-toi d'avoir PHPMailer installé via Composer

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuration serveur SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com';  // à adapter
            $mail->SMTPAuth   = true;
            $mail->Username   = 'votre_email@example.com'; // à adapter
            $mail->Password   = 'votre_mot_de_passe';       // à adapter
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Destinataire
            $mail->setFrom('votre_email@example.com', 'Ecordie');
            $mail->addAddress($user['email'], $user['prenom']);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Votre compte a été suspendu';
            $mail->Body    = "Bonjour {$user['prenom']},<br><br>Nous vous informons que votre compte a été supprimé de notre plateforme.<br><br>L'équipe Ecordie.";

            $mail->send();
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi du mail : {$mail->ErrorInfo}";
        }

        // Suppression du compte utilisateur
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE utilisateur_id = ?");
        $stmt->execute([$_POST['suspendre']]);
        $message2="Compte suspendue et mail envoyé";
    }
}

    // Stats
    $covoituragesParJour = $pdo->query("SELECT date_depart, COUNT(*) AS total FROM covoiturage GROUP BY date_depart ORDER BY date_depart DESC LIMIT 10")->fetchAll();
    $creditsParJour = $pdo->query("SELECT date_depart, SUM(prix_personne) AS total FROM covoiturage GROUP BY date_depart ORDER BY date_depart DESC LIMIT 10")->fetchAll();
    $totalCredits = $pdo->query("SELECT SUM(prix_personne) AS total FROM covoiturage")->fetchColumn();
    $utilisateurs = $pdo->query("SELECT utilisateur_id, pseudo, email FROM utilisateur")->fetchAll();
}
?>

<!-- Affichage HTML adapté au rôle -->
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Espace <?= $role ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
  <div class="col-auto">
    <a href="In  dex.php" class="btn btn-outline-success me-3">Retour</a>


    <h1 class="mb-4 ms-5 d-inline align-middle">Bienvenue dans l'espace <?= $role ?></h1>
  </div>
  <?php if ($role === 'employe'): ?>
  <h2>Validation des avis</h2>
  <?php foreach ($avis as $a): ?>
  <div class="border p-2 mb-2">
    <p>Pseudo du passager </p> <strong>: <?= htmlspecialchars($a['passager_pseudo']) ?></strong> - Note:
    <?= $a['note'] ?><br>
    <p><?= htmlspecialchars($a['commentaire']) ?></p>
    <p>Chauffeur concerné : <?= htmlspecialchars($a['chauffeur_pseudo']) ?></p>
    <p>
    <form method="post" class="d-inline">
      <button name="valider_avis" value="<?= $a['avis_id'] ?>" class="btn btn-success btn-sm">Valider</button>

      <button name="refuser_avis" value="<?= $a['avis_id'] ?>" class="btn btn-danger btn-sm">Refuser</button>
    </form>
  </div>
  <?php endforeach; ?>

  <h2>Covoiturages problématiques</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Chauffeur</th>
        <th>Passager</th>
        <th>Date Départ</th>
        <th>Date Arrivée</th>
        <th>Départ</th>
        <th>Arrivée</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($problemes as $p): ?>
      <tr>
        <td><?= $p['covoiturage_id'] ?></td>
        <td><?= $p['chauffeur'] ?> (<?= $p['email_chauffeur'] ?>)</td>
        <td><?= $p['passager'] ?> (<?= $p['email_passager'] ?>)</td>
        <td><?= $p['date_depart'] ?></td>
        <td><?= $p['date_arrivee'] ?></td>
        <td><?= $p['lieu_depart'] ?></td>
        <td><?= $p['lieu_arrivee'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <?php if ($role === 'admin'): ?>
  <h2>Créer un compte employé</h2>
  <div class="d-flex justify-content-center mt-4">
    <form method="post" class="mb-4">
      <input type="hidden" name="creer_employe" value="1">
      <input type="text" name="pseudo" class="form-control mb-2" placeholder="Pseudo" required>
      <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
      <input type="password" name="password" class="form-control mb-2" placeholder="Mot de passe" required>
      <button class="btn btn-primary w-100">Créer</button>
    </form>
  </div>

  <?php if (!empty($message)): ?>
  <div id="success-message" class="text-center text-success fw-bold">
    <?= htmlspecialchars($message) ?>
  </div>
  <script>
  setTimeout(() => {
    const msg = document.getElementById('success-message');
    if (msg) {
      msg.style.display = 'none';
    }
  }, 5000); // 5000 ms = 5 sec
  </script>
  <?php endif; ?>

  <h2>Statistiques</h2>

  <p>Total des crédits gagnés : <strong><?= $totalCredits ?> €</strong></p>
  <div class="d-flex justify-content-center mt-4">
    <div id="charts" class="row">
      <div class="col-md-6">
        <h5>Covoiturages par jour</h5>
        <ul>
          <?php foreach ($covoituragesParJour as $c): ?>
          <li><?= $c['date_depart'] ?> : <?= $c['total'] ?> trajets</li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-md-6">
        <h5>Crédits gagnés par jour</h5>
        <ul>
          <?php foreach ($creditsParJour as $c): ?>
          <li><?= $c['date_depart'] ?> : <?= $c['total'] ?> €</li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <h2>Suspendre un compte</h2>
  <div class="d-flex justify-content-center mt-4">
    <form method="post"> <select name="suspendre" class="form-select mb-2">
        <?php foreach ($utilisateurs as $u): ?>
        <option value="<?= $u['utilisateur_id'] ?>"><?= $u['pseudo'] ?> - <?= $u['email'] ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-danger w-100">Suspendre</button>
    </form>
  </div>
  <?php endif; ?> <?php if (!empty($message2)): ?> <div id="success-message" class="text-center text-success fw-bold">
    <?= htmlspecialchars($message2) ?>
  </div>
  <script>
  setTimeout(() => {
    const msg = document.getElementById('success-message');
    if (msg) {
      msg.style.display = 'none';
    }
  }, 5000); // 5000 ms = 5 sec
  </script>
  <?php endif; ?>
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");

  h1,
  h2 {
    font-family: "Neucha", "Outfit", serif;
    font-weight: 700;
    color: #7fa72a;
    margin-bottom: 40px;
    text-align: center;
  }

  h2 {
    margin-top: 5vh;
    ;
  }

  form {
    width: 15rem;

  }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
  </script>
</body>

</html>