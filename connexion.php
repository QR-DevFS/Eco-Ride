<?php
header('Content-Type: application/json');

$servername = "localhost";
$username2 = "root";
$password = "";
$dbname = "ecoride";

// Créer une connexion
$conn = new mysqli($servername, $username2, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Erreur de connexion à la base de données: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['pseudoconnexion']);
$password = trim($data['passwordconnexion']);

// Validation des entrées utilisateur
if (empty($username) || empty($password)) {
    die(json_encode(["success" => false, "message" => "Tous les champs sont requis."]));
}

// Préparer la requête pour vérifier les informations d'identification
$stmt = $conn->prepare("SELECT pseudo, password FROM Utilisateur WHERE pseudo = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc(); // Utiliser fetch_assoc() pour obtenir un seul enregistrement

if ($data) {
    // Vérifier le mot de passe
    if (password_verify($password, $data['password'])) {
        // Authentification réussie
        echo json_encode(["success" => true, "message" => "Connexion réussie"]);
        // Inclure le fichier recherche.php si nécessaire
      
    } else {
        // Mot de passe incorrect
        echo json_encode(["success" => false, "message" => "Mot de passe incorrect."]);
    }
} else {
    // Utilisateur non trouvé
    echo json_encode(["success" => false, "message" => "Nom d'utilisateur incorrect."]);
}

$stmt->close();
$conn->close();
?>