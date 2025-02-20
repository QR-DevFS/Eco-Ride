<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Forcer le format JSON
require_once "covoit.html";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $depart = $_POST["depart"];
    $destination = $_POST['destination'];
    $date = $_POST['date'];

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=ecoride', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

       // $requete = $pdo->prepare("SELECT * FROM utilisateur UNION SELECT * FROM covoiturage WHERE covoiturage_id = utilisateur_id AND lieu_depart = :lieudepart AND lieu_arrivee = :lieuarrivee AND date_depart = :datedepart");
       $requete = $pdo->prepare("
    SELECT covoiturage.*, utilisateur.pseudo, utilisateur.photo, voiture.energie
    FROM covoiturage 
    INNER JOIN utilisateur ON covoiturage.covoiturage_id = utilisateur.utilisateur_id
    INNER JOIN voiture ON voiture.voiture_id= utilisateur.utilisateur_id
    WHERE covoiturage.lieu_depart = :lieudepart
    AND covoiturage.lieu_arrivee = :lieuarrivee
    AND covoiturage.date_depart = :datedepart
");

       $requete->bindParam(':lieudepart', $depart, PDO::PARAM_STR);
        $requete->bindParam(':lieuarrivee', $destination, PDO::PARAM_STR);
        $requete->bindParam(':datedepart', $date, PDO::PARAM_STR);
        $requete->execute();
       
       
    
        $result = $requete->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            foreach ($result as $row){
                $resultnbplace=  htmlspecialchars($row['nb_place']);
                $resultprix=htmlspecialchars($row['prix_personne']);
                $resultheuredepart= htmlspecialchars($row['heure_depart']);
                $resultheurearrivee= htmlspecialchars($row['heure_arrivee']);
                $resultdepart = htmlspecialchars($row['lieu_depart']);
                $resultarrivee= htmlspecialchars($row['lieu_arrivee']);
                  $resultdate =htmlspecialchars($row['date_depart']) ;
                  $resultpseudo=htmlspecialchars($row['pseudo']);
                  $resultenergie=htmlspecialchars($row['energie']);
                
                  
                  
                  $trajetecologique = ($resultenergie == "Essence") ? "Trajet non-écologique" : "Trajet écologique";
              

      
      // $resultphoto = htmlspecialchars($result2['photo']);
include "detail.php";
      
            }
            
           
          }
else {
  echo "Aucun résultat trouvé.";
  }
        }
  catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
  }
        }
            

  
  
?>