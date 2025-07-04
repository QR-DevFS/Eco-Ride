<?php
session_start();
require "db.php";
require "vendor/autoload.php"; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['utilisateur_id'])) {
    die("Veuillez vous connecter.");
}
$userId = $_SESSION['utilisateur_id'];

// Démarrer un covoiturage
if (isset($_POST['demarrer']) && isset($_POST['covoiturage_id'])) {
    $covoitId = intval($_POST['covoiturage_id']);
    $stmt = $pdo->prepare("UPDATE covoiturage SET statut = 'en cours' WHERE covoiturage_id = ? AND utilisateur_id_chauffeur = ?");
    $stmt->execute([$covoitId, $userId]);
}

// Marquer comme terminé
if (isset($_POST['terminer']) && isset($_POST['covoiturage_id'])) {
    $covoitId = intval($_POST['covoiturage_id']);

    // Mettre à jour le statut
    $stmt = $pdo->prepare("UPDATE covoiturage SET statut = 'terminé' WHERE covoiturage_id = ? AND utilisateur_id_chauffeur = ?");
    $stmt->execute([$covoitId, $userId]);

    // Récupérer les participants
    $stmtPart = $pdo->prepare("SELECT u.email, u.pseudo FROM utilisateur u 
        JOIN participation p ON u.utilisateur_id = p.utilisateur_id 
        WHERE p.covoiturage_id = ?");
    $stmtPart->execute([$covoitId]);
    $participants = $stmtPart->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer un mail à chaque participant
    foreach ($participants as $p) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
           $mail->Username = 'QRdev.fs@gmail.com'; // Remplacez par votre email
        $mail->Password = 'dhrs qybg ffzj egoz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('Ecoridel@ecoride.com', 'EcoRide');
            $mail->addAddress($p['email'], $p['pseudo']);
            $mail->Subject = "Merci de valider votre trajet";
            $mail->Body = "Bonjour " . $p['pseudo'] . ",\n\nLe covoiturage auquel vous avez participé est terminé.\nMerci de vous connecter à votre espace pour valider si tout s'est bien passé et éventuellement laisser un avis.";

            $mail->send();
        } catch (Exception $e) {
            // Erreur d’envoi
        }
    }
}

// Récupération des covoiturages
$stmt = $pdo->prepare("SELECT * FROM covoiturage WHERE utilisateur_id_chauffeur = ?");
$stmt->execute([$userId]);
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Mes Covoiturages</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light text-dark">
  <div class="container my-5">
    <div class="container mt-4">

      <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block ">
      <!-- En-tête avec bouton retour -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-secondary" onclick="history.back()">← Retour</button>
        <h2 class="text-center flex-grow-1">Gérer mes covoiturages</h2>
      </div>
    </div>
    <table class="table table-bordered bg-white shadow-sm rounded">
      <thead class="table-success text-center">
        <tr>
          <th class="align-middle">Date</th>
          <th class="align-middle">Départ</th>
          <th class="align-middle">Arrivée</th>
          <th class="align-middle">Statut</th>
          <th class="align-middle">Action</th>
        </tr>
      </thead>
      <tbody class="text-center align-middle">
        <?php foreach ($liste as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['date_depart']) ?></td>
          <td><?= htmlspecialchars($c['lieu_depart']) ?></td>
          <td><?= htmlspecialchars($c['lieu_arrivee']) ?></td>
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
            <?php if ($c['statut'] === 'prévu'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="covoiturage_id" value="<?= $c['covoiturage_id'] ?>">
              <button name="demarrer" class="btn btn-outline-primary btn-sm">Démarrer</button>
            </form>
            <?php elseif ($c['statut'] === 'en cours'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="covoiturage_id" value="<?= $c['covoiturage_id'] ?>">
              <button name="terminer" class="btn btn-outline-success btn-sm">Arrivée</button>
            </form>
            <?php else: ?>
            <span class="text-muted">Aucune action</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($liste)): ?>
        <tr>
          <td colspan="5" class="text-center text-muted">Aucun covoiturage.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>


  </div>
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");

  body {
    font-family: 'Neucha', cursive;
    background-color: #f0fdf4;
  }

  table {
    font-size: 0.95rem;
  }

  .table th {
    background-color: #eaf7e9;
    color: #2e7d32;
  }

  .btn-outline-primary {
    border-color: #6abf69;
    color: #388e3c;
  }

  .btn-outline-success {
    border-color: #5cb85c;
    color: #2e7d32;
  }

  .btn-outline-primary:hover,
  .btn-outline-success:hover {
    background-color: #e8f5e9;
  }

  .badge {
    font-size: 0.85rem;
  }

  .logo {
    max-width: 25rem;
    height: auto;
    padding: 4vh;
  }

  h2 {
    color: #85c17e;

    text-align: center;
    margin-bottom: 10%;
  }
  </style>
</body>


</html>