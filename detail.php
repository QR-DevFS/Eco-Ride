<?php

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $covoiturage_id = $_GET['id'];
    $condition="";
    $url = getenv('JAWSDB_URL');

    if (!$url) {
        die("❌ Erreur : JAWSDB_URL non définie. Vérifiez vos variables d'environnement Heroku.");
    }

   

    // Parsing de l'URL pour extraire les identifiants de connexion
    $dbparts = parse_url($url);

    $hostname = $dbparts['host'] ?? '';
    $username = $dbparts['user'] ?? '';
    $password = $dbparts['pass'] ?? '';
    $database = ltrim($dbparts['path'] ?? '', '/');
    $port = $dbparts['port'] ?? 3306;

    // Vérification des valeurs extraites
    if (empty($hostname) || empty($username) || empty($database)) {
        die("❌ Erreur : Problème avec la configuration de la base de données.");
    }

    try {
        $pdo = new PDO("mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $requete = $pdo->prepare("
            SELECT
    covoiturage.*,
    avis.*,
    utilisateur.pseudo,
    utilisateur.prenom,
    utilisateur.photo,
    voiture.energie,
    voiture.modèle,
    marque.libelle
FROM
    covoiturage
INNER JOIN
    utilisateur ON covoiturage.covoiturage_id = utilisateur.utilisateur_id
INNER JOIN
    voiture ON utilisateur.utilisateur_id = voiture.voiture_id
INNER JOIN
    marque ON voiture.voiture_id = marque.marque_id
   LEFT JOIN
    avis ON avis.avis_id = utilisateur.utilisateur_id OR avis.commentaire= :condition
WHERE
    covoiturage.covoiturage_id = :covoiturage_id

        ");
        $requete->bindParam(':covoiturage_id', $covoiturage_id, PDO::PARAM_INT);
        $requete->bindParam(':condition', $condition, PDO::PARAM_STR);
        $requete->execute();

        $result = $requete->fetch(PDO::FETCH_ASSOC);
$resultavis =" ";
        if ($result) {
            $resultnbplace = htmlspecialchars($result['nb_place']);
            $resultprix = htmlspecialchars($result['prix_personne']);
            $resultheuredepart = htmlspecialchars($result['heure_depart']);
            $resultheurearrivee = htmlspecialchars($result['heure_arrivee']);
            $resultdepart = htmlspecialchars($result['lieu_depart']);
            $resultarrivee = htmlspecialchars($result['lieu_arrivee']);
            $resultdate = htmlspecialchars($result['date_depart']);
            $resultpseudo = htmlspecialchars($result['pseudo']);
            $resultphoto = htmlspecialchars($result['photo']);
            $resultenergie = htmlspecialchars($result['energie']);
            $prenomutil=htmlspecialchars($result['prenom']);
            $resultmarque =htmlspecialchars($result['libelle']);
            $resultmodele=htmlspecialchars($result['modèle']);
            $resultavis=($result['commentaire']);
            $resultnote=($result['note']);
            $trajetecologique = ($resultenergie == "Essence") ? "Trajet non-écologique" : "Trajet écologique";
            $avis =($resultavis=="")?"Pas d'avis sur cette personne pour le moment": $resultavis;
            // Display results
            include "detail_trame.php";
        } else {
            echo "Aucun résultat trouvé.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ID de covoiturage non spécifié.";
}
?>