<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "OmnesImmobilier";

// Créez une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $expéditeur_id = $data['expéditeur_id'];
    $destinataire_id = $data['destinataire_id'];
    $message = $data['message'];

    $stmt = $conn->prepare("INSERT INTO messages (expéditeur_id, destinataire_id, message, date, heure, type) VALUES (?, ?, ?, CURDATE(), CURTIME(), 'Texto')");
    $stmt->bind_param("iis", $expéditeur_id, $destinataire_id, $message);
    $stmt->execute();

    echo json_encode(['success' => true]);
}
?>
