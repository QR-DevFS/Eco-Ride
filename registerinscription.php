<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$url = getenv('JAWSDB_URL') ?: "mysql://dq5e7327qswvhthq:yluqrzgxc4a9vns0@u3y93bv513l7zv6o.chr7pe7iynqr.eu-west-1.rds.amazonaws.com:3306/gexxtxdmk4rhsy75";
$dbparts = parse_url($url);

try {
    $pdo = new PDO(
        "mysql:host={$dbparts['host']};dbname=" . ltrim($dbparts["path"], "/") . ";charset=utf8mb4",
        $dbparts["user"],
        $dbparts["pass"]
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données: " . $e->getMessage()]);
    exit;
}

// Lire les données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Erreur de décodage JSON: " . json_last_error_msg()]);
    exit;
}

if (!isset($data['pseudo'], $data['email'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Données manquantes."]);
    exit;
}

$pseudo = trim($data['pseudo']);
$email = trim($data['email']);
$password = trim($data['password']);

if (empty($pseudo) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Tous les champs sont requis."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Email invalide."]);
    exit;
}

// Vérifier si le pseudo ou l'email existe déjà
$stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE pseudo = :pseudo OR email = :email");
$stmt->execute(['pseudo' => $pseudo, 'email' => $email]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(["success" => false, "message" => "Nom d'utilisateur ou email déjà utilisé."]);
    exit;
}

// Hacher le mot de passe
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insertion
$stmt = $pdo->prepare("INSERT INTO utilisateur (pseudo, email, password,credit) VALUES (:pseudo, :email, :password,20)");
$success = $stmt->execute([
    'pseudo' => $pseudo,
    'email' => $email,
    'password' => $hashedPassword
]);

if ($success) {
    echo json_encode(["success" => true, "message" => "Inscription réussie !"]);
} else {
    echo json_encode(["success" => false, "message" => "Erreur lors de l'inscription."]);
}
?>