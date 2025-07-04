<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Charger PHPMailer
session_start();
require "db.php";

// Vérification connexion utilisateur
if (!isset($_SESSION['utilisateur_id'])) {
    die("Veuillez vous connecter.");
}
$userId = $_SESSION['utilisateur_id'];

// -- ANNULATION COVOITURAGE --
if (isset($_POST['annuler']) && isset($_POST['covoiturage_id'])) {
  $mail = new PHPMailer(true);
    $covoiturageId = intval($_POST['covoiturage_id']);

    // Récupérer le covoiturage et vérifier que c'est le chauffeur (utilisateur_id dans covoiturage)
    $stmt = $pdo->prepare("SELECT * FROM covoiturage WHERE covoiturage_id = ? AND statut = 'prévu'");
    $stmt->execute([$covoiturageId]);
    $covoit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$covoit) {
        $error = "Covoiturage introuvable ou déjà annulé.";
    } elseif ($covoit['utilisateur_id_chauffeur'] != $userId) {
        $error = "Vous n'êtes pas le chauffeur de ce covoiturage.";
    } else {
        // Annuler le covoiturage (statut = 'annulé')
        $stmtUpdate = $pdo->prepare("UPDATE covoiturage SET statut = 'annulé' WHERE covoiturage_id = ?");
        $stmtUpdate->execute([$covoiturageId]);

        // Rembourser crédits utilisateur (ex: nb_place * prix_personne)
        $montantRembourse = $covoit['nb_place'] * $covoit['prix_personne'];

        $stmtCredit = $pdo->prepare("UPDATE utilisateur SET credit = credit + ? WHERE utilisateur_id = ?");
        $stmtCredit->execute([$montantRembourse, $userId]);

        // Envoi mail à l'utilisateur (chauffeur) et potentiellement à tous les participants
        // Comme pas de participants en BDD, on envoie juste au chauffeur ici
        $stmtUser = $pdo->prepare("SELECT email, pseudo FROM utilisateur WHERE utilisateur_id = ?");
        $stmtUser->execute([$userId]);
        $userInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);

  try {
    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';             // Serveur SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'ravenelquentin@gmail.com';     // Ton adresse mail
    $mail->Password = 'vdax takb btja wxhw';         // Un mot de passe d'application (pas ton vrai mot de passe Gmail)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Destinataire
    $mail->setFrom('no-reply@ecoride.com', 'EcoRide');
    $mail->addAddress($userInfo['email'], $userInfo['pseudo']);

    // Contenu
    $mail->isHTML(false); // ou true si tu veux du HTML
    $mail->Subject = "Annulation de votre covoiturage";
    $mail->Body    = "Bonjour " . $userInfo['pseudo'] . ",\n\nVotre covoiturage du " . $covoit['date_depart'] . " à " . $covoit['heure_depart'] . " a bien été annulé.\nLe montant de " . number_format($montantRembourse, 2) . " crédits vous a été remboursé.\n\nCordialement,\nEcoRide";

    $mail->send();
    $success = "Covoiturage annulé, crédits remboursés et email envoyé.";
} catch (Exception $e) {
    $error = "L'email n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}";
}
    }
}

// -- Récupérer historique covoiturages du chauffeur --
//$stmtHist = $pdo->prepare("SELECT * FROM covoiturage WHERE utilisateur_id_chauffeur = ?");
//$stmtHist->execute([$_SESSION['utilisateur_id']]);
//$historique = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
$stmtHist = $pdo->prepare("
  SELECT * FROM (
    SELECT c.*, 
           'chauffeur' AS role,
           NULL AS part_covoit_id,
           NULL AS avis_id
    FROM covoiturage c
    WHERE c.utilisateur_id_chauffeur = :id_chauffeur

    UNION

    SELECT c.*, 
           'passager' AS role,
           p.participation_id AS part_covoit_id,
           p.avis_id
    FROM covoiturage c
    JOIN participation p ON p.covoiturage_id = c.covoiturage_id
    WHERE p.utilisateur_id = :id_passager
  ) AS historique_mixte
  ORDER BY date_depart DESC, heure_depart DESC
");

$stmtHist->execute([
  'id_chauffeur' => $_SESSION['utilisateur_id'],
  'id_passager' => $_SESSION['utilisateur_id']
]);
$historique = $stmtHist->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <title>Historique covoiturages - EcoRide</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");


  body {
    background-color: #f3f7f2;
    font-family: "Neucha", "Outfit", serif;
    color: #85c17e;
  }

  .btn-annuler {
    color: #85c17e;
    border-color: #85c17e;
    ;
  }

  .container {
    max-width: 80vw;
    margin-top: 10vh;

    margin-bottom: 50px;
  }

  h1 {
    font-family: "Neucha", "Outfit", serif;
    font-weight: 700;
    color: #85c17e;
    margin-bottom: 40px;
    text-align: center;
  }

  table {
    background: #e7f1d1;
    border-radius: 10px;
  }

  th,
  td {
    vertical-align: middle !important;
  }

  .btn-annuler {
    background-color: #7fa72a;
    border-color: #7fa72a;
    color: white;
  }

  .btn-annuler:hover {
    background-color: #5e7817;
    border-color: #5e7817;
  }

  .alert-success,
  .alert-danger {
    max-width: 600px;
    margin: 0 auto 20px auto;
    border-radius: 15px;
    font-weight: 600;
    font-size: 1.1em;
  }

  .logo {
    max-width: 25rem;
    height: auto;
    padding: 4vh;
  }

  .btn {
    color: #85c17e;
    border-color: #85c17e;
  }

  table {
    font-size: 0.95rem;
  }

  .table th {
    background-color: #eaf7e9;
    color: #2e7d32;
  }

  .btn-outline-warning {
    border-color: #f0ad4e;
    color: #d58512;
  }

  .btn-outline-warning:hover {
    background-color: #fff8e1;
  }

  .btn-outline-success {
    border-color: #5cb85c;
    color: #2e7d32;
  }

  .btn-outline-success:hover {
    background-color: #e8f5e9;
  }

  .badge {
    font-size: 0.85rem;
  }
  </style>
</head>

<body>
  <!-- <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block ">
  <div class="row justify-content-center align-items-center mb-4 text-center">
    <div class="col-auto">
      <a href="espace_util.php" class="btn btn-outline-success me-5 ">Retour</a>
      <h1 class="d-inline align-middle">Historique des mes voyages</h1>
    </div>
  </div>!-->
  <div class="container mt-4">

    <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block ">
    <!-- En-tête avec bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <button class="btn btn-outline-secondary" onclick="history.back()">← Retour</button>
      <h2 class="text-center flex-grow-1">Historique des mes covoiturages</h2>
    </div>
  </div>
  <?php if (isset($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (isset($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <div class="container">
    <table class="table table-striped table-bordered bg-white shadow-sm rounded">
      <thead class="table-success text-center">
        <tr>
          <th>Date départ</th>
          <th>Heure départ</th>
          <th>Lieu départ</th>
          <th>Date arrivée</th>
          <th>Heure arrivée</th>
          <th>Lieu arrivée</th>
          <th>Places dispos</th>
          <th>Prix/pers.</th>
          <th>Statut</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody class="text-center align-middle">
        <?php if (count($historique) === 0): ?>
        <tr>
          <td colspan="10" class="text-center text-muted">Aucun covoiturage trouvé.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($historique as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['date_depart']) ?></td>
          <td><?= htmlspecialchars($c['heure_depart']) ?></td>
          <td><?= htmlspecialchars($c['lieu_depart']) ?></td>
          <td><?= htmlspecialchars($c['date_arrivee']) ?></td>
          <td><?= htmlspecialchars($c['heure_arrivee']) ?></td>
          <td><?= htmlspecialchars($c['lieu_arrivee']) ?></td>
          <td><?= htmlspecialchars($c['nb_place']) ?></td>
          <td><?= number_format($c['prix_personne'], 2) ?> €</td>
          <td>
            <?php if ($c['statut'] === 'prévu'): ?>
            <span class="badge bg-warning text-dark">Prévu</span>
            <?php elseif ($c['statut'] === 'en cours'): ?>
            <span class="badge bg-primary">En cours</span>
            <?php else: ?>
            <span class="badge bg-secondary">Terminé</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($c['role'] === 'chauffeur' && $c['statut'] === 'prévu'): ?>
            <form method="post" onsubmit="return confirm('Confirmez-vous l\'annulation ?');" class="d-inline">
              <input type="hidden" name="covoiturage_id" value="<?= intval($c['covoiturage_id']) ?>" />
              <button type="submit" name="annuler" class="btn btn-outline-warning btn-sm">Annuler</button>
            </form>

            <?php elseif ($c['role'] === 'passager' && $c['statut'] === 'terminé' && empty($c['avis_id'])): ?>
            <form method="post" action="validation_passager.php" class="d-inline">
              <input type="hidden" name="covoiturage_id" value="<?= intval($c['covoiturage_id']) ?>" />
              <button type="submit" class="btn btn-outline-success btn-sm">Laisser un avis</button>
            </form>

            <?php elseif ($c['role'] === 'passager' && !empty($c['avis_id'])): ?>
            <span class="text-muted">Avis donné</span>

            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</body>

</html>