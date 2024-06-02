

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Menu Omnes Immobilier</title>
    <link rel="stylesheet" type="text/css" href="Accueil.css" />
    <link rel="stylesheet" type="text/css" href="Formulaire.css" />
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
                            <a href="#">Immobilier commercial</a>
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

        <div class="search-container">
            <h1>Recherche</h1>
            <form action="recherche.php" method="GET">
                <input type="text" name="q" placeholder="Tapez votre recherche...">
                <button type="submit">Rechercher</button>
            </form>

            <?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "omnesimmobilier";

// Créez une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Vérifier si le formulaire de recherche a été soumis et si le champ de recherche n'est pas vide
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = $_GET['q'];

    // Requête pour rechercher des propriétés par adresse, ville ou numéro de propriété
    $property_query = "SELECT * FROM propriétés WHERE adresse LIKE '%$search_query%' OR ville LIKE '%$search_query%' OR id LIKE '%$search_query%'";

    // Requête pour rechercher des agents par nom ou prénom et lier avec les propriétés correspondantes
    $agent_query = "SELECT agents.*, propriétés.* FROM agents 
                    JOIN propriétés ON agents.id = propriétés.agent_id 
                    WHERE agents.nom LIKE '%$search_query%' OR agents.prénom LIKE '%$search_query%'";

    // Exécuter la requête des propriétés
    $property_result = $conn->query($property_query);

    // Exécuter la requête des agents
    $agent_result = $conn->query($agent_query);

    // Vérifier si les requêtes ont échoué
    if (!$property_result || !$agent_result) {
        die("Erreur de requête SQL : " . mysqli_error($conn));
    }

    // Afficher les résultats des propriétés
    if ($property_result->num_rows > 0) {
        echo "<h2>Résultats de la recherche des propriétés :</h2>";
        while ($row = $property_result->fetch_assoc()) {
            echo "<div class='result property'>";
            echo "<h3>" . htmlspecialchars($row["adresse"]) . "</h3>";
            echo "<p>Ville: " . htmlspecialchars($row["ville"]) . " (" . htmlspecialchars($row["code_postal"]) . ")</p>";
            echo "<p>Prix: " . htmlspecialchars($row["prix"]) . " €/mois</p>";
            echo "<p>Description: " . htmlspecialchars($row["description"]) . "</p>";
            echo "<p>Superficie: " . htmlspecialchars($row["superficie"]) . " m²</p>";
            echo "<p>Nombre de pièces: " . htmlspecialchars($row["Nombre_de_pièce"]) . "</p>";
            echo "<p>Nombre de chambres: " . htmlspecialchars($row["nombre_de_chambres"]) . "</p>";
            echo "<p>Nombre de salles de bain: " . htmlspecialchars($row["nombre_de_salles_de_bain"]) . "</p>";
            echo "<p>Balcon: " . ($row["balcon"] ? 'Oui' : 'Non') . "</p>";
            echo "<p>Parking: " . ($row["parking"] ? 'Oui' : 'Non') . "</p>";

            $photos = explode(' ', $row["photos"]);
            if (count($photos) > 0) {
                echo "<div class='carousel'>";
                foreach ($photos as $photo) {
                    if (!empty($photo)) {
                        echo "<div class='carousel-item'><img src='" . htmlspecialchars($photo) . "' alt='Photo de l'appartement'></div>";
                    }
                }
                echo "</div>";
            }

            echo "<button class='view-more'>Voir plus</button>";
            echo "</div>";
        }
    } else {
        $no_property_results = true;
    }

    // Afficher les résultats des agents avec les propriétés correspondantes
    if ($agent_result->num_rows > 0) {
        echo "<h2>Résultats de la recherche des agents :</h2>";
        while ($row = $agent_result->fetch_assoc()) {
            echo "<div class='result agent'>";
            echo "<p>Nom: " . htmlspecialchars($row["nom"]) . " - Prénom: " . htmlspecialchars($row["prénom"]) . "</p>";
            echo "<p>Email: " . htmlspecialchars($row["email"]) . "</p>";
            
            echo "<div class='property'>";
            echo "<h3>Propriété liée: " . htmlspecialchars($row["adresse"]) . "</h3>";
            echo "<p>Ville: " . htmlspecialchars($row["ville"]) . " (" . htmlspecialchars($row["code_postal"]) . ")</p>";
            echo "<p>Prix: " . htmlspecialchars($row["prix"]) . " €/mois</p>";
            echo "<p>Description: " . htmlspecialchars($row["description"]) . "</p>";
            echo "<p>Superficie: " . htmlspecialchars($row["superficie"]) . " m²</p>";
            echo "<p>Nombre de pièces: " . htmlspecialchars($row["Nombre_de_pièce"]) . "</p>";
            echo "<p>Nombre de chambres: " . htmlspecialchars($row["nombre_de_chambres"]) . "</p>";
            echo "<p>Nombre de salles de bain: " . htmlspecialchars($row["nombre_de_salles_de_bain"]) . "</p>";
            echo "<p>Balcon: " . ($row["balcon"] ? 'Oui' : 'Non') . "</p>";
            echo "<p>Parking: " . ($row["parking"] ? 'Oui' : 'Non') . "</p>";

            $photos = explode(' ', $row["photos"]);
            if (count($photos) > 0) {
                echo "<div class='carousel'>";
                foreach ($photos as $photo) {
                    if (!empty($photo)) {
                        echo "<div class='carousel-item'><img src='" . htmlspecialchars($photo) . "' alt='Photo de l'appartement'></div>";
                    }
                }
                echo "</div>";
            }

            echo "</div>";

            echo "<button class='view-more'>Voir plus</button>";
            echo "</div>";
        }
    } else {
        $no_agent_results = true;
    }

    // Afficher un message si aucun résultat n'est trouvé
    if (isset($no_property_results) && isset($no_agent_results)) {
        echo "Aucun résultat trouvé.";
    }
} else {
    // Si le champ de recherche est vide
    echo "Aucune recherche effectuée.";
}

// Fermer la connexion à la base de données
$conn->close();
?>





        </div>



        <style>
        body {
            font-family: Arial, sans-serif;
        }
        .search-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }
        input[type="text"] {
            width: 80%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
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
</body>

</html>