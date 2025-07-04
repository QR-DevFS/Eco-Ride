<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=gexxtxdmk4rhsy75", "root", "");
$utilisateur_id = $_SESSION['utilisateur_id']; // Utilisateur connecté

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'];

    // Met à jour le rôle dans la table utilisateur
    $pdo->prepare("UPDATE utilisateur SET pseudo = CONCAT(pseudo, ' [$role]') WHERE utilisateur_id = ?")
        ->execute([$utilisateur_id]);

    // Si le rôle est chauffeur ou les deux, on ajoute la voiture
    if ($role !== "passager") {
        $stmt = $pdo->prepare("INSERT INTO voiture (modèle, immatriculation, energie, couleur, date_premiere_immatriculation)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['modele'],
            $_POST['immatriculation'] . " - UID:$utilisateur_id", // Ajout de l’ID de l’utilisateur
            $_POST['energie'],
            $_POST['couleur'],
            $_POST['date_premiere_immatriculation']
        ]);
    }

    echo "Rôle et données mises à jour.";
}
?>