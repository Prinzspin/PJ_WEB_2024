<?php
session_start();

// Détails de connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "omnesimmobilier";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include PHPMailer
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

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Le message n'a pas pu être envoyé. Erreur Mailer: {$mail->ErrorInfo}";
    }
}

// Supposons que l'ID utilisateur est stocké dans la session après la connexion
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$user = null;
$cards_info = [];

if ($user_id) {
    // Récupération des informations de l'utilisateur
    $sql = "SELECT * FROM utilisateurs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && $user['type_utilisateur'] == 'Client') {
        // Récupération des paiements pour le client
        $sql_payment = "SELECT * FROM paiements WHERE client_id = ?";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("i", $user_id);
        $stmt_payment->execute();
        $result_payment = $stmt_payment->get_result();
        
        $paiement_ids = [];
        while ($row = $result_payment->fetch_assoc()) {
            $paiement_ids[] = $row['id'];
        }
        $stmt_payment->close();

        if (!empty($paiement_ids)) {
            // Récupération des cartes de crédit associées aux paiements
            $placeholders = implode(',', array_fill(0, count($paiement_ids), '?'));
            $sql_cards = "SELECT * FROM cartes_credit WHERE paiement_id IN ($placeholders)";
            $stmt_cards = $conn->prepare($sql_cards);
            $stmt_cards->bind_param(str_repeat('i', count($paiement_ids)), ...$paiement_ids);
            $stmt_cards->execute();
            $result_cards = $stmt_cards->get_result();
            
            while ($row = $result_cards->fetch_assoc()) {
                $cards_info[] = $row;
            }
            $stmt_cards->close();
        }
    }
}

// Gestion de la transaction de location d'appartement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rent_property'])) {
    $property_id = $_POST['property_id'];
    $rental_fee = $_POST['rental_fee'];
    $card_id = $_POST['card_id'];

    // Récupération des informations de la carte de crédit sélectionnée
    $sql_card_info = "SELECT * FROM cartes_credit WHERE id = ?";
    $stmt_card_info = $conn->prepare($sql_card_info);
    $stmt_card_info->bind_param("i", $card_id);
    $stmt_card_info->execute();
    $result_card_info = $stmt_card_info->get_result();
    $card_info = $result_card_info->fetch_assoc();
    $stmt_card_info->close();

    // Vérification si la limite de la carte de crédit permet de faire la transaction
    $credit_limit = $card_info['limite_credit'];
    $card_balance = $card_info['balance']; // Solde utilisé réel

    if ($rental_fee <= ($credit_limit - $card_balance)) {
        // La transaction passe
        // Mise à jour du solde de la carte
        $new_balance = $card_balance + $rental_fee;
        $sql_update_balance = "UPDATE cartes_credit SET balance = ? WHERE id = ?";
        $stmt_update_balance = $conn->prepare($sql_update_balance);
        $stmt_update_balance->bind_param("di", $new_balance, $card_id);
        $stmt_update_balance->execute();
        $stmt_update_balance->close();

        // Insertion de la transaction dans la table paiements
        $sql_rent = "INSERT INTO paiements (client_id, propriété_id, montant, date, moyen_de_paiement, confirmation) VALUES (?, ?, ?, NOW(), 'Carte de crédit', 1)";
        $stmt_rent = $conn->prepare($sql_rent);
        $stmt_rent->bind_param("iid", $user_id, $property_id, $rental_fee);
        if ($stmt_rent->execute()) {
            // Envoi de l'email de confirmation
            $to = $user['email']; // Utilise l'email de l'utilisateur connecté
            $subject = "Confirmation de la transaction";
            $body = "Votre transaction de $rental_fee € pour la propriété $property_id a été approuvée.";
            sendConfirmationEmail($to, $subject, $body);

            echo "<script>alert('Transaction réussie.'); window.location.href='Mon_compte.php';</script>";
        } else {
            // Envoi de l'email d'erreur
            $to = $user['email']; // Utilise l'email de l'utilisateur connecté
            $subject = "Échec de la transaction";
            $body = "Erreur lors de la transaction de $rental_fee € pour la propriété $property_id.";
            sendConfirmationEmail($to, $subject, $body);

            echo "<script>alert('Erreur lors de la transaction.');</script>";
        }
        $stmt_rent->close();
    } else {
        // La transaction échoue
        // Envoi de l'email de dépassement de limite
        $to = $user['email']; // Utilise l'email de l'utilisateur connecté
        $subject = "Échec de la transaction";
        $body = "Votre transaction de $rental_fee euros pour la propriete $property_id a echoue. La limite de credit est depassee.";
        sendConfirmationEmail($to, $subject, $body);

        echo "<script>alert('Transaction refusee. Limite de credit depassee.'); window.location.href='Mon_compte.php';</script>";
    }
}
$property_added = false;
$agent_added = false;

$agent = null; // Initialize agent variable

// Gestion de l'ajout de propriété
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_property'])) {
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $code_postal = $_POST['code_postal'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $superficie = $_POST['superficie'];
    $nombre_de_chambres = $_POST['nombre_de_chambres'];
    $nombre_de_salles_de_bain = $_POST['nombre_de_salles_de_bain'];
    $balcon = isset($_POST['balcon']) ? 1 : 0;
    $parking = isset($_POST['parking']) ? 1 : 0;
    $photos = $_FILES['photos']['name'];
    $videos = $_FILES['videos']['name'];
    $specialite = $_POST['specialite'];

    // Handle file uploads for photos
    $target_dir_photos = "uploads/photos/";
    $target_file_photos = $target_dir_photos . basename($_FILES["photos"]["name"]);
    move_uploaded_file($_FILES["photos"]["tmp_name"], $target_file_photos);

    // Handle file uploads for videos
    $target_dir_videos = "uploads/videos/";
    $target_file_videos = $target_dir_videos . basename($_FILES["videos"]["name"]);
    move_uploaded_file($_FILES["videos"]["tmp_name"], $target_file_videos);

    // Trouver l'agent avec la spécialité correspondante et le moins d'affectations, et prioriser par ID en cas d'égalité
    $sql_agents = "SELECT agents.id, COUNT(propriétés.id) AS affectations
                   FROM agents
                   LEFT JOIN propriétés ON agents.id = propriétés.agent_id
                   WHERE agents.specialité = ?
                   GROUP BY agents.id
                   ORDER BY affectations ASC, agents.id ASC
                   LIMIT 1";
    $stmt_agents = $conn->prepare($sql_agents);
    $stmt_agents->bind_param("s", $specialite);
    $stmt_agents->execute();
    $result_agents = $stmt_agents->get_result();
    $selected_agent = $result_agents->fetch_assoc();
    $stmt_agents->close();

    if ($selected_agent) {
        $agent_id = $selected_agent['id'];

        // Insertion des données dans la table propriétés
        $sql_insert = "INSERT INTO propriétés (type, adresse, ville, code_postal, description, prix, superficie, nombre_de_chambres, nombre_de_salles_de_bain, balcon, parking, photos, videos, agent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssssdiiiiissi", $specialite, $adresse, $ville, $code_postal, $description, $prix, $superficie, $nombre_de_chambres, $nombre_de_salles_de_bain, $balcon, $parking, $target_file_photos, $target_file_videos, $agent_id);

        if ($stmt_insert->execute()) {
            $property_added = true;
        } else {
            echo "Erreur: " . $sql_insert . "<br>" . $conn->error;
        }

        $stmt_insert->close();

        // Retrieve the selected agent's information
        $sql_agent_info = "SELECT * FROM agents WHERE id = ?";
        $stmt_agent_info = $conn->prepare($sql_agent_info);
        $stmt_agent_info->bind_param("i", $agent_id);
        $stmt_agent_info->execute();
        $result_agent_info = $stmt_agent_info->get_result();
        $agent = $result_agent_info->fetch_assoc();
        $stmt_agent_info->close();
    } else {
        echo "<script>alert('Aucun agent trouvé avec la spécialité demandée.');</script>";
    }
}




// Gestion de l'ajout d'agent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_agent'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $specialite = $_POST['specialite'];
    $cv = $_FILES['cv']['name'];
    $photo = $_FILES['photo']['name'];

    // Handle file uploads for CV
    $target_dir_cv = "uploads/cv/";
    $target_file_cv = $target_dir_cv . basename($_FILES["cv"]["name"]);
    move_uploaded_file($_FILES["cv"]["tmp_name"], $target_file_cv);

    // Handle file uploads for photo
    $target_dir_photo = "uploads/photo/";
    $target_file_photo = $target_dir_photo . basename($_FILES["photo"]["name"]);
    move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file_photo);

    // Génération d'un mot de passe aléatoire
    $password = bin2hex(random_bytes(8)); // 16 caractères hexadécimaux
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insertion des données dans la table utilisateurs
    $sql_insert_user = "INSERT INTO utilisateurs (nom, prénom, email, mot_de_passe, type_utilisateur, numéro_téléphone, cv, photo) VALUES (?, ?, ?, ?, 'Agent', ?, ?, ?)";
    $stmt_insert_user = $conn->prepare($sql_insert_user);
    $stmt_insert_user->bind_param("sssssss", $nom, $prenom, $email, $hashed_password, $telephone, $target_file_cv, $target_file_photo);

    if ($stmt_insert_user->execute()) {
        // Récupérer l'ID de l'utilisateur nouvellement ajouté
        $new_user_id = $stmt_insert_user->insert_id;

        // Insertion des données dans la table agents
        $sql_insert_agent = "INSERT INTO agents (id, nom, prénom, email, numéro_téléphone, cv, photo, specialité) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_agent = $conn->prepare($sql_insert_agent);
        $stmt_insert_agent->bind_param("isssssss", $new_user_id, $nom, $prenom, $email, $telephone, $target_file_cv, $target_file_photo, $specialite);

        if ($stmt_insert_agent->execute()) {
            $agent_added = true;

            // Insérer les créneaux horaires de base pour l'agent nouvellement ajouté
            $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
            $hours = ['09:00-10:00', '10:00-11:00', '11:00-12:00', '13:00-14:00', '14:00-15:00', '15:00-16:00', '16:00-17:00', '17:00-18:00'];
            $sql_dispo_insert = "INSERT INTO disponibilités_agents (agent_id, jour, créneau_horaire, disponible) VALUES (?, ?, ?, 1)";
            $stmt_dispo_insert = $conn->prepare($sql_dispo_insert);

            foreach ($days as $day) {
                foreach ($hours as $hour) {
                    $stmt_dispo_insert->bind_param("iss", $new_user_id, $day, $hour);
                    $stmt_dispo_insert->execute();
                }
            }

            $stmt_dispo_insert->close();
        } else {
            echo "Erreur: " . $sql_insert_agent . "<br>" . $conn->error;
        }

        $stmt_insert_agent->close();
    } else {
        echo "Erreur: " . $sql_insert_user . "<br>" . $conn->error;
    }

    $stmt_insert_user->close();
}

// Gestion de la recherche et suppression des propriétés
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_property'])) {
    $sql_search_property = "SELECT * FROM propriétés WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($_POST['property_search'])) {
        $sql_search_property .= " AND (adresse LIKE ? OR ville LIKE ? OR code_postal LIKE ?)";
        $like_search = "%" . $_POST['property_search'] . "%";
        $params[] = $like_search;
        $params[] = $like_search;
        $params[] = $like_search;
        $types .= "sss";
    }
    if (!empty($_POST['search_specialite'])) {
        $sql_search_property .= " AND type = ?";
        $params[] = $_POST['search_specialite'];
        $types .= "s";
    }
    if (!empty($_POST['search_chambres'])) {
        $sql_search_property .= " AND nombre_de_chambres = ?";
        $params[] = $_POST['search_chambres'];
        $types .= "i";
    }
    if (!empty($_POST['search_pieces'])) {
        $sql_search_property .= " AND Nombre_de_pièce = ?";
        $params[] = $_POST['search_pieces'];
        $types .= "i";
    }

    $stmt_search_property = $conn->prepare($sql_search_property);
    if ($params) {
        $stmt_search_property->bind_param($types, ...$params);
    }
    $stmt_search_property->execute();
    $result_search_property = $stmt_search_property->get_result();
}

// Gestion de la suppression des propriétés
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_property'])) {
    $property_id = $_POST['property_id'];
    $sql_delete_property = "DELETE FROM propriétés WHERE id = ?";
    $stmt_delete_property = $conn->prepare($sql_delete_property);
    $stmt_delete_property->bind_param("i", $property_id);
    $stmt_delete_property->execute();
    $stmt_delete_property->close();
}

// Gestion de la recherche et suppression des agents
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_agent'])) {
    $sql_search_agent = "SELECT * FROM agents WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($_POST['agent_search_nom'])) {
        $sql_search_agent .= " AND nom LIKE ?";
        $like_search = "%" . $_POST['agent_search_nom'] . "%";
        $params[] = $like_search;
        $types .= "s";
    }
    if (!empty($_POST['agent_search_prenom'])) {
        $sql_search_agent .= " AND prénom LIKE ?";
        $like_search = "%" . $_POST['agent_search_prenom'] . "%";
        $params[] = $like_search;
        $types .= "s";
    }
    if (!empty($_POST['agent_search_specialite'])) {
        $sql_search_agent .= " AND specialité = ?";
        $params[] = $_POST['agent_search_specialite'];
        $types .= "s";
    }

    $stmt_search_agent = $conn->prepare($sql_search_agent);
    if ($params) {
        $stmt_search_agent->bind_param($types, ...$params);
    }
    $stmt_search_agent->execute();
    $result_search_agent = $stmt_search_agent->get_result();
}

// Gestion de la suppression des agents
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_agent'])) {
    $agent_id = $_POST['agent_id'];

    // Supprimer les créneaux horaires associés à l'agent
    $sql_delete_dispo = "DELETE FROM disponibilités_agents WHERE agent_id = ?";
    $stmt_delete_dispo = $conn->prepare($sql_delete_dispo);
    $stmt_delete_dispo->bind_param("i", $agent_id);
    $stmt_delete_dispo->execute();
    $stmt_delete_dispo->close();

    // Supprimer l'agent
    $sql_delete_agent = "DELETE FROM agents WHERE id = ?";
    $stmt_delete_agent = $conn->prepare($sql_delete_agent);
    $stmt_delete_agent->bind_param("i", $agent_id);
    $stmt_delete_agent->execute();
    $stmt_delete_agent->close();

    // Supprimer l'utilisateur correspondant
    $sql_delete_user = "DELETE FROM utilisateurs WHERE id = ?";
    $stmt_delete_user = $conn->prepare($sql_delete_user);
    $stmt_delete_user->bind_param("i", $agent_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Menu Omnes Immobilier</title>
    <link rel="stylesheet" type="text/css" href="Accueil.css" />
    <link rel="stylesheet" type="text/css" href="Formulaire.css" />
    <link rel="stylesheet" type="text/css" href="Mon_compte.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <script>
    function showAlert() {
        alert('Nouvelle propriété ajoutée avec succès. Vous pouvez en envoyer d\'autres.');
    }

    function showAgentAlert() {
        alert('Nouvel agent ajouté avec succès. Vous pouvez en ajouter d\'autres.');
    }

    function toggleSoldProperty(element) {
        element.classList.toggle('sold');
    }

    function handleFormSubmit(event) {
        const selectedProperties = document.querySelectorAll('.property-checkbox:checked');
        if (selectedProperties.length === 0) {
            alert('Veuillez sélectionner au moins une propriété à marquer comme vendue.');
            event.preventDefault();
        }
    }
    </script>
    <style>
    .payment-info table {
        margin-left: 15%;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15%;
        font-size: 10px;
    }

    .payment-info th,
    .payment-info td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .payment-info th {
        background-color: #f2f2f2;
        color: black;
    }

    .payment-info tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .payment-info tr:hover {
        background-color: #ddd;
    }

    .payment-info th,
    .payment-info td {
        padding: 12px;
        text-align: left;
    }

    .payment-info tr th {
        font-weight: bold;
    }
    </style>


</head>

<body>
    <div class="WRAPPER">
        <div class="header">
            <h1>Omnes Immobilier <img src="LOGO.png" alt="Omnes Logo" /></h1>
        </div>
        <div class="NAVIGATION">
            <ul>
                <li><a href="Accueil.php">Accueil</a></li>
                <li>
                    <a href="#">Tout Parcourir</a>
                    <ul class="submenu">
                        <li>
                            <a href="Immobilier_résidentiel.php">Immobilier résidentiel</a>
                        </li>
                        <li>
                            <a href="Immobilier_commercial.php">Immobilier commercial</a>
                        </li>
                        <li><a href="Terrain.php">Terrain</a></li>
                        <li>
                            <a href="Appartement_a_louer.php">Appartement à louer</a>
                        </li>
                    </ul>
                </li>
                <li><a href="#">Recherche</a></li>
                <li><a href="Rendez_vous.php">Rendez-vous</a></li>
                <li>
                    <a href="#">Votre Compte</a>
                    <ul class="submenu">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="#">Mon Compte</a></li>
                        <li><a href="logout.php">Se déconnecter</a></li>
                        <?php else: ?>
                        <li>
                            <br />
                            <button class="login-button" onclick="My_function3()">Se connecter</button>
                        </li>
                        <li>
                            <button class="login-button" onclick="My_function()">Créer un compte</button>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="SECTION">
            <h2>Informations du Compte</h2>
            <?php if ($user): ?>
            <div class="account-info">
                <label>Type de Compte:</label>
                <span class="value"><?php echo htmlspecialchars($user['type_utilisateur']); ?></span>
            </div>
            <div class="global">
                <?php if ($user['type_utilisateur'] == 'Client'): ?>
                <div class="g1">
                    <button onclick="toggleDetails('personal-info')"><i class="fas fa-user"></i> &nbsp Informations
                        Personnelles</button>
                    <div id="personal-info" class="details">
                        <div>
                            <label>Nom:</label>
                            <span><?php echo htmlspecialchars($user['nom']); ?></span>
                        </div>
                        <div>
                            <label>Prénom:</label>
                            <span><?php echo htmlspecialchars($user['prénom']); ?></span>
                        </div>
                        <div>
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div>
                            <label>Adresse:</label>
                            <span><?php echo htmlspecialchars($user['adresse1']); ?></span>
                        </div>
                        <div>
                            <label>Numéro de Téléphone:</label>
                            <span><?php echo htmlspecialchars($user['numéro_téléphone']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="g2">
                    <button onclick="window.location.href='Rendez_vous.php'"><i class="fas fa-history"></i> &nbsp
                        Historique des Consultations</button>
                    <div id="appointments" class="details">

                    </div>
                </div>
                <div class="g3">
                    <button onclick="window.location.href='Rendez_vous.php'"><i class="fas fa-calendar-alt"></i> &nbsp
                        Rendez-vous</button>
                    <div id="appointments" class="details">

                    </div>
                </div>

                <div class="g4">
                    <button onclick="toggleDetails('paid-services')"><i class="fas fa-dollar-sign"></i> &nbsp Services
                        Payants</button>
                    <div id="paid-services" class="details">
                        <h3>Transaction de Location d'Appartement</h3>
                        <form id="rent-form" method="post" action="">
                            <div class="form-group">
                                <label>ID de la Propriété:</label>
                                <input type="text" id="property_id" name="property_id" required>
                                <button type="button" id="fetch-property-price">Vérifier le Prix</button>
                            </div>
                            <div class="form-group">
                                <label>Code Jours Spéciaux:</label>
                                <input type="text" id="special_day_code" name="special_day_code">
                            </div>
                            <div class="form-group">
                                <label>Code Fidélité:</label>
                                <input type="text" id="fidelity_code" name="fidelity_code">
                            </div>
                            <div class="form-group">
                                <label>Frais de Location (incluant frais d'agence):</label>
                                <input type="text" id="rental_fee" name="rental_fee" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Sélectionner une Carte de Crédit:</label>
                                <select id="card_id" name="card_id" required>
                                    <option value="" disabled selected>Choisissez une carte</option>
                                    <?php foreach ($cards_info as $card): ?>
                                    <option value="<?php echo $card['id']; ?>"
                                        data-name="<?php echo $card['nom_sur_carte']; ?>"
                                        data-number="<?php echo $card['numero_carte']; ?>"
                                        data-expiration="<?php echo $card['date_expiration']; ?>"
                                        data-type="<?php echo $card['type_carte']; ?>"
                                        data-address="<?php echo $card['adresse_facturation']; ?>">
                                        <?php echo $card['type_carte'] . ' - ' . $card['nom_sur_carte'] . ' - ' . $card['numero_carte']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <h4>Résumé de la Carte Sélectionnée</h4>
                                <p id="card-summary">Aucune carte sélectionnée.</p>
                            </div>
                            <button type="submit" name="rent_property">Effectuer la Transaction</button>
                        </form>
                        <p id="transaction-result"></p>
                    </div>


                </div>
                <div class="g5">
                    <button onclick="toggleDetails('payment-options')"><i class="fas fa-credit-card"></i> &nbsp Options
                        de Paiement</button>
                    <div id="payment-options" class="details">
                        <h3>Options de Paiement</h3>
                        <?php if (!empty($cards_info)): ?>
                        <div class="payment-info">
                            <table>
                                <tr>
                                    <th>Nom sur la Carte</th>
                                    <th>Numéro de Carte</th>
                                    <th>Date d'Expiration</th>
                                    <th>Adresse de Facturation</th>
                                    <th>Type de Carte</th>
                                    <th>Actions</th>
                                </tr>
                                <?php foreach ($cards_info as $card): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($card['nom_sur_carte']); ?></td>
                                    <td><?php echo htmlspecialchars($card['numero_carte']); ?></td>
                                    <td><?php echo htmlspecialchars($card['date_expiration']); ?></td>
                                    <td><?php echo htmlspecialchars($card['adresse_facturation']); ?></td>
                                    <td><?php echo htmlspecialchars($card['type_carte']); ?></td>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                            <button type="submit" name="delete_payment">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <?php else: ?>
                        <p>Aucune information de paiement disponible.</p>
                        <?php endif; ?>

                        <h3>Ajouter une Nouvelle Carte de Crédit</h3>
                        <form method="post" action="">
                            <div class="form-group">
                                <label>Nom sur la Carte:</label>
                                <input type="text" name="card_name" required>
                            </div>
                            <div class="form-group">
                                <label>Numéro de Carte:</label>
                                <input type="text" name="card_number" required>
                            </div>
                            <div class="form-group">
                                <label>Date d'Expiration:</label>
                                <input type="text" name="expiration_date" placeholder="MM/YY" required>
                            </div>
                            <div class="form-group">
                                <label>Code de Sécurité:</label>
                                <input type="text" name="security_code" required>
                            </div>
                            <div class="form-group">
                                <label>Adresse de Facturation:</label>
                                <textarea name="billing_address" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Type de Carte:</label>
                                <select name="card_type" required>
                                    <option value="Visa">Visa</option>
                                    <option value="Mastercard">Mastercard</option>
                                    <option value="American Express">American Express</option>
                                    <option value="Discover">Discover</option>
                                </select>
                            </div>
                            <button type="submit" name="add_payment">Ajouter la Carte</button>
                        </form>
                    </div>

                </div>
                <?php elseif ($user['type_utilisateur'] == 'Agent'): ?>
                <div class="g1">
                    <button onclick="toggleDetails('professional-info')"><i class="fas fa-user-tie"></i> &nbsp
                        Informations Professionnelles</button>
                    <div id="professional-info" class="details">
                        <div>
                            <label>Nom:</label>
                            <span><?php echo htmlspecialchars($user['nom']); ?></span>
                        </div>
                        <div>
                            <label>Prénom:</label>
                            <span><?php echo htmlspecialchars($user['prénom']); ?></span>
                        </div>
                        <div>
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div>
                            <label>Spécialité:</label>
                            <span><?php echo htmlspecialchars($agent['specialité']  ); ?></span>
                        </div>

                        <div>
                            <label>CV:</label>
                            <button onclick="viewCV('<?php echo htmlspecialchars($agent['cv'] ?? ''); ?>')">Consulter le
                                CV</button>
                        </div>



                    </div>
                </div>


                <div class="g2">
                    <button onclick="window.location.href='Rendez_vous.php'"><i class="fas fa-clock"></i> &nbsp
                        Disponibilité</button>
                    <div id="current-consultations" class="details">

                    </div>
                </div>
                <div class="g3">
                    <button onclick="window.location.href='Rendez_vous.php'"><i class="fas fa-calendar-check"></i>&nbsp
                        Consultations Courantes ou à Venir</button>
                    <div id="current-consultations" class="details">

                    </div>
                </div>

                <div class="g4">
                    <button onclick="toggleDetails('communication-methods')"><i class="fas fa-comments"></i> &nbsp
                        Moyens de Communication</button>
                    <div id="communication-methods" class="details">
                        <p>Moyens de communication (à implémenter).</p>
                    </div>
                </div>
                <?php elseif ($user['type_utilisateur'] == 'Admin'): ?>
                <?php if ($property_added): ?>
                <script>
                showAlert();
                </script>
                <?php endif; ?>
                <?php if ($agent_added): ?>
                <script>
                showAgentAlert();
                </script>
                <?php endif; ?>
                <div class="g1">
                    <button onclick="toggleDetails('manage-properties')"><i class="fas fa-building"></i> &nbsp Gestion
                        des Biens Immobiliers</button>
                    <div id="manage-properties" class="details">
                        <div class="form-container">
                            <form method="post" action="" enctype="multipart/form-data">
                                <h3>Ajouter une nouvelle propriété</h3>
                                <div class="form-group">
                                    <label>Spécialité:</label>
                                    <select name="specialite" required>
                                        <option value="Immobilier résidentiel">Immobilier résidentiel</option>
                                        <option value="Immobilier commercial">Immobilier commercial</option>
                                        <option value="Terrain">Terrain</option>
                                        <option value="Appartement à louer">Appartement à louer</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Adresse:</label>
                                    <input type="text" name="adresse" required>
                                </div>
                                <div class="form-group">
                                    <label>Ville:</label>
                                    <input type="text" name="ville" required>
                                </div>
                                <div class="form-group">
                                    <label>Code Postal:</label>
                                    <input type="text" name="code_postal" required>
                                </div>
                                <div class="form-group">
                                    <label>Description:</label>
                                    <textarea name="description" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Prix:</label>
                                    <input type="number" step="0.01" name="prix" required>
                                </div>
                                <div class="form-group">
                                    <label>Superficie:</label>
                                    <input type="number" step="0.01" name="superficie">
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Chambres:</label>
                                    <input type="number" name="nombre_de_chambres">
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Salles de Bain:</label>
                                    <input type="number" name="nombre_de_salles_de_bain">
                                </div>
                                <div class="form-group">
                                    <label>Balcon:</label>
                                    <input type="checkbox" name="balcon">
                                </div>
                                <div class="form-group">
                                    <label>Parking:</label>
                                    <input type="checkbox" name="parking">
                                </div>
                                <div class="form-group">
                                    <label>Photos:</label>
                                    <input type="file" name="photos" id="photos" accept="image/*"
                                        onchange="previewImage(event)">
                                    <img id="photo-preview" src="#" alt="Photo Preview"
                                        style="display:none; max-width: 200px;">
                                </div>
                                <div class="form-group">
                                    <label>Videos:</label>
                                    <input type="file" name="videos" id="videos" accept="video/*"
                                        onchange="previewVideo(event)">
                                    <video id="video-preview" controls style="display:none; max-width: 200px;"></video>
                                </div>
                                <button type="submit" name="add_property">Ajouter la Propriété</button>
                            </form>
                        </div>
                        <div class="search-section">
                            <div class="form-container">
                                <form method="post" action="">
                                    <h3>Rechercher des propriétés</h3>
                                    <div class="form-group">
                                        <label>Recherche:</label>
                                        <input type="text" name="property_search">
                                    </div>
                                    <div class="form-group">
                                        <label>Spécialité:</label>
                                        <select name="search_specialite">
                                            <option value="">--Sélectionner--</option>
                                            <option value="Résidentiel">Immobilier résidentiel</option>
                                            <option value="Commercial">Immobilier commercial</option>
                                            <option value="Terrain">Terrain</option>
                                            <option value="Appartement">Appartement à louer</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Nombre de Chambres:</label>
                                        <input type="number" name="search_chambres">
                                    </div>
                                    <div class="form-group">
                                        <label>Nombre de Pièces:</label>
                                        <input type="number" name="search_pieces">
                                    </div>
                                    <button type="submit" name="search_property">Rechercher</button>
                                </form>
                            </div>
                            <?php if (isset($result_search_property) && $result_search_property->num_rows > 0): ?>
                            <div class="table-result">
                                <h3></h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Adresse</th>
                                            <th>Ville</th>
                                            <th>Code Postal</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($property = $result_search_property->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="<?php echo $property['photos']; ?>" alt="Photo"></td>
                                            <td><?php echo $property['adresse']; ?></td>
                                            <td><?php echo $property['ville']; ?></td>
                                            <td><?php echo $property['code_postal']; ?></td>

                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="g2">
                    <button onclick="toggleDetails('manage-agents')"><i class="fas fa-users"></i> &nbsp Gestion des
                        Agents Immobiliers</button>
                    <div id="manage-agents" class="details">
                        <div class="form-container">
                            <form method="post" action="" enctype="multipart/form-data">
                                <h3>Ajouter un nouvel agent immobilier</h3>
                                <div class="form-group">
                                    <label>Nom:</label>
                                    <input type="text" name="nom" required>
                                </div>
                                <div class="form-group">
                                    <label>Prénom:</label>
                                    <input type="text" name="prenom" required>
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Téléphone:</label>
                                    <input type="text" name="telephone" required>
                                </div>
                                <div class="form-group">
                                    <label>Spécialité:</label>
                                    <select name="specialite" required>
                                        <option value="Immobilier résidentiel">Immobilier résidentiel</option>
                                        <option value="Immobilier commercial">Immobilier commercial</option>
                                        <option value="Terrain">Terrain</option>
                                        <option value="Appartement à louer">Appartement à louer</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>CV:</label>
                                    <input type="file" name="cv" accept=".pdf,.doc,.docx">
                                </div>
                                <div class="form-group">
                                    <label>Photo:</label>
                                    <input type="file" name="photo" accept="image/*">
                                </div>
                                <button type="submit" name="add_agent">Ajouter l'Agent</button>
                            </form>
                        </div>
                        <div class="search-section">
                            <div class="form-container">
                                <form method="post" action="">
                                    <h3>Rechercher des agents</h3>
                                    <div class="form-group">
                                        <label>Nom:</label>
                                        <input type="text" name="agent_search_nom">
                                    </div>
                                    <div class="form-group">
                                        <label>Prénom:</label>
                                        <input type="text" name="agent_search_prenom">
                                    </div>
                                    <div class="form-group">
                                        <label>Spécialité:</label>
                                        <select name="agent_search_specialite">
                                            <option value="">--Sélectionner--</option>
                                            <option value="Immobilier résidentiel">Immobilier résidentiel</option>
                                            <option value="Immobilier commercial">Immobilier commercial</option>
                                            <option value="Terrain">Terrain</option>
                                            <option value="Appartement à louer">Appartement à louer</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="search_agent">Rechercher</button>
                                </form>
                            </div>
                            <?php if (isset($result_search_agent) && $result_search_agent->num_rows > 0): ?>
                            <div class="table-result">
                                <h3></h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Email</th>
                                            <th>Spécialité</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($agent = $result_search_agent->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="<?php echo $agent['photo']; ?>" alt="Photo"></td>
                                            <td><?php echo $agent['nom']; ?></td>
                                            <td><?php echo $agent['prénom']; ?></td>
                                            <td><?php echo $agent['email']; ?></td>
                                            <td><?php echo $agent['specialité']; ?></td>
                                            <td>
                                                <form method="post" action="">
                                                    <input type="hidden" name="agent_id"
                                                        value="<?php echo $agent['id']; ?>">
                                                    <button type="submit" name="delete_agent">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
            </div>
            <?php else: ?>
            <p>Aucune information de compte trouvée.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="FOOTER">
        <div class="contact-info">
            <p><strong>Contactez-nous :</strong></p>
            <p>Email : contact@omnesimmobilier.com</p>
            <p>Téléphone : +33 1 23 45 67 89</p>
            <p>Adresse : 10 Boulevard des Batignolles, 75017 Paris, France</p>
        </div>
        <div class="google-map">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1311.835293265241!2d2.3251254817565425!3d48.88355598171996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e4cefd8d45f%3A0x84039578b0269828!2s10%20Bd%20des%20Batignolles%2C%2075017%20Paris!5e0!3m2!1sfr!2sfr!4v1716802597343!5m2!1sfr!2sfr"
                width="600" height="450" style="border: 0" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
    <div class="Fenêtre" id="Co">
        <span class="Close" onclick="My_function4()">&times;</span>
        <form action="Connexion.php" method="POST" style="border: 2px solid grey">
            <div class="Fenêtre_contenu">
                <div class="Contenue_1">
                    <ul>
                        <li>
                            <h1>Sign UP</h1>
                        </li>
                        <li>
                            <p>Merci de remplir le formulaire</p>
                        </li>
                    </ul>
                </div>
                <div class="Barage"></div>
                <div class="Contenue_2">
                    <label for="email">Courriel</label>
                    <input type="email" placeholder="Enter Email" name="email" required />
                </div>
                <div class="Contenue_2">
                    <label for="mot_de_passe">Mot de Passe</label>
                    <input type="password" placeholder="Enter Mot de Passe" name="mot_de_passe" required />
                </div>
                <div class="Contenue_2 admin_code" style="display: none">
                    <label for="admin_code">Code Administrateur</label>
                    <input type="text" placeholder="Enter Admin Code" name="admin_code" />
                </div>
                <div class="Contenue_5">
                    <label for="checkbox">Remember me</label>
                    <input type="checkbox" name="checkbox" />
                </div>
                <br />
                <div class="Fin_du_forum">
                    <div class="Espace">
                        <button type="button" class="Cancel" onclick="My_function4()">
                            Cancel
                        </button>
                        <button type="submit" class="Sign_Up">Se connecter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="Fenêtre" id="SignUp">
        <span class="Close" onclick="My_function2()">&times;</span>
        <form action="login.php" method="POST" style="border: 2px solide grey">
            <div class="Fenêtre_contenu">
                <div class="Contenue_1">
                    <ul>
                        <li>
                            <h1>Sign UP</h1>
                        </li>
                        <li>
                            <p>Merci de remplir le formulaire</p>
                        </li>
                    </ul>
                </div>
                <div class="Barage"></div>
                <div class="Contenue_2">
                    <label for="type">Type de compte:</label>
                    <select name="type" id="type" required onchange="showFields()">
                        <option value="">--Select--</option>
                        <option value="Client">Client</option>
                        <option value="Agent Immobilier">Agent Immobilier</option>
                        <option value="Administrateur">Administrateur</option>
                    </select>
                </div>
                <div class="Contenue_2">
                    <label for="nom">Nom</label>
                    <input type="text" placeholder="Enter Nom" name="nom" required />
                </div>
                <div class="Contenue_2">
                    <label for="prenom">Prénom</label>
                    <input type="text" placeholder="Enter Prénom" name="prenom" required />
                </div>
                <div class="Contenue_2">
                    <label for="email">Courriel</label>
                    <input type="email" placeholder="Enter Email" name="email" required />
                </div>
                <div class="Contenue_2">
                    <label for="adresse1">Adresse Ligne 1</label>
                    <input type="text" placeholder="Enter Adresse Ligne 1" name="adresse1" required />
                </div>
                <div class="Contenue_2">
                    <label for="adresse2">Adresse Ligne 2</label>
                    <input type="text" placeholder="Enter Adresse Ligne 2" name="adresse2" />
                </div>
                <div class="Contenue_2">
                    <label for="ville">Ville</label>
                    <input type="text" placeholder="Enter Ville" name="ville" required />
                </div>
                <div class="Contenue_2">
                    <label for="code_postal">Code Postal</label>
                    <input type="text" placeholder="Enter Code Postal" name="code_postal" required />
                </div>
                <div class="Contenue_2">
                    <label for="pays">Pays</label>
                    <input type="text" placeholder="Enter Pays" name="pays" required />
                </div>
                <div class="Contenue_2">
                    <label for="telephone">Numéro de téléphone</label>
                    <input type="text" placeholder="Enter Numéro de téléphone" name="telephone" required />
                </div>
                <div class="Contenue_2">
                    <label for="mot_de_passe">Mot de Passe</label>
                    <input type="password" placeholder="Enter Mot de Passe" name="mot_de_passe" required />
                </div>
                <div class="Contenue_2">
                    <label for="confirm_mot_de_passe">Confirmer Mot de Passe</label>
                    <input type="password" placeholder="Confirmer Mot de Passe" name="confirm_mot_de_passe" required />
                </div>
                <div class="Contenue_2 admin_code" style="display: none">
                    <label for="admin_code">Code Administrateur</label>
                    <input type="text" placeholder="Enter Admin Code" name="admin_code" />
                </div>
                <div class="Contenue_5">
                    <label for="checkbox">Remember me</label>
                    <input type="checkbox" name="checkbox" />
                </div>
                <div class="Contenue_5">
                    <p>
                        By creating an account you agree to our
                        <a href="#" style="color: dodgerblue">Terms & Privacy</a>.
                    </p>
                </div>
                <br />
                <div class="Fin_du_forum">
                    <div class="Espace">
                        <button type="button" class="Cancel" onclick="My_function2()">
                            Cancel
                        </button>
                        <button type="submit" class="Sign_Up">SignUp</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Accueil.js"></script>
    <script src="Creecompte.js"></script>
    <script src="Connexion.js"></script>
    <script>
    function toggleDetails(id) {
        var element = document.getElementById(id);
        if (element.style.display === "none" || element.style.display === "") {
            element.style.display = "block";
        } else {
            element.style.display = "none";
        }
    }

    function viewCV(cvUrl) {
        var popup = window.open(cvUrl, 'CV', 'width=800,height=600,scrollbars=yes,resizable=yes');
        popup.focus();
    }
    </script>
    <script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('photo-preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function previewVideo(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('video-preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    </script>
    <script>
    $(document).ready(function() {
        $('#fetch-property-price').click(function() {
            var propertyId = $('#property_id').val();
            $.ajax({
                url: 'fetch_property_price.php',
                type: 'post',
                data: {
                    property_id: propertyId
                },
                success: function(response) {
                    var originalPrice = parseFloat(response);
                    var finalPrice = originalPrice;

                    // Code de réduction par alerte
                    var discountCode = prompt(
                        "Si vous avez un code de réduction, entrez-le maintenant :");
                    if (discountCode === "omnes") {
                        finalPrice *= 0.9; // Appliquer une remise de 10%
                        alert("Une remise de 10 % a été appliquée !");
                    }

                    // Code Jours Spéciaux
                    var specialDayCode = $('#special_day_code').val();
                    if (specialDayCode === "MAMAN") {
                        finalPrice -= 20; // Réduction de 20 euros
                    }

                    // Code Fidélité
                    var fidelityCode = $('#fidelity_code').val();
                    if (fidelityCode === "ABONNEMENT") {
                        finalPrice *= 0.8; // Réduction de 20%
                    }

                    $('#rental_fee').val(finalPrice.toFixed(
                        2)); // Mettre à jour le champ avec le prix final
                },
                error: function() {
                    alert('Erreur lors de la récupération du prix de la propriété.');
                }
            });
        });

        $('#card_id').change(function() {
            var selectedCard = $(this).find('option:selected');
            var cardSummary = `
            Type de Carte: ${selectedCard.data('type')}<br>
            Nom sur la Carte: ${selectedCard.data('name')}<br>
            Numéro de Carte: ${selectedCard.data('number')}<br>
            Date d'Expiration: ${selectedCard.data('expiration')}<br>
            Adresse de Facturation: ${selectedCard.data('address')}
        `;
            $('#card-summary').html(cardSummary);
        });
    });
    </script>


</body>

</html>