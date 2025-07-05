<?php

session_start();
require "db.php";

// Récupération des paramètres de recherche
$depart = $_POST['depart'] ?? '';
$destination = $_POST['destination'] ?? '';
$date = $_POST['date'] ?? '';
$prix_max = $_POST['prix_max'] ?? null;
$duree_max = $_POST['duree_max'] ?? null;
$note_min = $_POST['note_min'] ?? null;
$electrique = isset($_POST['electrique']);

// Construction dynamique de la requête
$sql = "SELECT 
    c.*, 
    u.nom, u.prenom, u.pseudo, u.photo, 
    v.modèle, v.energie, 
    ROUND(n.note_moyenne, 1) AS note_moyenne,
    n.nb_avis_valides
FROM covoiturage c
JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id_chauffeur
JOIN voiture v ON v.voiture_id = c.voiture_id

-- Sous-requête : calcule la note moyenne + le nombre d'avis validés par chauffeur
LEFT JOIN (
    SELECT 
        c2.utilisateur_id_chauffeur AS chauffeur_id,
        AVG(a.note) AS note_moyenne,
        COUNT(*) AS nb_avis_valides
    FROM covoiturage c2
    JOIN participation p ON p.covoiturage_id = c2.covoiturage_id
    JOIN avis a ON a.avis_id = p.avis_id
    WHERE a.statut = 'valide'
    GROUP BY c2.utilisateur_id_chauffeur
) n ON n.chauffeur_id = c.utilisateur_id_chauffeur

WHERE c.lieu_depart = :depart
  AND c.lieu_arrivee = :destination
  AND c.date_depart = :date
  AND c.nb_place > 0


";

// Ajout des filtres facultatifs
if ($prix_max !== null && $prix_max !== '') {
    $sql .= " AND c.prix_personne <= :prix_max";
}
if ($electrique) {
    $sql .= " AND v.energie = 'Électrique'";
}


if ($note_min !== null && $note_min !== '') {
    $sql .= " HAVING note_moyenne >= :note_min";
}
if ($note_min !== null && $note_min !== '') {
    $sql .= " HAVING note_moyenne >= :note_min";
} else {
    $sql .= " GROUP BY c.covoiturage_id";
}

$sql .= " ORDER BY c.date_depart, c.heure_depart";

$stmt = $pdo->prepare($sql);

$params = [
    ':depart' => $depart,
    ':destination' => $destination,
    ':date' => $date,
];
if ($prix_max !== null && $prix_max !== '') {
    $params[':prix_max'] = $prix_max;
}
if ($note_min !== null && $note_min !== '') {
    $params[':note_min'] = $note_min;
}

$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "<div class='alert alert-warning text-center'>Aucun covoiturage trouvé pour ces critères.</div>";
    exit;
}

// Affichage des résultats
foreach ($rows as $row) {
    $note = $row['note_moyenne'] ? number_format($row['note_moyenne'], 1) : "Aucune note";
    $ecologique = ($row["energie"] == "Électrique") ? "✅ Écologique" : "❌ Non-écologique";

    echo "
    <div class='card m-3'>
        <div class='card-body'>
            <div class='row align-items-center'>
                <div class='col-md-2 text-center'>
                    <p>{$row['pseudo']}</p>
                    <p>⭐ $note</p>
                </div>
                <div class='col-md-6'>
                    <p><strong>De :</strong> {$row['lieu_depart']} → <strong>À :</strong> {$row['lieu_arrivee']}</p>
                    <p><strong>Date :</strong> {$row['date_depart']} | <strong>Départ :</strong> {$row['heure_depart']} - <strong>Arrivée :</strong> {$row['heure_arrivee']}</p>
                    <p><strong>Places restantes :</strong> {$row['nb_place']} | <strong>Prix :</strong> {$row['prix_personne']} €</p>
                    <p>$ecologique</p>
                </div>
                <div class='col-md-4 text-end'>
                    <a href='detail.php?id={$row['covoiturage_id']}' class='btn btn-outline-info'>Détail</a>";
 if (isset($_SESSION['utilisateur_id'])) {
        echo "
                    <form action='participer.php' method='post' onsubmit='return confirm(\"Confirmez-vous la participation à ce covoiturage et l'utilisation de votre crédit ?\")'>
                        <input type='hidden' name='covoiturage_id' value='{$row['covoiturage_id']}'>
                        <button type='submit' class='btn btn-success mt-1'>Participer</button>
                    </form>";
    } else {
        echo "
                    <a href='login.php' class='btn btn-secondary mt-1'>Se connecter pour participer</a>";
    }

    echo "
                </div>
            </div>
        </div>
    </div>";

}
?>