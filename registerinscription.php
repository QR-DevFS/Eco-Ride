<?php
header('Content-Type: application/json');

$servername = "localhost";
$username1 = "root";
$password = "";
$dbname = "ecoride";

// Créer une connexion
$conn = new mysqli($servername, $username1, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Erreur de connexion à la base donnée: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['pseudo']);
$email = trim($data['email']);
$password = trim($data['password']);

// Validation des entrées utilisateur
if (empty($username) || empty($email) || empty($password)) {
    die(json_encode(["success" => false, "message" => "All fields are required."]));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(["success" => false, "message" => "Invalid email format."]));
}

// Vérifier si le nom d'utilisateur ou l'email existe déjà
$stmt = $conn->prepare("SELECT utilisateur_id FROM Utilisateur WHERE pseudo = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die(json_encode(["success" => false, "message" => "Username or email already exists."]));
}
$stmt->close();

// Hacher le mot de passe
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Utiliser une requête préparée pour insérer les données
$stmt = $conn->prepare("INSERT INTO Utilisateur (pseudo, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "User registered successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>