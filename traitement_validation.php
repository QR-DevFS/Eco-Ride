<?php
session_start();
require 'db.php';

if (!isset($_SESSION['utilisateur_id']) || !isset($_POST['valider_trajet'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['utilisateur_id'];
$covoitId = intval($_POST['covoiturage_id']);
$etat = $_POST['etat'];
$note = intval($_POST['note']);
$commentaire = trim($_POST['commentaire']);

// Vérifie la participation
$stmt = $pdo->prepare("SELECT c.utilisateur_id_chauffeur FROM participation p 
    JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
    WHERE p.utilisateur_id = ? AND p.covoiturage_id = ?");
$stmt->execute([$userId, $covoitId]);
$data = $stmt->fetch();
$chauffeurId = $data['utilisateur_id_chauffeur'];
if (!$data) {
    echo "Participation introuvable.";
    exit;
}

$chauffeurId = $data['utilisateur_id_chauffeur'];

// Création de l'avis
$statut = 'en_attente';
$stmt = $pdo->prepare("INSERT INTO avis (commentaire, note, statut,id_utilisateur) VALUES (?, ?, ?,?)");
$stmt->execute([$commentaire, $note, $statut,$chauffeurId]);
$avisId = $pdo->lastInsertId();

// Mise à jour de la participation
$stmt = $pdo->prepare("UPDATE participation SET avis_id = ?, statut_validation = ? WHERE utilisateur_id = ? AND covoiturage_id = ?");
$stmt->execute([$avisId, $etat, $userId, $covoitId]);

// Si le trajet s'est bien passé, on crédite le chauffeur
if ($etat === 'bien') {
    // On récupère le prix
    $stmt = $pdo->prepare("SELECT prix_personne FROM covoiturage WHERE covoiturage_id = ?");
    $stmt->execute([$covoitId]);
    $prix = $stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE utilisateur SET credit = credit + ? WHERE utilisateur_id = ?");
    $stmt->execute([$prix, $chauffeurId]);
}

echo "Merci pour votre retour.";
?>