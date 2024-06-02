<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clientNumber = $_POST['clientNumber'];
    $clientName = $_POST['clientName'];
    $age = $_POST['age'];
    $seatClass = $_POST['seatClass'];
    $clientStatus = $_POST['clientStatus'];

    $errors = [];

    // Vérification des champs vides
    if (empty($clientNumber)) $errors[] = "Numéro du client est vide";
    if (empty($clientName)) $errors[] = "Nom du client est vide";
    if (empty($age)) $errors[] = "Âge est vide";
    if (empty($seatClass)) $errors[] = "Classe de siège est vide";
    if (empty($clientStatus)) $errors[] = "Statut du client est vide";

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:#ff0000;'>Erreur : $error</p>";
        }
        exit;
    }

    // Calcul des rabais selon l'âge
    $discount = 0;
    if ($age >= 0 && $age <= 3) {
        $discount = 0.50;
    } elseif ($age >= 4 && $age <= 5) {
        $discount = 0.20;
    } elseif ($age >= 60) {
        $discount = 0.20;
    }

    // Prix de base d'un ticket en Économie
    $basePrice = 550;

    // Ajustement du prix selon la classe de siège
    switch ($seatClass) {
        case 'Affaire':
            $basePrice *= 2.2;
            break;
        case 'Premiere':
            $basePrice *= 3;
            break;
    }

    // Application du rabais selon l'âge
    $priceAfterAgeDiscount = $basePrice * (1 - $discount);

    // Application du rabais selon le statut du client
    switch ($clientStatus) {
        case 'Fidelite':
            $priceAfterStatusDiscount = $priceAfterAgeDiscount * 0.975;
            break;
        case 'VIP':
            $priceAfterStatusDiscount = $priceAfterAgeDiscount * 0.92;
            break;
        default:
            $priceAfterStatusDiscount = $priceAfterAgeDiscount;
            break;
    }

    // Affichage des informations du client et du prix
    echo "<h2>Informations du Client</h2>";
    echo "<p>Numéro du client : $clientNumber</p>";
    echo "<p>Nom du client : $clientName</p>";
    echo "<p>Âge : $age ans</p>";
    echo "<p>Classe de siège : $seatClass</p>";
    echo "<p>Statut du client : $clientStatus</p>";
    echo "<h2>Prix du Ticket</h2>";
    echo "<p>Le prix que vous allez payer pour voyager en Air France vol AF 123 de Paris à Dubaï ce samedi est de : " . number_format($priceAfterStatusDiscount, 2) . " €</p>";
}
?>
