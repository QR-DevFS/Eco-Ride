<?php
//require "filtre.php";

// Connexion à la base de données

//if ($_SERVER["REQUEST_METHOD"] == "POST"){
//
//
//  $energie = $_POST['energie'] ?? [];
//  $prixmax = $_POST['prixmax'] ?? null;
//  $dureemax = $_POST['dureemax'] ?? null;
//  $note = $_POST['note'] ?? [];
//
//  echo $dureemax;
////  
////}
////try {
////    $pdo = new PDO('mysql:host=localhost;dbname=ecoride', 'root', '');
////    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
////    require_once "covoit.html";
////    function appliquerFiltres($pdo, $depart, $destination, $date, $energie, $prixmax, $dureemax, $note) {
////      $sql = "
////          SELECT covoiturage.*, utilisateur.pseudo, utilisateur.photo, utilisateur.prenom, voiture.energie, avis.*
////          FROM covoiturage
////          INNER JOIN utilisateur ON covoiturage.covoiturage_id = utilisateur.utilisateur_id
////          INNER JOIN voiture ON utilisateur.utilisateur_id = voiture.voiture_id
////          LEFT JOIN avis ON avis.avis_id = utilisateur.utilisateur_id
////          WHERE covoiturage.lieu_depart = :lieudepart
////          AND covoiturage.lieu_arrivee = :lieuarrivee
////          AND covoiturage.date_depart = :datedepart
////      ";
////  
////      $conditions = [];
////      $params = [
////        
////      ];
////  
////      if (!empty($energie)) {
////          $energieConditions = [];
////          foreach ($energie as $key => $type) {
////              $energieConditions[] = "voiture.energie = :energie_$key";
////              $params[":energie_$key"] = $type;
////          }
////          $conditions[] = "(" . implode(" OR ", $energieConditions) . ")";
////      }
////  
////      if ($prixmax !== null) {
////          $conditions[] = "covoiturage.prix_personne <= :prixmax";
////          $params[':prixmax'] = $prixmax;
////      }
////  
////      if ($dureemax !== null) {
////          $conditions[] = "TIMESTAMPDIFF(HOUR, covoiturage.heure_depart, covoiturage.heure_arrivee) <= :dureemax";
////          $params[':dureemax'] = $dureemax;
////      }
////  
////      if (!empty($note)) {
////          $noteConditions = [];
////          foreach ($note as $key => $rating) {
////              $noteConditions[] = "utilisateur.note = :note_$key";
////              $params[":note_$key"] = $rating;
////          }
////          $conditions[] = "(" . implode(" OR ", $noteConditions) . ")";
////      }
////  
////      if (!empty($conditions)) {
////          $sql .= " AND " . implode(" AND ", $conditions);
////      }
////  
////      $requete = $pdo->prepare($sql);
////      foreach ($params as $key => &$value) {
////          $requete->bindParam($key, $value);
////      }
////  
////      $requete->execute();
////       $requete->fetchAll(PDO::FETCH_ASSOC);
////
////       if (!empty($depart)) {
////        $requete = appliquerFiltres($pdo, $depart, $destination, $date, $energie, $prixmax, $dureemax, $note);
////    
////        if ($requete) {
////            foreach ($requete as $row) {
////                $resultnbplace = htmlspecialchars($row['nb_place']);
////                $resultprix = htmlspecialchars($row['prix_personne']);
////                $resultheuredepart = htmlspecialchars($row['heure_depart']);
////                $resultheurearrivee = htmlspecialchars($row['heure_arrivee']);
////                $resultdepart = htmlspecialchars($row['lieu_depart']);
////                $resultarrivee = htmlspecialchars($row['lieu_arrivee']);
////                $resultdate = htmlspecialchars($row['date_depart']);
////                $resultpseudo = htmlspecialchars($row['pseudo']);
////                $resultenergie = htmlspecialchars($row['energie']);
////                $covoitid = htmlspecialchars($row['covoiturage_id']);
////    
////                $trajetecologique = ($resultenergie == "Essence") ? "Trajet non-écologique" : "Trajet écologique";
////    
////                // Inclusion du fichier affichant les résultats
////                include "covoit.php";
////            }
////        } else {
////            echo "Aucun résultat trouvé.";
////        }
////    } else {
////        echo "Veuillez renseigner un lieu de départ.";
////    }
////  }
////  
////
////
////
////} catch (PDOException $e) {
////    die("Erreur de connexion : " . $e->getMessage());
////}
////
////
////
////

function appliquerFiltres($pdo, $depart, $destination, $date, $energie, $prixmax, $dureemax, $note) {
    $sql = "
        SELECT covoiturage.*, utilisateur.pseudo, utilisateur.photo, utilisateur.prenom, voiture.energie, avis.*
        FROM covoiturage
        INNER JOIN utilisateur ON covoiturage.covoiturage_id = utilisateur.utilisateur_id
        INNER JOIN voiture ON utilisateur.utilisateur_id = voiture.voiture_id
        LEFT JOIN avis ON avis.avis_id = utilisateur.utilisateur_id
        WHERE covoiturage.lieu_depart = :lieudepart
        AND covoiturage.lieu_arrivee = :lieuarrivee
        AND covoiturage.date_depart = :datedepart
    ";

    $conditions = [];
    $params = [
        ':lieudepart' => $depart,
        ':lieuarrivee' => $destination,
        ':datedepart' => $date
    ];

    if (!empty($energie)) {
        $energieConditions = [];
        foreach ($energie as $key => $type) {
            $energieConditions[] = "voiture.energie = :energie_$key";
            $params[":energie_$key"] = $type;
        }
        $conditions[] = "(" . implode(" OR ", $energieConditions) . ")";
    }

    if ($prixmax !== null) {
        $conditions[] = "covoiturage.prix_personne <= :prixmax";
        $params[':prixmax'] = $prixmax;
    }

    if ($dureemax !== null) {
        $conditions[] = "TIMESTAMPDIFF(HOUR, covoiturage.heure_depart, covoiturage.heure_arrivee) <= :dureemax";
        $params[':dureemax'] = $dureemax;
    }

    if (!empty($note)) {
        $noteConditions = [];
        foreach ($note as $key => $rating) {
            $noteConditions[] = "utilisateur.note = :note_$key";
            $params[":note_$key"] = $rating;
        }
        $conditions[] = "(" . implode(" OR ", $noteConditions) . ")";
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $requete = $pdo->prepare($sql);
    foreach ($params as $key => &$value) {
        $requete->bindParam($key, $value);
    }

    $requete->execute();
    return $requete->fetchAll(PDO::FETCH_ASSOC);
}
?>

?>