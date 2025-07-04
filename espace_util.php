<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: login.php');
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$utilisateur = $stmt->fetch();
$credits = $utilisateur['credit'] ?? 0; 

$stmt = $pdo->prepare("SELECT r.libelle FROM role r INNER JOIN utilisateur_role ur ON r.role_id = ur.role_id WHERE ur.utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

function a_le_role($role) {
    global $roles;
    return in_array($role, $roles);
}
$stmt = $pdo->prepare("
    SELECT v.*
    FROM voiture v
    INNER JOIN voiture_utilisateur vu ON v.voiture_id = vu.voiture_id
    WHERE vu.utilisateur_id = ?
");
$stmt->execute([$utilisateur_id]);
$voitures = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Espace utilisateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");



  body {
    background-color: #f5fff5;

    font-family: "Neucha", "Outfit", serif;
    font-size: 4vh;
  }


  .logo {
    max-width: 25rem;
    height: auto;
  }

  h1,
  h3 {
    color: #85c17e;
  }

  .sidebar a {
    display: block;
    margin-top: 1vh;
    ;
    color: #85c17e;
    padding: 10px 0;
    text-decoration: none;
    font-size: 4vh;

  }

  .sidebar a:hover {
    text-decoration: underline;
  }
  </style>
</head>

<body>
  <!-- NAVBAR -->
  <nav class="navbar navbar-light bg-light border-bottom">
    <div class="container-fluid">
      <button class="btn btn-outline-success d-md-none" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#menuMobile">
        ☰
      </button>
      <img src="7b3eb6e2-864e-47d4-97be-730401265524.png" alt="Logo EcoRide" class="logo mx-auto d-md-none">
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- SIDEBAR LARGE ÉCRAN -->
      <div class="col-md-3 d-none d-md-block bg-light p-4 sidebar">
        <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block mx-auto">
        <a href="index.php">Accueil</a>
        <a href="recherche_covoiturage.php">Covoiturages</a>
        <a href="contact.php">Contact</a>
        <hr>
        <div class="dropdown">
          <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Mon compte</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
            <li><a class="dropdown-item" href="deconnexion.php">Déconnexion</a></li>
          </ul>
        </div>
      </div>

      <!-- MENU MOBILE OFFCANVAS -->
      <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="menuMobile">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body sidebar">
          <a href="index.php">Accueil</a>
          <a href="recherche_covoiturage.php">Covoiturages</a>
          <a href="contact.php">Contact</a>
          <hr>
          <a href="profil.php">Mon profil</a>
          <a href="logout.php">Déconnexion</a>
        </div>
      </div>

      <!-- CONTENU PRINCIPAL -->
      <div class="col-md-9 p-4">
        <h1 class="text-center mb-5">Bienvenue, <?= htmlspecialchars($utilisateur['pseudo']) ?> !</h1>
        <p class="text-center text-success">Crédits disponibles : <strong><?= intval($credits) ?></strong></p>
        <h3 class="ms-5">Vous souhaitez être...</h3>
        <?php if (isset($_GET['updated'])): ?>
        <p><strong>Rôle(s) actuel(s)</strong> : <?= implode(', ', array_map('ucfirst', $roles)) ?></p>
        <?php endif; ?>
        <form method="post" action="ajouter_role.php">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="1" id="passager"
              <?= a_le_role('passager') ? 'checked' : '' ?>>
            <label class="form-check-label" for="passager">Passager</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="2" id="chauffeur"
              <?= a_le_role('chauffeur') ? 'checked' : '' ?>>
            <label class="form-check-label" for="chauffeur">Chauffeur</label>
          </div>
          <button type="submit" class="btn btn-success mt-2">Mettre à jour</button>
        </form>

        <?php if (a_le_role('chauffeur')): ?>
        <hr>
        <h3>Mes véhicules</h3>
        <a href="gerer_voitures.php" class="btn btn-outline-success">Ajouter un véhicule</a>
        <a href="gerer_pref.php" class="btn btn-outline-secondary">Préférences</a>
        <?php if (count($voitures) > 0): ?>
        <ul class="list-group my-3">
          <?php foreach ($voitures as $voiture): ?>
          <li class="list-group-item">
            <strong><?= htmlspecialchars($voiture['modèle']) ?></strong>
            – <?= htmlspecialchars($voiture['immatriculation']) ?> <br>
            <small>Énergie : <?= htmlspecialchars($voiture['energie']) ?> |
              Couleur : <?= htmlspecialchars($voiture['couleur']) ?> |
              Immat. : <?= htmlspecialchars($voiture['date_premiere_immatriculation']) ?></small>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-muted my-2">Vous n'avez pas encore enregistré de véhicule.</p>
        <?php endif; ?>

        <hr>
        <h3>Saisir un covoiturage</h3>
        <a href="ajouter_covoit.php" class="btn btn-outline-info">Nouveau trajet</a>

        <hr>
        <h3>Covoiturages à venir</h3>
        <a href="mes_covoiturages.php" class="btn btn-outline-warning">Voir mes trajets</a>
        <?php endif; ?>

        <?php if (a_le_role('passager')): ?>
        <hr>
        <h3>Participations</h3>
        <a href="validation_passager.php" class="btn btn-outline-primary">Voir mes réservations</a>
        <?php endif; ?>

        <hr>
        <h3>Historique</h3>
        <a href="histo.php" class="btn btn-outline-dark">Historique de mes covoiturages</a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>