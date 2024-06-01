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
    die("Échec de la connexion : " . $conn->connect_error);
}

// Supposons que l'ID utilisateur est stocké dans la session après la connexion
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

// Fonction pour obtenir le nom du jour à partir d'une date
function getDayName($date) {
    $days = [
        'Sunday' => 'Dimanche',
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi'
    ];
    $dayName = date('l', strtotime($date));
    return $days[$dayName];
}

// Annulation ou complétion de rendez-vous si demandé
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['rendez_vous_id'])) {
        $rendez_vous_id = $_POST['rendez_vous_id'];
        $sql_select = "SELECT * FROM rendez_vous WHERE id = ?";
        $stmt = $conn->prepare($sql_select);
        $stmt->bind_param("i", $rendez_vous_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rendez_vous = $result->fetch_assoc();
        $stmt->close();

        if ($rendez_vous) {
            // Obtenir le jour à partir de la date
            $jour = getDayName($rendez_vous['date']);
            
            // Convertir le créneau horaire au format approprié pour la colonne `créneau_horaire`
            $heure = explode(":", $rendez_vous['heure']);
            $time_slot_start = $heure[0] . ":00-" . str_pad($heure[0] + 1, 2, "0", STR_PAD_LEFT) . ":00";

            if (isset($_POST['complete'])) {
                // Marquer le rendez-vous comme complété et l'ajouter à l'historique des rendez-vous
                $sql_insert = "INSERT INTO historiques (agent_id, utilisateur_id, propriété_id, action, date, heure, détails, adresse_rendez_vous)
                               VALUES (?, ?, ?, 'Rendez-vous complété', ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_insert);
                $stmt->bind_param("iiissss", $rendez_vous['agent_id'], $rendez_vous['client_id'], $rendez_vous['propriété_id'], $rendez_vous['date'], $rendez_vous['heure'], $rendez_vous['commentaires'], $rendez_vous['adresse']);
                $stmt->execute();
                $stmt->close();

                // Supprimer de la table des rendez-vous
                $sql_delete = "DELETE FROM rendez_vous WHERE id = ?";
                $stmt = $conn->prepare($sql_delete);
                $stmt->bind_param("i", $rendez_vous_id);
                $stmt->execute();
                $stmt->close();
            } else if (isset($_POST['cancel'])) {
                // Suppression du rendez-vous
                $sql_delete = "DELETE FROM rendez_vous WHERE id = ?";
                $stmt = $conn->prepare($sql_delete);
                $stmt->bind_param("i", $rendez_vous_id);
                $stmt->execute();
                $stmt->close();
            }

            // Remettre la disponibilité de l'agent à 1
            $sql_update = "UPDATE disponibilités_agents SET disponible = 1 WHERE agent_id = ? AND jour = ? AND créneau_horaire = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("iss", $rendez_vous['agent_id'], $jour, $time_slot_start);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Récupération des rendez-vous, historique des rendez-vous et disponibilités des agents
$rendez_vous = [];
$historique_rendez_vous = [];
$disponibilites_agents = [];
if ($user) {
    if ($user['type_utilisateur'] == 'Admin') {
        $sql = "SELECT rv.id, rv.date, rv.heure, rv.adresse, rv.commentaires, 
                       cl.nom AS client_nom, cl.prénom AS client_prenom,
                       ag.nom AS agent_nom, ag.prénom AS agent_prenom, cl.email, cl.adresse1, cl.ville, cl.numéro_téléphone
                FROM rendez_vous rv
                JOIN utilisateurs cl ON rv.client_id = cl.id
                JOIN agents a ON rv.agent_id = a.id
                JOIN utilisateurs ag ON a.utilisateur_id = ag.id";
        $result = $conn->query($sql);

        $historique_sql = "SELECT h.date, h.heure, h.détails AS commentaires, 
                            ag.nom AS agent_nom, ag.prénom AS agent_prenom
                        FROM historiques h
                        JOIN agents a ON h.agent_id = a.id
                        JOIN utilisateurs ag ON a.utilisateur_id = ag.id";
        $historique_result = $conn->query($historique_sql);

    } elseif ($user['type_utilisateur'] == 'Agent') {
        $sql = "SELECT rv.id, rv.date, rv.heure, rv.adresse, rv.commentaires, 
                       cl.nom AS client_nom, cl.prénom AS client_prenom,
                       ag.nom AS agent_nom, ag.prénom AS agent_prenom, cl.email, cl.adresse1, cl.ville, cl.numéro_téléphone
                FROM rendez_vous rv
                JOIN utilisateurs cl ON rv.client_id = cl.id
                JOIN agents a ON rv.agent_id = a.id
                JOIN utilisateurs ag ON a.utilisateur_id = ag.id
                WHERE a.utilisateur_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $historique_sql = "SELECT h.date, h.heure, h.détails AS commentaires, 
                            ag.nom AS agent_nom, ag.prénom AS agent_prenom
                        FROM historiques h
                        JOIN agents a ON h.agent_id = a.id
                        JOIN utilisateurs ag ON a.utilisateur_id = ag.id
                        WHERE a.utilisateur_id = ?";
        $historique_stmt = $conn->prepare($historique_sql);
        $historique_stmt->bind_param("i", $user_id);
        $historique_stmt->execute();
        $historique_result = $historique_stmt->get_result();

        $disponibilites_sql = "SELECT * FROM disponibilités_agents WHERE agent_id = (SELECT id FROM agents WHERE utilisateur_id = ?)";
        $disponibilites_stmt = $conn->prepare($disponibilites_sql);
        $disponibilites_stmt->bind_param("i", $user_id);
        $disponibilites_stmt->execute();
        $disponibilites_result = $disponibilites_stmt->get_result();
    } elseif ($user['type_utilisateur'] == 'Client') {
        $sql = "SELECT rv.id, rv.date, rv.heure, rv.adresse, rv.commentaires, 
                       ag.nom AS agent_nom, ag.prénom AS agent_prenom
                FROM rendez_vous rv
                JOIN agents a ON rv.agent_id = a.id
                JOIN utilisateurs ag ON a.utilisateur_id = ag.id
                WHERE rv.client_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $historique_sql = "SELECT h.date, h.heure, h.détails AS commentaires, 
                            ag.nom AS agent_nom, ag.prénom AS agent_prenom
                        FROM historiques h
                        JOIN agents a ON h.agent_id = a.id
                        JOIN utilisateurs ag ON a.utilisateur_id = ag.id
                        WHERE h.utilisateur_id = ?";
        $historique_stmt = $conn->prepare($historique_sql);
        $historique_stmt->bind_param("i", $user_id);
        $historique_stmt->execute();
        $historique_result = $historique_stmt->get_result();
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rendez_vous[] = $row;
        }
    }

    if ($historique_result && $historique_result->num_rows > 0) {
        while ($row = $historique_result->fetch_assoc()) {
            $historique_rendez_vous[] = $row;
        }
    }

    if (isset($disponibilites_result) && $disponibilites_result->num_rows > 0) {
        while ($row = $disponibilites_result->fetch_assoc()) {
            $disponibilites_agents[] = $row;
        }
    }
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
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        color: #333;
    }

    .SECTION {
        width: 90%;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-top: 20px;
        font-size: 10px;
    }

    .mot_bienvenue h2 {
        margin: 0;
        font-size: 24px;
        color: #444;
    }

    .rendez,
    .historique {
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        table-layout: fixed;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
        overflow-wrap: break-word;
    }

    th {
        background-color: #f4f4f4;
        font-weight: bold;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    button {
        font-size: 10px;
        background-color: #f44336;
        color: white;
        border: none;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 10px;
        margin-top: 4px;
        cursor: pointer;
        border-radius: 4px;
    }

    button:hover {
        background-color: #d32f2f;
    }

    .disponible {
        background-color: blue;
        color: white;
    }

    .indisponible {
        background-color: red;
        color: white;
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
            <div class="mot_bienvenue">
                <h2>Bienvenue:
                    <span><?php echo htmlspecialchars($user['nom'] . ' ' . htmlspecialchars($user['prénom'])); ?></span>
                </h2>
            </div>
            <div class="rendez">
                <?php if ($user['type_utilisateur'] == 'Client'): ?>
                <h3>Vos Rendez-vous</h3>
                <?php if (count($rendez_vous) > 0): ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Adresse</th>
                        <th>Commentaires</th>
                        <th>Nom de l'agent</th>
                        <th>Prénom de l'agent</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($rendez_vous as $rv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rv['date']); ?></td>
                        <td><?php echo htmlspecialchars($rv['heure']); ?></td>
                        <td><?php echo htmlspecialchars($rv['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($rv['commentaires']); ?></td>
                        <td><?php echo htmlspecialchars($rv['agent_nom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['agent_prenom']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="rendez_vous_id"
                                    value="<?php echo htmlspecialchars($rv['id']); ?>">
                                <button type="submit" name="cancel">Annuler</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p>Aucun rendez-vous trouvé.</p>
                <?php endif; ?>

                <h3>Historique de vos Rendez-vous</h3>
                <?php if (count($historique_rendez_vous) > 0): ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Commentaires</th>
                        <th>Nom de l'agent</th>
                        <th>Prénom de l'agent</th>
                    </tr>
                    <?php foreach ($historique_rendez_vous as $hr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hr['date']); ?></td>
                        <td><?php echo htmlspecialchars($hr['heure']); ?></td>
                        <td><?php echo htmlspecialchars($hr['commentaires']); ?></td>
                        <td><?php echo htmlspecialchars($hr['agent_nom']); ?></td>
                        <td><?php echo htmlspecialchars($hr['agent_prenom']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p>Aucun historique de rendez-vous trouvé.</p>
                <?php endif; ?>

                <?php elseif ($user['type_utilisateur'] == 'Agent'): ?>
                <h3>Rendez-vous</h3>
                <?php if (count($rendez_vous) > 0): ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Adresse</th>
                        <th>Commentaires</th>
                        <th>Nom de l'agent</th>
                        <th>Prénom de l'agent</th>
                        <th>Nom du client</th>
                        <th>Prénom du client</th>
                        <th>Email</th>
                        <th>Numéro de téléphone</th>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($rendez_vous as $rv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rv['date']); ?></td>
                        <td><?php echo htmlspecialchars($rv['heure']); ?></td>
                        <td><?php echo htmlspecialchars($rv['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($rv['commentaires']); ?></td>
                        <td><?php echo htmlspecialchars($rv['agent_nom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['agent_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['client_nom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['client_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['email']); ?></td>
                        <td><?php echo htmlspecialchars($rv['numéro_téléphone']); ?></td>
                        <td><?php echo htmlspecialchars($rv['adresse1']); ?></td>
                        <td><?php echo htmlspecialchars($rv['ville']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="rendez_vous_id"
                                    value="<?php echo htmlspecialchars($rv['id']); ?>">
                                <button type="submit" name="complete">Compléter</button>
                                <button type="submit" name="cancel">Annuler</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p>Aucun rendez-vous trouvé.</p>
                <?php endif; ?>

                <h3>Historique des Rendez-vous</h3>
                <?php if (count($historique_rendez_vous) > 0): ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Commentaires</th>
                        <th>Nom de l'agent</th>
                        <th>Prénom de l'agent</th>
                    </tr>
                    <?php foreach ($historique_rendez_vous as $hr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hr['date']); ?></td>
                        <td><?php echo htmlspecialchars($hr['heure']); ?></td>
                        <td><?php echo htmlspecialchars($hr['commentaires']); ?></td>
                        <td><?php echo htmlspecialchars($hr['agent_nom']); ?></td>
                        <td><?php echo htmlspecialchars($hr['agent_prenom']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p>Aucun historique de rendez-vous trouvé.</p>
                <?php endif; ?>

                <h3>Emploi du temps</h3>
                <table>
                    <tr>
                        <th>Lundi</th>
                        <th>Mardi</th>
                        <th>Mercredi</th>
                        <th>Jeudi</th>
                        <th>Vendredi</th>
                    </tr>
                    <?php 
                    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
                    for ($i = 9; $i <= 18; $i++): 
                        echo '<tr>';
                        foreach ($jours as $jour): 
                            $heure = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00-" . str_pad($i + 1, 2, "0", STR_PAD_LEFT) . ":00";
                            $dispo = false;
                            foreach ($disponibilites_agents as $disponibilite) {
                                if ($disponibilite['jour'] == $jour && $disponibilite['créneau_horaire'] == $heure) {
                                    $dispo = $disponibilite['disponible'];
                                    break;
                                }
                            }
                            $class = $dispo ? 'disponible' : 'indisponible';
                            echo "<td class='$class'>$heure</td>";
                        endforeach; 
                        echo '</tr>';
                    endfor; 
                    ?>
                </table>

                <?php elseif ($user['type_utilisateur'] == 'Admin'): ?>
                <h3>Tous les Rendez-vous</h3>
                <?php if (count($rendez_vous) > 0): ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Adresse</th>
                        <th>Commentaires</th>
                        <th>Nom de l'agent</th>
                        <th>Prénom de l'agent</th>
                        <th>Nom du client</th>
                        <th>Prénom du client</th>
                        <th>Email</th>
                        <th>Numéro de téléphone</th>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($rendez_vous as $rv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rv['date']); ?></td>
                        <td><?php echo htmlspecialchars($rv['heure']); ?></td>
                        <td><?php echo htmlspecialchars($rv['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($rv['commentaires']); ?></td>
                        <td><?php echo htmlspecialchars($rv['agent_nom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['agent_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['client_nom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['client_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($rv['email']); ?></td>
                        <td><?php echo htmlspecialchars($rv['numéro_téléphone']); ?></td>
                        <td><?php echo htmlspecialchars($rv['adresse1']); ?></td>
                        <td><?php echo htmlspecialchars($rv['ville']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="rendez_vous_id"
                                    value="<?php echo htmlspecialchars($rv['id']); ?>">
                                <button type="submit" name="complete">Compléter</button>
                                <button type="submit" name="cancel">Annuler</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p>Aucun rendez-vous trouvé.</p>
                <?php endif; ?>

                <h3>Historique de tous les Rendez-vous</h3>
                <?php if (count($historique_rendez_vous) > 0): ?>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Commentaires</th>
                        <th>Nom de l'agent</th>
                        <th>Prénom de l'agent</th>
                    </tr>
                    <?php foreach ($historique_rendez_vous as $hr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hr['date']); ?></td>
                        <td><?php echo htmlspecialchars($hr['heure']); ?></td>
                        <td><?php echo htmlspecialchars($hr['commentaires']); ?></td>
                        <td><?php echo htmlspecialchars($hr['agent_nom']); ?></td>
                        <td><?php echo htmlspecialchars($hr['agent_prenom']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p>Aucun historique de rendez-vous trouvé.</p>
                <?php endif; ?>
                <?php endif; ?>
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
                        <input type="password" placeholder="Confirmer Mot de Passe" name="confirm_mot_de_passe"
                            required />
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
        <script src="Creecompte.js"></script>
        <script src="Connexion.js"></script>
</body>

</html>
