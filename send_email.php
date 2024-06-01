<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once "vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $to_email = $_POST['to_email'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];

    // Activer ou désactiver les exceptions par variable
    $debug = true;

    try {
        // Créer une instance de classe PHPMailer
        $mail = new PHPMailer($debug);

        if ($debug) {
            // donne un journal détaillé
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        }

        // Authentification via SMTP
        $mail->isSMTP();
        $mail->SMTPAuth = true;

        // Connexion
        $mail->Host = "smtp.office365.com"; // Remplacez par votre hôte SMTP
        $mail->Port = 587; // Utilisez le port correct pour votre serveur SMTP
        $mail->Username = "omnesimmobilier@outlook.fr"; // Votre nom d'utilisateur SMTP
        $mail->Password = "asxdr1234"; // Votre mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Utilisez TLS

        // Paramètres de l'email
        $mail->setFrom('omnesimmobilier@outlook.fr', 'Omnes Immobilier'); // L'adresse de l'expéditeur
        $mail->addAddress($to_email); // Ajouter un destinataire
        $mail->addAddress('omnesimmobilier@outlook.fr'); // Ajouter une copie à vous-même

        // Paramètres de l'email
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        // Envoyer l'email
        $mail->send();
        echo 'Le message a été envoyé';
    } catch (Exception $e) {
        echo "Le message n'a pas pu être envoyé. Erreur Mailer: ".$e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Formulaire de test d'envoi d'email</title>
</head>

<body>
    <h2>Formulaire de test d'envoi d'email</h2>
    <form method="post" action="send_email.php">
        <label for="to_email">Email du destinataire :</label><br>
        <input type="email" id="to_email" name="to_email" required><br><br>
        <label for="subject">Sujet :</label><br>
        <input type="text" id="subject" name="subject" required><br><br>
        <label for="body">Message :</label><br>
        <textarea id="body" name="body" rows="4" cols="50" required></textarea><br><br>
        <input type="submit" value="Envoyer l'email">
    </form>
</body>

</html>
