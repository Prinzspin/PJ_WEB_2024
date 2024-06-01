<?php
session_start();


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
    }

    .section {
        margin: 20px 0;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    .section h2 {
        margin-top: 0;
    }

    .button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    .button:hover {
        background-color: #0056b3;
    }

    .code {
        display: none;
        margin-top: 10px;
        padding: 10px;
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .contact-info {
        margin-top: 10px;
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
                <li><a href="#">Accueil</a></li>
                <li>
                    <a href="#">Tout Parcourir</a>
                    <ul class="submenu">
                        <li>
                            <a href="Immobilier_résidentiel.html">Immobilier résidentiel</a>
                        </li>
                        <li>
                            <a href="Immobilier_commercial.html">Immobilier commercial</a>
                        </li>
                        <li><a href="Terrain.html">Terrain</a></li>
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
        <div class="SECTION">
            <div class="mot_bienvenue">
                <h2>Bienvenue sur Omnes Immobilier</h2>
                <p>
                    Omnes Immobilier est un site innovant conçu pour répondre aux besoins immobiliers en ligne du grand
                    public. Notre plateforme permet aux clients de consulter une liste complète de propriétés
                    immobilières en vente, de sélectionner un agent immobilier associé à chaque propriété, et d'accéder
                    à des informations pertinentes telles que son CV, ses coordonnées, son calendrier hebdomadaire ainsi
                    que sa disponibilité pour des rendez-vous de visite. Lorsqu'un rendez-vous est pris, une
                    confirmation est envoyée au client. Omnes Immobilier est ouvert à tous les membres de la communauté
                    Omnes Education. De plus, si un agent immobilier est disponible en ligne, les clients peuvent
                    communiquer avec lui par message, visioconférence ou courriel. Notre site est géré par une équipe
                    d'administrateurs dédiée, et chaque membre de la communauté Omnes Education peut créer un compte
                    client. Les administrateurs sont responsables d'ajouter de nouvelles propriétés, de nouveaux agents
                    immobiliers et de mettre à jour les emplois du temps des agents. Bienvenue chez Omnes Immobilier,
                    votre partenaire de confiance pour toutes vos transactions immobilières.
                </p>
            </div>
            <div class="Evenement_buletin">
                <p>
                    <button id="btn-evenement">Évènement de la semaine</button> /
                    <button id="btn-bulletin">Bulletin de la semaine</button>
                </p>
                <div class="Evenement_de_la_semaine active">
                    <div class="Evenement_contenu">
                        <h5>Journée Salon de l'Immobilier</h5>
                        <img src="ACCEUIL_IMAGE/Salon.jpg" alt="Photo du Salon de l'Immobilier" />
                    </div>
                    <p>
                        Ne manquez pas notre Journée Salon de l'Immobilier cette semaine !
                        Venez rencontrer des experts en immobilier, des conseillers en
                        hypothèques, et découvrir les meilleures offres du marché.
                    </p>
                </div>
                <div class="Bulletin">
                    <div class="Evenement_contenu">
                        <h5>Appartement en visite libre ce week-end</h5>
                        <img src="ACCEUIL_IMAGE/Visiteweek.jpg" alt="Photo de la maison à visiter" />
                    </div>
                    <p>
                        Ne ratez pas l'occasion de découvrir votre futur chez-vous lors de
                        notre journée portes ouvertes ce week-end. Profitez de cette
                        opportunité unique pour visiter un appartement exceptionnellement
                        conçu pour répondre à tous vos besoins en matière d'habitat. Situé
                        dans un quartier prisé, l'appartement offre des finitions de haute
                        qualité, un agencement optimal et une vue imprenable. Des
                        conseillers seront également sur place pour répondre à toutes vos
                        questions concernant les démarches d'achat et les options de
                        financement disponibles. Venez nous rejoindre et faire un pas de
                        plus vers la réalisation de votre rêve immobilier.
                    </p>
                </div>
            </div>
            <div class="carousel-container">
                <p>Nos Biens</p>
                <div class="carousel">
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image1.jpg" alt="Propriété 1" />
                        <div class="description">
                            <h3>PARIS</h3>
                            <h2>APPARTEMENT LUXUEUX À PARIS AVEC VUE TOUR EIFFEL</h2>
                            <div class="details">
                                <span>180 m²</span>
                                <span>3 Chambres</span>
                                <span>4 Pièces</span>
                            </div>
                            <div class="price">1 500 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image2.jpg" alt="Propriété 2" />
                        <div class="description">
                            <h3>LA MORALEJA</h3>
                            <h2>APPARTEMENT LUXUEUX FAMILIALE À PARIS</h2>
                            <div class="details">
                                <span>1800 m²</span>
                                <span>6 Chambres</span>
                                <span>15 Pièces</span>
                            </div>
                            <div class="price">Prix confidentiel</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image3.jpg" alt="Propriété 3" />
                        <div class="description">
                            <h3>PARIS</h3>
                            <h2>APPARTEMENT ETUDIANT À PARIS</h2>
                            <div class="details">
                                <span>35 m²</span>
                                <span>1 Chambres</span>
                                <span>2 Pièces</span>
                            </div>
                            <div class="price">400 000€</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image4.jpg" alt="Propriété 4" />
                        <div class="description">
                            <h3>PARIS</h3>
                            <h2>APPARTEMENT FAMILIALE À PARIS</h2>
                            <div class="details">
                                <span>200 m²</span>
                                <span>4 Chambres</span>
                                <span>8 Pièces</span>
                            </div>
                            <div class="price">2 600 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image5.jpg" alt="Propriété 5" />
                        <div class="description">
                            <h3>PARIS</h3>
                            <h2>APPARTEMENT RUSTIQUE À PARIS</h2>
                            <div class="details">
                                <span>120 m²</span>
                                <span>4 Chambres</span>
                                <span>6 Pièces</span>
                            </div>
                            <div class="price">1 400 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image6.jpg" alt="Propriété 6" />
                        <div class="description">
                            <h3>NEUILLY SUR SEINE</h3>
                            <h2>MAISON MODERNE À NEUILLY SUR SEINE</h2>
                            <div class="details">
                                <span>296 m²</span>
                                <span>5 Chambres</span>
                                <span>9 Pièces</span>
                            </div>
                            <div class="price">5 600 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image7.jpg" alt="Propriété 7" />
                        <div class="description">
                            <h3>LYON</h3>
                            <h2>VILLA LUXUEUSE À LYON AVEC PISCINE</h2>
                            <div class="details">
                                <span>260 m²</span>
                                <span>4 Chambres</span>
                                <span>5 Pièces</span>
                            </div>
                            <div class="price">2 500 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image8.jpg" alt="Propriété 8" />
                        <div class="description">
                            <h3>BORDEAUX</h3>
                            <h2>VILLA À BORDEAUX AVEC PISCINE</h2>
                            <div class="details">
                                <span>350 m²</span>
                                <span>5 Chambres</span>
                                <span>10 Pièces</span>
                            </div>
                            <div class="price">2 400 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image9.jpg" alt="Propriété 9" />
                        <div class="description">
                            <h3>LEVALLOIS - PERRET</h3>
                            <h2>APPARTEMENT CONTEMPORAIN À LEVALLOIS - PERRET AVEC JARDIN</h2>
                            <div class="details">
                                <span>180 m²</span>
                                <span>4 Chambres</span>
                                <span>6 Pièces</span>
                            </div>
                            <div class="price">2 300 000 €</div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="ACCEUIL_IMAGE/image10.jpg" alt="Propriété 10" />
                        <div class="description">
                            <h3>STRASBOURG</h3>
                            <h2>MAISON DE DESIGNERS À STRASBOURG</h2>
                            <div class="details">
                                <span>200 m²</span>
                                <span>5 Chambres</span>
                                <span>7 Pièces</span>
                            </div>
                            <div class="price">1 400 000 €</div>
                        </div>
                    </div>
                    <button class="prev">&#10094;</button>
                    <button class="next">&#10095;</button>
                </div>
            </div>

            <div class="section jour-special">
                <h2>Journée Fête des Mères</h2>
                <p>Profitez d'une belle journée dédiée à toutes les mamans ! Utilisez le code <strong>MAMAN</strong>
                    pour bénéficier d'une remise de 20 euros.</p>


            </div>
            <div class="section jour-special">
                <h2>Bonus Abonnement</h2>
                <p>En vous abonnant profitez d'une remise de -20% sur le premier payment
                </p>
                <div class="button" onclick="toggleCode()">S'abonner</div>
                <div id="code-abonnement" class="code">
                    Code de réduction : ABONNEMENT
                </div>
            </div>

            <div class="section assurance-immobilier">
                <h2>Assurance Immobilier</h2>
                <p>Notre agence d'assurance immobilier propose un taux intéressant pour tous nos clients. Contactez-nous
                    pour plus de détails.</p>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.3550226017423!2d2.3584165683024008!3d48.87050826427441!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e0ebd3ea9d1%3A0x8838d6a5910a70c5!2sMAIF%20Assurances%20Paris%20Magenta!5e0!3m2!1sfr!2sfr!4v1717228126068!5m2!1sfr!2sfr"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                <p>Adresse : 15 Bd de Magenta, 75010 Paris</p>
                <div class="contact-info">
                    <p>Contact: <strong>Jean Dupont</strong></p>
                    <p>Email: <strong>jean.dupont@maif.fr</strong></p>
                    <p>Téléphone: <strong>+33 1 23 45 67 89</strong></p>
                </div>
            </div>

            <div class="section pret-immobilier">
                <h2>Prêt Immobilier</h2>
                <p>Besoin d'un prêt immobilier ? Notre banque offre des taux compétitifs et un service personnalisé pour
                    vous aider à réaliser votre projet.</p>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10497.631837141265!2d2.3367395554199213!3d48.86949900000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e11a66ce099%3A0xd48fcb558243d321!2sSG!5e0!3m2!1sfr!2sfr!4v1717228415144!5m2!1sfr!2sfr"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                <p>Adresse : SG, 2 Bd de Strasbourg, 75010 Paris</p>
                <div class="contact-info">
                    <p>Contact: <strong>Marie Martin</strong></p>
                    <p>Email: <strong>marie.martin@sg.fr</strong></p>
                    <p>Téléphone: <strong>+33 1 98 76 54 32</strong></p>
                </div>
            </div>
        </div>
        <div class="FOOTER">
            <div class="contact-info">
                <p><strong>Contactez-nous :</strong></p>
                <p>Email : omnesimmobilier@outlook.fr</p>
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
    <script>
    function toggleCode() {
        var codeElement = document.getElementById('code-abonnement');
        if (codeElement.style.display === 'none' || codeElement.style.display === '') {
            codeElement.style.display = 'block';
        } else {
            codeElement.style.display = 'none';
        }
    }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Accueil.js"></script>
    <script src="Creecompte.js"></script>
    <script src="Connexion.js"></script>
</body>

</html>