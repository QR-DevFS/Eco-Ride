<?php
// db.php
// Configuration de la connexion PDO à la base de données

// Paramètres de connexion
$host = 'u3y93bv513l7zv6o.chr7pe7iynqr.eu-west-1.rds.amazonaws.com';
$dbname = 'gexxtxdmk4rhsy75';
$user = 'dq5e7327qswvhthq';
$pass = 'yluqrzgxc4a9vns0';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // gestion des erreurs via exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // fetch associatif par défaut
    PDO::ATTR_EMULATE_PREPARES => false, // utiliser les vrais prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, afficher un message d'erreur et stopper le script
    echo 'Connexion échouée : ' . htmlspecialchars($e->getMessage());
    exit;
}
?>