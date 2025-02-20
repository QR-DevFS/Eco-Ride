<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Charger PHPMailer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'QRdev.fs@gmail.com'; // Remplacez par votre email
        $mail->Password = 'dhrs qybg ffzj egoz'; // Remplacez par votre mot de passe ou App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et Destinataire
        $mail->setFrom($email, $name);
        $mail->addAddress('QRdev.fs@gmail.com'); // Votre adresse de réception

        // Contenu du mail
        $mail->isHTML(true);
        $mail->Subject = "Message de $name";
        $mail->Body    = "<strong>Nom:</strong> $name <br><strong>Email:</strong> $email <br><strong>Message:</strong> $message";

        // Envoi de l'email
        if ($mail->send()) {
          // echo json_encode(["success" => true, "message" => "Email envoyé avec succès !"]);
         
          header("Location: Index_pagecontact.php?success=1");
          exit();
        } else {
            header("Location: Index_pagecontact.php?error=1");
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Erreur: {$mail->ErrorInfo}"]);
    }
}
?>