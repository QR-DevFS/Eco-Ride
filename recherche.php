<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?? "Eco-Ride" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>


<?php

require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $depart = $_POST["depart"];
    $destination = $_POST["destination"];
    $date = $_POST["date"];

    // Connexion à la DB
   
    // Recherche des trajets avec places disponibles
    $sql = "
    SELECT c.*, u.pseudo, u.photo, AVG(a.note) as note_moyenne, v.energie
    FROM covoiturage c
    JOIN utilisateur u ON u.utilisateur_id = c.covoiturage_id
    JOIN voiture v ON v.voiture_id = u.utilisateur_id
    LEFT JOIN avis a ON a.id_utilisateur= u.utilisateur_id
    WHERE c.lieu_depart = :depart
      AND c.lieu_arrivee = :destination
      AND c.date_depart = :date
      AND c.nb_place > 0
    GROUP BY c.covoiturage_id
    ORDER BY c.date_depart, c.heure_depart
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":depart" => $depart,
        ":destination" => $destination,
        ":date" => $date


    ]);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultats) > 0) {
        foreach ($resultats as $row) {
            $ecologique = ($row["energie"] == "Électrique") ? "✅ Écologique" : "❌ Non-écologique";
            $note = $row["note_moyenne"] ? number_format($row["note_moyenne"], 1) : "Aucune note";
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
                    <a href='detail.php?id={$row['covoiturage_id']}' class='btn btn-outline-info'>Détail</a>
                  </div>
                </div>
              </div>
            </div>";
        }
    } else {
        // Si aucun trajet ce jour-là, proposer la date la plus proche
        $stmt = $pdo->prepare("
            SELECT date_depart 
            FROM covoiturage
            WHERE lieu_depart = :depart AND lieu_arrivee = :destination AND date_depart > :date AND nb_place > 0
            ORDER BY date_depart ASC LIMIT 1
        ");
        $stmt->execute([
            ":depart" => $depart,
            ":destination" => $destination,
            ":date" => $date
        ]);
        $prochain = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($prochain) {
            echo "<div class='alert alert-warning text-center mt-4'>
                    Aucun covoiturage le <strong>$date</strong>, essayez plutôt le <strong>{$prochain['date_depart']}</strong>.
                </div>";
        } else {
            echo "<div class='alert alert-danger text-center mt-4'>Aucun itinéraire disponible.</div>";
        }
    }
}
?>