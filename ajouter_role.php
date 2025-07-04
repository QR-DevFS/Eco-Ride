// ajouter_role.php
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: login.php');
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Supprimer les anciens rôles
$pdo->prepare("DELETE FROM utilisateur_role WHERE utilisateur_id = ?")->execute([$utilisateur_id]);

if (!empty($_POST['roles'])) {
    $stmt = $pdo->prepare("INSERT INTO utilisateur_role (utilisateur_id, role_id) VALUES (?, ?)");
    foreach ($_POST['roles'] as $role_id) {
        $stmt->execute([$utilisateur_id, $role_id]);
    }
}

// Rediriger vers l'espace utilisateur avec un paramètre "updated"
header("Location: espace_util.php?updated=1");
exit();