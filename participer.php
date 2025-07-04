<?php
session_start();
require 'db.php';
function afficherErreur($message, $retour = 'espace_util.php') {
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Erreur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container mt-5">
    <div class="alert alert-danger text-center shadow">
      <h4 class="alert-heading">Une erreur est survenue</h4>
      <p><?= htmlspecialchars($message) ?></p>
      <hr>
      <a href="<?= htmlspecialchars($retour) ?>" class="btn btn-outline-danger">Retour</a>
    </div>
  </div>
</body>

</html>
<?php
    exit;
}
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if (!isset($_POST['covoiturage_id'])) {
    header("Location: index.php");
    exit;
}

$covoiturage_id = intval($_POST['covoiturage_id']);

if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['message'] = "Vous devez vous connecter pour participer à un covoiturage.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['utilisateur_id'];

// Vérifie les infos du covoiturage
$stmt = $pdo->prepare("SELECT nb_place, prix_personne FROM covoiturage WHERE covoiturage_id = ?");
$stmt->execute([$covoiturage_id]);
$covoit = $stmt->fetch();

if (!$covoit || $covoit['nb_place'] <= 0) {
    afficherErreur("Ce covoiturage n'a plus de places disponibles.");
}

// Vérifie s'il participe déjà
$stmt = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE utilisateur_id = ? AND covoiturage_id = ?");
$stmt->execute([$user_id, $covoiturage_id]);
if ($stmt->fetchColumn() > 0) {
   afficherErreur("Vous participez déjà à ce covoiturage.");
}

// Vérifie les crédits
$stmt = $pdo->prepare("SELECT credit FROM utilisateur WHERE utilisateur_id = ?");
$stmt->execute([$user_id]);
$credit = $stmt->fetchColumn();

if ($credit < $covoit['prix_personne']) {
    afficherErreur("Crédit insuffisant pour participer à ce trajet.");
}

// === Confirmation ===
if (!isset($_POST['confirmation'])) {
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container mt-5">
    <div class="card shadow-lg">
      <div class="card-body text-center">
        <h3 class="card-title text-success">Confirmation de participation</h3>
        <p class="mt-3">Ce trajet coûte <strong><?= number_format($covoit['prix_personne'], 2) ?> crédits</strong>.</p>
        <p>Il vous reste <strong><?= number_format($credit, 2) ?> crédits</strong>.</p>
        <form method="post" class="d-inline">
          <input type="hidden" name="covoiturage_id" value="<?= $covoiturage_id ?>">
          <input type="hidden" name="confirmation" value="1">
          <button type="submit" class="btn btn-success mt-3">Confirmer la participation</button>
          <a href="javascript:history.back()" class="btn btn-outline-secondary mt-3 ms-2">Annuler</a>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
<?php
    exit;
}

// === Enregistrement de la participation ===
$pdo->beginTransaction();

try {
    // Enregistre participation
    $stmt = $pdo->prepare("INSERT INTO participation (utilisateur_id, covoiturage_id, statut_validation) VALUES (?, ?, 'valide')");
    $stmt->execute([$user_id, $covoiturage_id]);

    // Déduit crédits
    $stmt = $pdo->prepare("UPDATE utilisateur SET credit = credit - ? WHERE utilisateur_id = ?");
    $stmt->execute([$covoit['prix_personne'], $user_id]);

    // Réduit places
    $stmt = $pdo->prepare("UPDATE covoiturage SET nb_place = nb_place - 1 WHERE covoiturage_id = ?");
    $stmt->execute([$covoiturage_id]);

    $pdo->commit();

    // === Email de confirmation au passager ===
    $stmt = $pdo->prepare("SELECT nom, prenom, email FROM utilisateur WHERE utilisateur_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // à adapter
        $mail->SMTPAuth = true;
        $mail->Username = 'ton@email.com'; // à adapter
        $mail->Password = 'mot_de_passe'; // à adapter
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ton@email.com', 'EcoRide');
        $mail->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de participation au covoiturage';
        $mail->Body = "
            <h3>Bonjour {$user['prenom']},</h3>
            <p>Votre participation au covoiturage n°{$covoiturage_id} a bien été confirmée.</p>
            <p>Merci d’utiliser <strong>EcoRide</strong> !</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur mail passager : " . $mail->ErrorInfo);
    }

    // === Email au chauffeur ===
    $stmt = $pdo->prepare("
        SELECT u.nom, u.prenom, u.email 
        FROM utilisateur u 
        JOIN covoiturage c ON u.utilisateur_id = c.utilisateur_id 
        WHERE c.covoiturage_id = ?
    ");
    $stmt->execute([$covoiturage_id]);
    $chauffeur = $stmt->fetch();

    if ($chauffeur) {
        $mailChauffeur = new PHPMailer(true);
        try {
            $mailChauffeur->isSMTP();
            $mailChauffeur->Host = 'smtp.example.com';
            $mailChauffeur->SMTPAuth = true;
            $mailChauffeur->Username = 'ton@email.com';
            $mailChauffeur->Password = 'mot_de_passe';
            $mailChauffeur->SMTPSecure = 'tls';
            $mailChauffeur->Port = 587;

            $mailChauffeur->setFrom('ton@email.com', 'EcoRide');
            $mailChauffeur->addAddress($chauffeur['email'], $chauffeur['prenom'] . ' ' . $chauffeur['nom']);
            $mailChauffeur->isHTML(true);
            $mailChauffeur->Subject = 'Un passager a rejoint votre covoiturage';
            $mailChauffeur->Body = "
                <h3>Bonjour {$chauffeur['prenom']},</h3>
                <p>Un nouveau passager vient de rejoindre votre covoiturage n°{$covoiturage_id}.</p>
                <p>Merci d’utiliser <strong>EcoRide</strong> !</p>
            ";
            $mailChauffeur->send();
        } catch (Exception $e) {
            error_log("Erreur mail chauffeur : " . $mailChauffeur->ErrorInfo);
        }
    }

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de la participation : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Participation confirmée</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container mt-5">
    <div class="alert alert-success text-center shadow">
      <h4 class="alert-heading">Participation confirmée !</h4>
      <p>Vous avez bien rejoint le covoiturage. Un email de confirmation vous a été envoyé.</p>
      <hr>
      <a href="mes_reservations.php" class="btn btn-primary">Voir mes réservations</a>
    </div>
  </div>
</body>

</html>