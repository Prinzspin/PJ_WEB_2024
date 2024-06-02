

<?php
//declaration des variables
//le _POST est lier a la page hyml; si on avait mis l'autre methode Get, il faudrais hanger 
$name = isset($_POST["nom"])? $_POST["nom"] : "";
$prenom = isset($_POST["prenom"])? $_POST["prenom"] : "";
$age = isset($_POST["age"])? $_POST["age"] : "";
$phone = isset($_POST["telephone"])? $_POST["telephone"] : "";
$birthday = isset($_POST["naissance"])? $_POST["naissance"] : "";
$erreur = "";

if ($name == "") {
	//le .= signifie += 
$erreur .= "Le champ Nom est vide. <br>";
}

if ($prenom == "") {
$erreur .= "Le champ prenom est vide. <br>";
}

if ($age == "") {
$erreur .= "Le champ Âge est vide. <br>";
}

if ($phone == "") {
$erreur .= "Le champ Téléphone est vide. <br>";
}

if ($birthday == "") {
$erreur .= "Le champ Date de naissance est vide. <br>";
}

if ($erreur == "") {
echo "Formulaire valide.";
} else {
echo "Erreur: <br>" . $erreur;
}
?>
