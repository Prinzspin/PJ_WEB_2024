<?php
$login = isset($_POST["identifiant"])? $_POST["identifiant"] : "";
$pass = isset($_POST["passw"])? $_POST["passw"] : "";
//Associative array: Utilisateur => mots de passe

//Dans cet exemple, seulement 3 utilisateurs sont connus
$users = array(
	"toto" => "totomdp", 
 "titi" => "titimdp", 
 "tutu" => "123tutu123");


//Verifier si $login est dans le tableau des utilisateurs
$found = false;
foreach ($users as $key => $value) {
if ($key == $login) {
$found = true;
break;
}
}


//Si l'utilisateur est valide, vérifier son mot de passe
$connexion = false;
if ($found) {
for ($i = 0; $i < count($users); $i++) {
if ($users[$login] == $pass) {
$connexion = true;
break;
} 
}
}

//Message
if (!$found) {
echo "Connexion refusée. Utilisateur inconnu.";
} else {
if ($connexion) {
echo "Connexion okay.";
} else {
echo "Connexion refusée. Mot de passe invalide.";
}
}
?>
