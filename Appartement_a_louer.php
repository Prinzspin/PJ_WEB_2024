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

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$user = null;
if ($user_id) {
    // Récupération des informations de l'utilisateur
    $sql = "SELECT * FROM utilisateurs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
// Récupération des données des appartements à louer avec les informations de l'agent assigné
$sql = "SELECT p.*, a.nom AS agent_nom, a.prénom AS agent_prénom, a.email AS agent_email, a.numéro_téléphone AS agent_numéro_téléphone, a.cv AS agent_cv, a.photo AS agent_photo, a.specialité
        FROM propriétés p 
        LEFT JOIN agents a ON p.agent_id = a.id 
        WHERE p.type = 'Appartement'";
$result = $conn->query($sql);

$appartements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Récupérer les disponibilités de l'agent
        $agent_id = $row['agent_id'];
        $sql_disponibilites = "SELECT * FROM disponibilités_agents WHERE agent_id = $agent_id AND disponible = 1";
        $result_disponibilites = $conn->query($sql_disponibilites);
        
        $disponibilites = [];
        if ($result_disponibilites->num_rows > 0) {
            while ($disponibilite = $result_disponibilites->fetch_assoc()) {
                $disponibilites[] = $disponibilite;
            }
        }
        $row['disponibilités'] = $disponibilites;
        
        // Ajoutons chaque appartement au tableau $appartements
        $appartements[] = $row;
    }
} else {
    echo "0 résultats";
}

$conn->close();
?>





<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tout Parcourir</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Appartement_a_lour.js"></script>
    <link rel="stylesheet" type="text/css" href="Accueil.css" />
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <link rel="stylesheet" type="text/css" href="Formulaire.css" />
    <link rel="stylesheet" type="text/css" href="RDV.css" />
    <link rel="stylesheet" type="text/css" href="contact.css" />

</head>

<body>
    <div class="WRAPPER">
        <div class="header">
            <h1>Omnes Immobilier <img src="LOGO.png" alt="Omnes Logo" /></h1>
        </div>
        <div class="NAVIGATION">
            <ul>
                <li><a href="#">Accueil</a></li>
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
                            <a href="#">Appartement à louer</a>
                        </li>
                    </ul>
                </li>
                <li><a href="#">Recherche</a></li>
                <li><a href="Rendez_vous.php">Rendez-vous</a></li>
                <li>
                    <a href="#">Votre Compte</a>
                    <ul class="submenu">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="Mon_compte.php">Mon Compte</a></li>
                        <li><a href="logout.php">Se déconnecter</a></li>
                        <?php else: ?>
                        <li>
                            <br />
                            <button class="login-button" onclick="My_function3()">
                                Se connecter
                            </button>
                        </li>
                        <li>
                            <button class="login-button" onclick="My_function()">
                                Créer un compte
                            </button>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="SECTION">
            <h1>Appartements à Louer</h1>
            <div class="appartements-container">
                <?php foreach ($appartements as $appartement): ?>
                <div class="appartement">
                    <div class="carousel">
                        <?php 
                $photos = explode(' ', $appartement['photos']);
                if (!empty($photos[0])) {
                    echo '<img src="' . htmlspecialchars($photos[0]) . '" class="initial-photo" alt="Photo de l\'appartement">';
                }
                ?>
                    </div>
                    <div class="summary">
                        <h2><?php echo htmlspecialchars($appartement['adresse']); ?></h2>
                        <p class="price"><?php echo htmlspecialchars($appartement['prix']); ?> €/mois</p>
                        <button class="voir-plus"
                            data-adresse="<?php echo htmlspecialchars($appartement['adresse']); ?>"
                            data-client-id="<?php echo $user['id']; ?>"
                            data-commentaires="<?php echo htmlspecialchars($appartement['infosup'] ?? ''); ?>">Voir
                            plus</button>
                    </div>
                    <div class="details" style="display:none;">
                        <p><?php echo htmlspecialchars($appartement['ville']) . " (" . htmlspecialchars($appartement['code_postal']) . ")"; ?>
                        </p>
                        <p><?php echo htmlspecialchars($appartement['description']); ?></p>
                        <p class="price">N°identification : &nbsp<?php echo htmlspecialchars($appartement['id']); ?></p>
                        <p>Superficie: <?php echo htmlspecialchars($appartement['superficie']); ?> m²</p>
                        <p><?php echo htmlspecialchars($appartement['Nombre_de_pièce']); ?> pièces,
                            <?php echo htmlspecialchars($appartement['nombre_de_chambres']); ?> chambres,
                            <?php echo htmlspecialchars($appartement['nombre_de_salles_de_bain']); ?> salle de bain</p>
                        <p>Balcon: <?php echo $appartement['balcon'] ? 'Oui' : 'Non'; ?></p>
                        <p>Parking: <?php echo $appartement['parking'] ? 'Oui' : 'Non'; ?></p>
                        <div class="carousel">
                            <div class="carousel-inner">
                                <?php
                        foreach ($photos as $photo) {
                            if (!empty($photo)) {
                                echo '<div class="carousel-item"><img src="' . htmlspecialchars($photo) . '" alt="Photo de l\'appartement"></div>';
                            }
                        }
                        ?>
                            </div>
                            <button class="prev">&lt;</button>
                            <button class="next">&gt;</button>
                        </div>
                        <button class="voir-agent" data-agent-id="<?php echo $appartement['agent_id']; ?>">Voir l'agent
                            assigné</button>
                        <div class="agent-card agent-details" style="display:none;">
                            <div class="agent-photo">
                                <img src="<?php echo htmlspecialchars($appartement['agent_photo']); ?>"
                                    alt="Photo de l'agent">
                            </div>
                            <div class="agent-info">
                                <h3>Agent assigné :
                                    <?php echo htmlspecialchars($appartement['agent_prénom']) . " " . htmlspecialchars($appartement['agent_nom']); ?>
                                </h3>
                                <p>Spécialité : <?php echo htmlspecialchars($appartement['specialité']); ?></p>
                                <p><?php echo htmlspecialchars($appartement['agent_email']); ?></p>
                                <p><?php echo htmlspecialchars($appartement['agent_numéro_téléphone']); ?></p>
                                <div class="agent-buttons">
                                    <button onclick="showContactDetails(this)">Contactez</button>
                                    <button
                                        onclick="scheduleAppointment(<?php echo htmlspecialchars(json_encode($appartement['disponibilités'])); ?>, <?php echo $appartement['agent_id']; ?>, <?php echo $user['id']; ?>, <?php echo $appartement['id']; ?>, '<?php echo addslashes($appartement['adresse']); ?>', '<?php echo addslashes($appartement['infosup'] ?? ''); ?>')">Prendre
                                        Rendez-vous</button>
                                    <button
                                        onclick="viewCV('<?php echo htmlspecialchars($appartement['agent_cv']); ?>')">Consulter
                                        le CV</button>
                                </div>
                                <div class="contact-buttons">
                                    <button
                                        onclick="showChatRoom(<?php echo $appartement['agent_id']; ?>, <?php echo $user['id']; ?>)">Chatroom</button>
                                    <button
                                        onclick="showEmailForm('<?php echo htmlspecialchars($appartement['agent_email']); ?>', '<?php echo htmlspecialchars($user['email']); ?>')">Envoyer
                                        un Email</button>
                                    <button
                                        onclick="showChatRoomHistory(<?php echo $appartement['agent_id']; ?>, <?php echo $user['id']; ?>)">Chatroom
                                        historique</button>
                                </div>
                                <div id="chatRoom" style="display:none;">
                                    <div id="chatMessages"></div>
                                    <form id="chatForm" onsubmit="sendMessage(event)">
                                        <input type="hidden" id="agent_id_chat" name="agent_id">
                                        <input type="hidden" id="client_id_chat" name="client_id">
                                        <textarea id="chatMessage" name="message" rows="4" required></textarea>
                                        <button type="submit">Envoyer</button>
                                    </form>
                                </div>
                                <div id="historique" style="display:none;">
                                    <h4>Historique des Conversations</h4>
                                    <div id="historiqueMessages"></div>
                                </div>
                                <div class="schedule" style="display:none;">
                                    <h4>Horaires disponibles</h4>
                                    <ul class="schedule-list">
                                        <?php foreach ($appartement['disponibilités'] as $disponibilite): ?>
                                        <li data-dispo-id="<?php echo $disponibilite['id']; ?>">
                                            <?php echo htmlspecialchars($disponibilite['jour']) . " " . htmlspecialchars($disponibilite['créneau_horaire']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <form id="appointmentForm" action="process_appointment.php" method="post" style="display:none;">
                <input type="hidden" id="form_agent_id" name="agent_id">
                <input type="hidden" id="form_client_id" name="client_id">
                <input type="hidden" id="form_propriété_id" name="propriété_id">
                <input type="hidden" id="form_adresse" name="adresse">
                <input type="hidden" id="form_date" name="date">
                <input type="hidden" id="form_heure" name="heure">
                <input type="hidden" id="form_dispo_id" name="dispo_id">
                <input type="hidden" id="form_commentaires" name="commentaires">
            </form>
            <div id="emailFormModal" style="display:none;">
                <form id="sendEmailForm" action="email_agent.php" method="post">
                    <input type="hidden" id="agent_email" name="agent_email">
                    <input type="hidden" id="client_email" name="client_email"
                        value="<?php echo htmlspecialchars($user['email']); ?>">
                    <label for="email_subject">Objet:</label>
                    <input type="text" id="email_subject" name="email_subject" required>
                    <label for="email_body">Message:</label>
                    <textarea id="email_body" name="email_body" rows="4" required></textarea>
                    <button type="submit">Envoyer</button>
                </form>
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

    <script>
    function toggleDetails(button) {
        var details = button.parentElement.nextElementSibling;
        var initialPhoto = button.parentElement.previousElementSibling.querySelector('.initial-photo');
        if (details.style.display === "none" || details.style.display === "") {
            details.style.display = "block";
            if (initialPhoto) {
                initialPhoto.style.display = "none";
            }
            button.innerText = "Voir moins";
        } else {
            details.style.display = "none";
            if (initialPhoto) {
                initialPhoto.style.display = "block";
            }
            button.innerText = "Voir plus";
        }
    }

    function toggleAgentDetails(button) {
        var agentDetails = button.nextElementSibling;
        if (agentDetails.style.display === "none" || agentDetails.style.display === "") {
            agentDetails.style.display = "block";
            button.innerText = "Voir moins d'agent";
        } else {
            agentDetails.style.display = "none";
            button.innerText = "Voir l'agent assigné";
        }
    }

    function showContactDetails(button) {
        var contactDetails = button.closest('.agent-card').querySelector('.contact-details');
        if (contactDetails.style.display === "none" || contactDetails.style.display === "") {
            contactDetails.style.display = "block";
        } else {
            contactDetails.style.display = "none";
        }
    }



    function viewCV(cvUrl) {
        var popup = window.open(cvUrl, 'CV', 'width=800,height=600,scrollbars=yes,resizable=yes');
        popup.focus();
    }
    </script>
    <script>
    function showChatRoom(agentId, clientId) {
        document.querySelector('#chatRoom').style.display = 'block';
        document.getElementById('agent_id_chat').value = agentId;
        document.getElementById('client_id_chat').value = clientId;
        loadMessages(agentId, clientId);
    }

    function sendMessage() {
        const agentId = document.getElementById('agent_id_chat').value;
        const clientId = document.getElementById('client_id_chat').value;
        const message = document.getElementById('chatMessage').value;

        fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    expéditeur_id: clientId,
                    destinataire_id: agentId,
                    message: message
                })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadMessages(agentId, clientId);
                }
            });
    }

    function loadMessages(agentId, clientId) {
        fetch(`load_messages.php?expéditeur_id=${clientId}&destinataire_id=${agentId}`)
            .then(response => response.json())
            .then(data => {
                const chatroom = document.getElementById('chatMessages');
                chatroom.innerHTML = '';
                data.messages.forEach(msg => {
                    const messageElement = document.createElement('div');
                    messageElement.textContent = msg.message;
                    chatroom.appendChild(messageElement);
                });
            });
    }

    function showEmailForm(agentEmail, clientEmail) {
        document.getElementById('emailForm').style.display = 'block';
        document.getElementById('agent_email').value = agentEmail;
        document.getElementById('client_email').value = clientEmail;
    }

    function showChatRoomHistory(agentId, clientId) {
        document.getElementById('historique').style.display = 'block';
        fetch(`log_communication.php?agent_id=${agentId}&client_id=${clientId}`)
            .then(response => response.json())
            .then(data => {
                const logContainer = document.getElementById('historiqueMessages');
                logContainer.innerHTML = '';
                data.communications.forEach(comm => {
                    const logElement = document.createElement('div');
                    logElement.textContent = `[${comm.timestamp}] ${comm.message}`;
                    logContainer.appendChild(logElement);
                });
            });
    }
    </script>


    <script src="RDV.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.21.1/dist/date-fns.min.js"></script>
    <script src="Accueil.js"></script>
    <script src="Creecompte.js"></script>
    <script src="Connexion.js"></script>



</body>

</html>