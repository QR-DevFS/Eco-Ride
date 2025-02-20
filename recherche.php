<?php
session_start();

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    require "filtre.php";

    $depart = $_POST["depart"] ?? '';
    $destination = $_POST["destination"] ?? '';
    $date = $_POST["date"] ?? '';
    $energie = $_POST['energie'] ?? [];
    $prixmax = $_POST['prixmax'] ?? null;
    $dureemax = $_POST['dureemax'] ?? null;
    $note = $_POST['note'] ?? [];

    // Vérification de l'URL JAWSDB
    $url = getenv('JAWSDB_URL');

    if (!$url) {
        die("❌ Erreur : JAWSDB_URL non définie. Vérifiez vos variables d'environnement Heroku.");
    }

    echo "🔗 JAWSDB_URL: " . htmlspecialchars($url) . "<br>";

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
        // Connexion avec `utf8mb4` pour éviter les problèmes d'encodage
        $pdo = new PDO("mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "✅ Connexion réussie à la base de données !<br>";

        // Vérifier si la table existe
        $result = $pdo->query("SHOW TABLES LIKE 'covoiturage'");
        if ($result->rowCount() == 0) {
            die("❌ Erreur : La table 'covoiturage' n'existe pas dans la base de données.");
        }

        // Préparation de la requête SQL
        $requete = $pdo->prepare("
            SELECT covoiturage.*, utilisateur.pseudo, utilisateur.photo, voiture.energie
            FROM covoiturage
            INNER JOIN utilisateur ON utilisateur.utilisateur_id = covoiturage.covoiturage_id
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
            echo "🚫 Aucun résultat trouvé.";
        }
    } catch (PDOException $e) {
        die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
    }
}
?>