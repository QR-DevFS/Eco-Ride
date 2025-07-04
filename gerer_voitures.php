<?php
session_start();


if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: Index.php');
    exit;
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Connexion PDO
require "db.php";

// Gestion ajout voiture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_voiture') {
    $marque = $_POST['marque'] ?? '';
  $modele = $_POST['modele'] ?? '';
    $immatriculation = $_POST['immatriculation'] ?? '';
    $energie = $_POST['energie'] ?? '';
    $couleur = $_POST['couleur'] ?? '';
    $date_premiere_immatriculation = $_POST['date_premiere_immatriculation'] ?? '';

   if ($modele && $immatriculation && $energie && $couleur && $date_premiere_immatriculation) {
    // Chercher si la marque existe
    $stmt = $pdo->prepare("SELECT marque_id FROM marque WHERE libelle = ?");
    $stmt->execute([$marque]);
    $marque_existante = $stmt->fetch();

    if ($marque_existante) {
        $marque_id = $marque_existante['marque_id'];
    } else {
        // Insérer la nouvelle marque
        $stmt = $pdo->prepare("INSERT INTO marque (libelle) VALUES (?)");
        $stmt->execute([$marque]);
        $marque_id = $pdo->lastInsertId();
    }

    // Insérer la voiture avec la marque_id
    $stmt = $pdo->prepare("INSERT INTO voiture (modèle, immatriculation, energie, couleur, date_premiere_immatriculation, marque_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$modele, $immatriculation, $energie, $couleur, $date_premiere_immatriculation, $marque_id]);
    
    // etc.
}
    // Récupérer l'ID de la voiture insérée
    $voiture_id = $pdo->lastInsertId();

    // Insérer la liaison voiture/utilisateur
    $stmt2 = $pdo->prepare("INSERT INTO voiture_utilisateur (voiture_id, utilisateur_id) VALUES (?, ?)");
    $stmt2->execute([$voiture_id, $utilisateur_id]);

    header("Location: espace_util.php?success=1");
    exit;


    } else {
        $error = "Tous les champs sont obligatoires.";
    }


?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <title>Gestion des Voitures</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
  <form method="post" action="">
    <input type="hidden" name="action" value="ajouter_voiture" />
    <label>Marque :
      <input type="text" name="marque" required />
    </label>
    <label>Modèle :
      <input type="text" name="modele" required />
    </label>
    <label>Immatriculation :
      <input type="text" name="immatriculation" required />
    </label>
    <label>Energie :
      <input type="text" name="energie" required />
    </label>
    <label>Couleur :
      <input type="text" name="couleur" required />
    </label>
    <label>Date 1ère immatriculation :
      <input type="date" name="date_premiere_immatriculation" required />
    </label>
    <button class="btn2" type="submit">Ajouter</button>
  </form>
  <style>
  /* Fonts style naturel et manuscrit */
  @import url("https://fonts.googleapis.com/css2?family=Neucha&family=Outfit:wght@100..900&display=swap");

  body {
    font-family: "Neucha", "Outfit", serif;
    background: #f6f8f5;
    color: #85c17e;
    margin: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  h3 {
    font-family: "Neucha", "Outfit", serif;
    font-size: 2.5rem;
    color: #85c17e;
    margin-bottom: 30px;
    text-align: center;
    text-shadow: 1px 1px 3px #c1d19f;
  }

  form {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(102, 157, 48, 0.3);
    padding: 30px 40px;
    width: 320px;
    box-sizing: border-box;
  }

  label {
    display: flex;
    flex-direction: column;
    font-weight: 600;
    margin-bottom: 20px;
    color: #56782a;
    font-size: 1.1rem;
  }

  input[type="text"],
  input[type="date"] {
    margin-top: 8px;
    padding: 10px 12px;
    border: 2px solid #aac47a;
    border-radius: 12px;
    font-size: 1rem;
    font-family: "Neucha", "Outfit", serif;
    transition: border-color 0.3s ease;
  }

  input[type="text"]:focus,
  input[type="date"]:focus {
    outline: none;
    border-color: #81b441;
    box-shadow: 0 0 8px #81b441a8;
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
    font-family: 'Patrick Hand', cursive;
  }

  button:hover {
    background: linear-gradient(135deg, #81b441, #c3e067);
    box-shadow: 0 6px 15px rgba(129, 180, 65, 0.8);
  }



  .btn {
    color: #85c17e;
    border-color: #85c17e;
  }


  .logo {
    max-width: 25rem;
    height: auto;
    padding: 4vh;
  }

  h2 {
    margin-bottom: 10%;
    ;
  }
  </style>
</body>

</html>