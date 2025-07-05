<?php
session_start();
require "db.php";
$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    die("❌ ID du covoiturage manquant.");
}

try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.nom, u.prenom, u.pseudo, u.photo,
               v.modèle, v.energie, m.libelle AS marque
        FROM covoiturage c
        JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id_chauffeur
        JOIN voiture v ON v.voiture_id = c.voiture_id
        LEFT JOIN marque m ON m.marque_id = v.marque_id
        WHERE c.covoiturage_id = :id
    ");
    $stmt->execute([':id' => $id]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        die("❌ Covoiturage introuvable.");
    }
$chauffeurId = $details['utilisateur_id_chauffeur'];
    $stmtAvis = $pdo->prepare("
        SELECT 
            a.avis_id, 
            a.commentaire, 
            a.note, 
            chauffeur.pseudo AS chauffeur_pseudo
        FROM avis a
        JOIN participation p ON p.avis_id = a.avis_id
        JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
        JOIN utilisateur chauffeur ON c.utilisateur_id_chauffeur = chauffeur.utilisateur_id
        WHERE a.statut = 'valide'
         AND c.utilisateur_id_chauffeur = :chauffeur_id
    ");
    $stmtAvis->execute(['chauffeur_id' => $chauffeurId]);
    $avis=$stmtAvis->fetchAll();

} catch (PDOException $e) {
    die("❌ Erreur DB : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Détail Covoiturage - EcoRide</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Neucha&display=swap" rel="stylesheet">
  <style>
  body {
    font-family: 'Neucha', cursive;
    background-color: #f0fdf4;
  }

  .logo {
    height: 50px;
  }

  .header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;

    color: white;
  }

  .card-header {
    background-color: #85c17e;
  }

  .btn-retour {
    color: #85c17e;
    text-decoration: none;
    font-size: 1.8rem;
  }

  .btn-retour:hover {
    text-decoration: underline;
  }

  h4 {
    font-size: 2rem;
  }
  </style>
</head>

<body>

  <header class="header-bar">
    <div>
      <img src="./img/logo" alt="Logo EcoRide" class="logo">
    </div>
    <div>
      <a href="javascript:history.back()" class="btn-retour"><i class="bi bi-arrow-left-circle"></i> Retour</a>
    </div>
  </header>

  <div class="container my-5">
    <div class="card shadow rounded">
      <div class="card-header text-white text-center">
        <h4><i class="bi bi-car-front"></i> Détails du covoiturage</h4>
      </div>
      <div class="card-body">
        <h5 class="text-success"><i class="bi bi-geo-alt-fill"></i> Trajet</h5>
        <p><strong>Départ :</strong> <?= htmlspecialchars($details['lieu_depart']) ?> à <?= $details['heure_depart'] ?>
        </p>
        <p><strong>Arrivée :</strong> <?= htmlspecialchars($details['lieu_arrivee']) ?> à
          <?= $details['heure_arrivee'] ?></p>
        <p><strong>Date :</strong> <?= $details['date_depart'] ?></p>
        <p><strong>Prix/personne :</strong> <?= $details['prix_personne'] ?> €</p>
        <p><strong>Places disponibles :</strong> <?= $details['nb_place'] ?></p>

        <hr>

        <h5 class="text-success"><i class="bi bi-person-fill"></i> Conducteur</h5>
        <p><strong>Pseudo :</strong> <?= htmlspecialchars($details['pseudo']) ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($details['prenom'] . ' ' . $details['nom']) ?></p>
        <img src="<?= htmlspecialchars($details['photo']) ?>" alt="Photo du conducteur" class="img-thumbnail"
          width="150">

        <hr>

        <h5 class="text-success"><i class="bi bi-truck-front"></i> Véhicule</h5>
        <p><strong>Modèle :</strong> <?= $details['modèle'] ?></p>
        <p><strong>Marque :</strong> <?= $details['marque'] ?></p>
        <p><strong>Énergie :</strong> <?= $details['energie'] ?></p>

        <hr>

        <h5 class="text-success"><i class="bi bi-chat-dots-fill"></i> Avis sur le conducteur</h5>
        <?php if ($avis): ?>
        <ul class="list-group">
          <?php foreach ($avis as $a): ?>
          <li class="list-group-item">
            <strong>Note :</strong> <?= htmlspecialchars($a['note']) ?>/5<br>
            <?= nl2br(htmlspecialchars($a['commentaire'])) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p>Aucun avis disponible.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</body>

</html>