<?php
include 'config_roles.php';
session_start();
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);
require "db.php";
if (!isset($data['pseudoConnexion'], $data['passwordConnexion'])) {
    echo json_encode(["success" => false, "message" => "Champs requis manquants."]);
    exit;
}

$pseudo = trim($data['pseudoConnexion']);
$password = trim($data['passwordConnexion']);


// Vérification admin
if ($pseudo === getenv('ADMIN_PSEUDO') && password_verify($password, trim(getenv('ADMIN_PASSWORD_HASH')))) {
    $_SESSION["utilisateur_id"]=1;
    $_SESSION['pseudo'] = getenv('ADMIN_PSEUDO');
    $_SESSION['role'] = 'admin';
    echo json_encode(["success" => true, "role" => "admin"]);
    exit;
}
$stmt = $pdo->prepare("SELECT utilisateur_id  FROM utilisateur WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    $utilisateuremploye = $stmt->fetch(PDO::FETCH_ASSOC);
// Vérification employé
if ($pseudo === getenv('EMPLOYE_PSEUDO') && password_verify($password, trim(getenv('EMPLOYE_PASSWORD_HASH')))) {
     $_SESSION["utilisateur_id"]=$utilisateuremploye;
    $_SESSION['pseudo'] = getenv('EMPLOYE_PSEUDO');
    $_SESSION['role'] = 'employe';
    echo json_encode(["success" => true, "role" => "employe"]);
    exit;
}

// Connexion base utilisateur normale
$url = getenv('JAWSDB_URL') ?: "mysql://dq5e7327qswvhthq:yluqrzgxc4a9vns0@u3y93bv513l7zv6o.chr7pe7iynqr.eu-west-1.rds.amazonaws.com:3306/gexxtxdmk4rhsy75";
$dbparts = parse_url($url);

try {
    $pdo = new PDO(
        "mysql:host={$dbparts['host']};dbname=" . ltrim($dbparts["path"], "/") . ";charset=utf8mb4",
        $dbparts["user"],
        $dbparts["pass"]
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT utilisateur_id, pseudo, password FROM utilisateur WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['utilisateur_id'] = $user['utilisateur_id'];
        $_SESSION['pseudo'] = $user['pseudo'];
        $_SESSION['role'] = 'user'; // ou selon ta logique de rôle utilisateur
        echo json_encode(['success' => true, 'role' => 'user']);
    } else {
        echo json_encode(['success' => false, 'message' => "Nom d'utilisateur ou mot de passe incorrect."]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Erreur lors de la vérification: " . $e->getMessage()]);
}
exit;
?>