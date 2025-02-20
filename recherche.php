<?php
session_start();





if  (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    require "filtre.php";
  
  $depart = $_POST["depart"] ?? '';
  $destination = $_POST["destination"] ?? '';
  $date = $_POST["date"] ?? '';
  $energie = $_POST['energie'] ?? [];
  $prixmax = $_POST['prixmax'] ?? null;
  $dureemax = $_POST['dureemax'] ?? null;
  $note = $_POST['note'] ?? [];
    
  echo "JAWSDB_URL: " . getenv('JAWSDB_URL') . "<br>";

  $url = getenv('JAWSDB_URL');
$dbparts = parse_url($url);

$hostname = $dbparts['host'];
$username = $dbparts['user'];
$password = $dbparts[""];
$database = ltrim($dbparts['path'],'/');

    // Vérifiez que les champs nécessaires sont remplis et que la date est valide
    if (!empty($depart) && !empty($destination) && !empty($date)) {
        // Vérifiez le format de la date
        $dateValid = DateTime::createFromFormat('Y-m-d', $date);
        if ($dateValid && $dateValid->format('Y-m-d') === $date) {
            try {
                $pdo = new PDO("mysql:host=$hostname;port=3306;dbname=$database", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $requete = $pdo->prepare("
                    SELECT covoiturage.*, utilisateur.pseudo, utilisateur.photo, voiture.energie
                    FROM covoiturage
                    INNER JOIN utilisateur ON covoiturage.covoiturage_id = utilisateur.utilisateur_id
                    INNER JOIN voiture ON voiture.voiture_id = utilisateur.utilisateur_id
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
                    foreach ($result as $row) {
                        $resultnbplace = htmlspecialchars($row['nb_place']);
                        $resultprix = htmlspecialchars($row['prix_personne']);
                        $resultheuredepart = htmlspecialchars($row['heure_depart']);
                        $resultheurearrivee = htmlspecialchars($row['heure_arrivee']);
                        $resultdepart = htmlspecialchars($row['lieu_depart']);
                        $resultarrivee = htmlspecialchars($row['lieu_arrivee']);
                        $resultdate = htmlspecialchars($row['date_depart']);
                        $resultpseudo = htmlspecialchars($row['pseudo']);
                        $resultenergie = htmlspecialchars($row['energie']);
                        $covoitid = htmlspecialchars($row['covoiturage_id']);

                        $trajetecologique = ($resultenergie == "Essence") ? "Trajet non-écologique" : "Trajet écologique";

                        // Inclure le fichier pour afficher les résultats
                        include "covoit.php";
                    }
                } else {
                    echo "Aucun résultat trouvé.";
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "La date fournie n'est pas valide. Veuillez utiliser le format YYYY-MM-DD.";
        }
    } else {
        echo "Veuillez renseigner tous les champs nécessaires.";
    }
}
?>