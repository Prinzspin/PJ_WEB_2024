<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "omnesimmobilier";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'];
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'];

    // Vérifier si les mots de passe correspondent
    if ($nouveau_mot_de_passe !== $confirmer_mot_de_passe) {
        die("Les mots de passe ne correspondent pas.");
    }

    // Hacher le mot de passe
    $hashed_password = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe dans la base de données
    $sql = "UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        // Afficher un message de succès avec JavaScript et rediriger vers Accueil.php
        echo "<script>
                alert('Mot de passe réinitialisé avec succès.');
                window.location.href = 'Accueil.php';
              </script>";
    } else {
        echo "Erreur : " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!-- Formulaire de réinitialisation de mot de passe HTML -->
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
</head>

<body>
    <form method="POST" action="reset_password.php">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>" required>
        <label for="nouveau_mot_de_passe">Nouveau mot de passe:</label>
        <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" required>
        <br>
        <label for="confirmer_mot_de_passe">Confirmer le mot de passe:</label>
        <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required>
        <br>
        <input type="submit" value="Réinitialiser le mot de passe">
    </form>
</body>

</html>
