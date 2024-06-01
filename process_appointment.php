<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "OmnesImmobilier";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Utilisez cette ligne si vous avez installé PHPMailer via Composer

// Fonction pour envoyer l'email de confirmation
function sendConfirmationEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Paramètres du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'omnesimmobilier@outlook.fr'; // Votre nom d'utilisateur SMTP
        $mail->Password = 'asxdr1234'; // Votre mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataires
        $mail->setFrom('omnesimmobilier@outlook.fr', 'Omnes Immobilier');
        $mail->addAddress($to);  // Ajouter un destinataire
        $mail->addAddress('omnesimmobilier@outlook.fr');  // Ajouter votre adresse pour la copie

        // Paramètres de l'email
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
    } catch (Exception $e) {
        echo "Le message n'a pas pu être envoyé. Erreur Mailer: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = $_POST['agent_id'];
    $client_id = $_POST['client_id'];
    $propriété_id = $_POST['propriété_id'];
    $adresse = $_POST['adresse'];
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $dispo_id = $_POST['dispo_id'];
    $commentaires = $_POST['commentaires'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connexion échouée: " . $conn->connect_error);
    }

    $sql = "INSERT INTO rendez_vous (propriété_id, agent_id, client_id, adresse, date, heure, confirmation, commentaires)
            VALUES (?, ?, ?, ?, ?, ?, NULL, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissss", $propriété_id, $agent_id, $client_id, $adresse, $date, $heure, $commentaires);
    if ($stmt->execute()) {
        $sql_update = "UPDATE disponibilités_agents SET disponible = 0 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $dispo_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Récupérer l'email du client
        $sql_client = "SELECT email FROM utilisateurs WHERE id = ?";
        $stmt_client = $conn->prepare($sql_client);
        $stmt_client->bind_param("i", $client_id);
        $stmt_client->execute();
        $result_client = $stmt_client->get_result();
        $client = $result_client->fetch_assoc();
        $stmt_client->close();

        // Envoi de l'email de confirmation
        $to = $client['email']; // Utilise l'email du client
        $subject = "Confirmation de rendez-vous";
        $body = "Votre rendez-vous pour la propriété $propriété_id à l'adresse $adresse le $date à $heure a été confirmé.";
        sendConfirmationEmail($to, $subject, $body);

        echo "<script>
            alert('Rendez-vous pris avec succès! Vous pouvez désormais le consulter dans vos rendez-vous.');
            window.location.href = 'Appartement_a_louer.php';
        </script>";
    } else {
        echo "<script>
            alert('Erreur lors de la prise de rendez-vous. Veuillez réessayer.');
            window.location.href = 'Appartement_a_louer.php';
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>