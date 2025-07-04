<?php
session_start();
require 'db.php'; // connexion $pdo

if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['utilisateur_id'];
$covoitId = intval($_POST['covoiturage_id'] ?? 0);




// Requête pour récupérer les covoiturages réservés non terminés
$stmt = $pdo->prepare("
  SELECT c.*
  FROM participation p
  INNER JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
  WHERE p.utilisateur_id = :id
    AND c.statut != 'terminé'
");
$stmt->execute(['id' => $_SESSION['utilisateur_id']]);
$covoitsEnCours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?? "Eco-Ride" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


</head>

<body>

  <div class="container mt-4">

    <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block ">
    <!-- En-tête avec bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <button class="btn btn-outline-secondary" onclick="history.back()">← Retour</button>
      <h2 class="text-center flex-grow-1">Mes réservations en cours</h2>
    </div>
    <?php if (count($covoitsEnCours) === 0): ?>
    <div class="alert alert-info text-center">Aucune réservation de covoiturage en cours.</div>
    <?php else: ?>
    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Date départ</th>
          <th>Heure départ</th>
          <th>Lieu départ</th>
          <th>Date arrivée</th>
          <th>Heure arrivée</th>
          <th>Lieu arrivée</th>
          <th>Places dispo</th>
          <th>Prix/pers.</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($covoitsEnCours as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['date_depart']) ?></td>
          <td><?= htmlspecialchars($c['heure_depart']) ?></td>
          <td><?= htmlspecialchars($c['lieu_depart']) ?></td>
          <td><?= htmlspecialchars($c['date_arrivee']) ?></td>
          <td><?= htmlspecialchars($c['heure_arrivee']) ?></td>
          <td><?= htmlspecialchars($c['lieu_arrivee']) ?></td>
          <td><?= htmlspecialchars($c['nb_place']) ?></td>
          <td><?= number_format($c['prix_personne'], 2) ?> €</td>
          <td><?= htmlspecialchars($c['statut']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");

  body {
    font-family: "Neucha", "Outfit", serif;
  }

  .logo {
    max-width: 25rem;
    height: auto;

  }

  .alert {
    margin-top: 25%;
  }

  h2,
  .btn {
    color: #85c17e;
    border-color: #85c17e;
  }
  </style>
</body>

</html>