<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: Index.php');
    exit;
}
$utilisateur_id = $_SESSION['utilisateur_id'];

require "db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fumeur = isset($_POST['fumeur']) ? 'oui' : 'non';
    $animal = isset($_POST['animal']) ? 'oui' : 'non';
    $autres = $_POST['autres'] ?? '';

    // Mettre à jour ou insérer les paramètres
    $params = [
        "user_{$utilisateur_id}_fumeur" => $fumeur,
        "user_{$utilisateur_id}_animal" => $animal,
        "user_{$utilisateur_id}_autres" => $autres,
    ];

    foreach ($params as $prop => $val) {
        $stmt = $pdo->prepare("SELECT parametre_id FROM parametre WHERE propriete = ?");
        $stmt->execute([$prop]);
        if ($stmt->rowCount() > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE parametre SET valeur = ? WHERE propriete = ?");
            $stmt->execute([$val, $prop]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO parametre (propriete, valeur) VALUES (?, ?)");
            $stmt->execute([$prop, $val]);
        }
    }

     $_SESSION['message'] = "✅ Préférences mises à jour avec succès !";
   header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Récupérer les préférences actuelles
function getPref($pdo, $prop) {
    $stmt = $pdo->prepare("SELECT valeur FROM parametre WHERE propriete = ?");
    $stmt->execute([$prop]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res ? $res['valeur'] : 'non';
}

$fumeur = getPref($pdo, "user_{$utilisateur_id}_fumeur");
$animal = getPref($pdo, "user_{$utilisateur_id}_animal");
$autres = getPref($pdo, "user_{$utilisateur_id}_autres");
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Gestion des Préférences</title>
  <style>
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");

  body {
    font-family: 'Neucha', cursive;
    background-color: #f0fdf4;
    padding: 4vh;
  }

  h2 {
    color: #85c17e;
    font-size: 4vh;
    text-align: center;
    margin-bottom: 10%;
  }

  form {
    max-width: 600px;
    margin: auto;
    background-color: #ffffff;
    border: 2px solid #c8e6c9;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0px 0px 10px #d0ecd0;
  }

  label {
    font-size: 2.5vh;
    color: #85c17e;
    display: block;
    margin-bottom: 15px;
  }

  textarea {
    width: 100%;
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #a5d6a7;
    resize: vertical;
    font-size: 2vh;
  }

  .btn-success {
    font-size: 2.2vh;
    padding: 10px 24px;
    border-radius: 10px;
    background-color: #85c17e;
    border: none;
  }

  p.message {
    text-align: center;
    font-size: 2.2vh;
    color: green;
    margin-bottom: 20px;
  }



  h2 {
    margin-top: 5vh;

  }

  .btn {
    color: #85c17e;
    border-color: #85c17e;
  }

  .btn2 {
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

  h1 {
    font-family: "Neucha", "Outfit", serif;
    font-weight: 700;
    color: #7fa72a;
    margin-bottom: 40px;
    text-align: center;
  }

  .logo {
    max-width: 25rem;
    height: auto;
    padding: 4vh;
  }

  .btn-success {
    color: #7fa72a;
  }
  </style>
</head>


<body>
  <div class="container mt-4">

    <img src="./img/logo.png" alt="EcoRide Logo" class="logo mb-4 d-block ">
    <!-- En-tête avec bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <button class="btn btn-outline-secondary" onclick="history.back()">← Retour</button>
      <h2 class="text-center flex-grow-1">Historique des mes covoiturages</h2>
    </div>
  </div>
  <?php if ($message): ?>
  <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
    <?= htmlspecialchars($message) ?><br>
    <small>Redirection automatique dans 3 secondes...</small>
  </div>
  <script>
  setTimeout(() => {
    window.location.href = 'espace_util.php'; // modifie avec le bon chemin si besoin
  }, 3000);
  </script>
  <?php endif; ?>
  <form method="post" action="">
    <label><input type="checkbox" name="fumeur" <?= $fumeur === 'oui' ? 'checked' : '' ?> /> Accepte
      fumeur</label><br />
    <label><input type="checkbox" name="animal" <?= $animal === 'oui' ? 'checked' : '' ?> /> Accepte
      animal</label><br />
    <label>Autres préférences :<br />
      <textarea name="autres" rows="4" cols="40"><?= htmlspecialchars($autres) ?></textarea>
    </label><br />
    <button class="btn2" type="submit">Enregistrer</button>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</body>

</html>