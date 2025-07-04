<?php
session_start();


if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: espace_util.php');
    exit;
}
$utilisateur_id = $_SESSION['utilisateur_id'];

require "db.php";

// Vérifier si l'utilisateur est chauffeur
$stmt = $pdo->prepare("
    SELECT r.libelle FROM role r
    JOIN utilisateur_role ur ON r.role_id = ur.role_id
    WHERE ur.utilisateur_id = ? AND r.libelle = 'chauffeur'
");
$stmt->execute([$utilisateur_id]);
$isChauffeur = $stmt->rowCount() > 0;

if (!$isChauffeur) {
    die("Vous devez être chauffeur pour saisir un voyage.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_depart = $_POST['date_depart'] ?? '';
    $heure_depart = $_POST['heure_depart'] ?? '';
    $lieu_depart = trim($_POST['lieu_depart'] ?? '');
    $date_arrivee = $_POST['date_arrivee'] ?? '';
    $heure_arrivee = $_POST['heure_arrivee'] ?? '';
    $lieu_arrivee = trim($_POST['lieu_arrivee'] ?? '');
    $nb_place = intval($_POST['nb_place'] ?? 0);
    $prix_personne = floatval($_POST['prix_personne'] ?? 0);
    $voiture_id = intval($_POST['voiture_id'] ?? 0);

    if (!$date_depart || !$heure_depart || !$lieu_depart || !$date_arrivee || !$heure_arrivee || !$lieu_arrivee || $nb_place <= 0 || $prix_personne <= 0 || $voiture_id <= 0) {
        $error = "Tous les champs sont obligatoires et doivent être valides.";
    } else {
        // Insérer le covoiturage
        $stmt = $pdo->prepare("INSERT INTO covoiturage (date_depart, heure_depart, lieu_depart, date_arrivee, heure_arrivee, lieu_arrivee, statut, nb_place, prix_personne,utilisateur_id_chauffeur,voiture_id) VALUES (?, ?, ?, ?, ?, ?, 'prévu', ?, ?,?,?)");
        $stmt->execute([$date_depart, $heure_depart, $lieu_depart, $date_arrivee, $heure_arrivee, $lieu_arrivee, $nb_place, $prix_personne,$_SESSION['utilisateur_id'], $voiture_id]);
        $covoiturage_id = $pdo->lastInsertId();

        // Ici tu peux ajouter la relation covoiturage/utilisateur/voiture si tu as une table dédiée

        $success = "Voyage ajouté avec succès !";
    }
}

// Récupérer les voitures du chauffeur (relation voiture_utilisateur)
$stmt = $pdo->prepare("
    SELECT v.* FROM voiture v
    JOIN voiture_utilisateur vu ON v.voiture_id = vu.voiture_id
    WHERE vu.utilisateur_id = ?
");
$stmt->execute([$utilisateur_id]);
$voitures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <title>Ajouter un voyage</title>
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");

  body {
    font-family: "Neucha", "Outfit", serif;
    background: #f6f8f5;
    color: #85c17e;
    margin: 40px;


  }

  h2 {
    font-family: "Neucha", "Outfit", serif;

    text-shadow: 1px 1px 3px #c1d19f;
  }

  form {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(102, 157, 48, 0.3);
    padding: 30px 40px;
    width: 360px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 18px;
    align-items: center;
  }

  label {
    display: flex;
    flex-direction: column;
    font-weight: 600;
    color: #56782a;
    font-size: 1.1rem;
  }

  input[type="date"],
  input[type="time"],
  input[type="text"],
  input[type="number"],
  select {
    margin-top: 8px;
    padding: 10px 12px;
    border: 2px solid #aac47a;
    border-radius: 12px;
    font-size: 1rem;
    font-family: "Neucha", "Outfit", serif;
    transition: border-color 0.3s ease;
  }

  input[type="date"]:focus,
  input[type="time"]:focus,
  input[type="text"]:focus,
  input[type="number"]:focus,
  select:focus {
    outline: none;
    border-color: #81b441;
    box-shadow: 0 0 8px #81b441a8;
  }

  button {
    width: 100%;
    background: linear-gradient(135deg, #6fa72f, #a3d04b);
    border: none;
    border-radius: 20px;
    padding: 14px;
    color: white;
    font-weight: 700;
    font-size: 1.3rem;
    cursor: pointer;
    box-shadow: 0 5px 10px rgba(111, 167, 47, 0.7);
    transition: background 0.4s ease;
    font-family: "Neucha", "Outfit", serif;
  }

  .btn {
    color: #6fa72f;
    font-size: 3vh;
  }

  button:hover {
    background: linear-gradient(135deg, #81b441, #c3e067);
    box-shadow: 0 6px 15px rgba(129, 180, 65, 0.8);
  }

  .message {
    width: 360px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 700;
  }

  .error {
    color: #d9534f;
    background: #f8d7da;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 0 5px #d9534f99;
  }

  .success {
    color: #3c763d;
    background: #dff0d8;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 0 5px #3c763d99;
  }

  .logo {
    max-width: 25rem;
    height: auto;
  }



  .container2 {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .container3 {
    margin-left: 8vw;
  }
  </style>
</head>

<body>

  <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block ">

  <div class="container3">
    <div class="row space-between  mb-4">
      <div class="col-md-4 col-8 ">
        <a href="espace_util.php" class="btn"> ← Retour</a>
      </div>
      <div class="col-md-8 col-8">
        <h1 class="ms-5 mb-0">Ajouter un voyage</h1>
      </div>

    </div>
  </div>

  <h4 class="text-center mb-5 mt-3">2 crédits pour le bon fonctionnement de EcoRide seront prélevés de votre voyage</h4>

  <?php if ($error): ?>
  <div class="message error"><?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
  <div class="message success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <div class="container2">

    <form method="post" action="">
      <label for="date_depart">Date départ :
        <input type="date" id="date_depart" name="date_depart" required />
      </label>

      <label for="heure_depart">Heure départ :
        <input type="time" id="heure_depart" name="heure_depart" required />
      </label>

      <label for="lieu_depart">Lieu départ :
        <input type="text" id="lieu_depart" name="lieu_depart" required />
      </label>

      <label for="date_arrivee">Date arrivée :
        <input type="date" id="date_arrivee" name="date_arrivee" required />
      </label>

      <label for="heure_arrivee">Heure arrivée :
        <input type="time" id="heure_arrivee" name="heure_arrivee" required />
      </label>

      <label for="lieu_arrivee">Lieu arrivée :
        <input type="text" id="lieu_arrivee" name="lieu_arrivee" required />
      </label>

      <label for="nb_place">Nombre de places :
        <input type="number" id="nb_place" name="nb_place" min="1" required />
      </label>

      <label for="prix_personne">Prix par personne (€) :
        <input type="number" id="prix_personne" name="prix_personne" step="0.01" min="0" required />
      </label>

      <label for="voiture_id">Choisir un véhicule :
        <select id="voiture_id" name="voiture_id" required>
          <option value="">-- Sélectionnez --</option>
          <?php foreach ($voitures as $v): ?>
          <option value="<?= $v['voiture_id'] ?>">
            <?= htmlspecialchars($v['modèle']) . ' - ' . htmlspecialchars($v['immatriculation']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </label>

      <button type="submit">Ajouter le voyage</button>
    </form>
  </div>
</body>

</html>