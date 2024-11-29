<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'votre_base_de_donnees';
$username_db = 'votre_utilisateur';
$password_db = 'votre_mot_de_passe';

$error_message = '';
$success_message = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connexion à la base de données
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des données du formulaire
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation des données
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error_message = "Tous les champs sont obligatoires";
        } elseif ($password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas";
        } elseif (strlen($password) < 8) {
            $error_message = "Le mot de passe doit contenir au moins 8 caractères";
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->fetch()) {
                $error_message = "Cette adresse email est déjà utilisée";
            } else {
                // Hachage du mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insertion du nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, password_hash) VALUES (:nom, :prenom, :email, :password_hash)");
                $stmt->execute([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password_hash' => $password_hash
                ]);

                // Connexion automatique après inscription
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['email'] = $email;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;

                // Redirection vers le tableau de bord
                header('Location: dashboard.php');
                exit();
            }
        }
    } catch (PDOException $e) {
        $error_message = "Erreur de connexion à la base de données";
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        .inscription-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        .inscription-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .inscription-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="inscription-container">
        <h2 style="text-align: center;">Créer un compte</h2>
        
        <?php
        // Affichage des messages d'erreur
        if (!empty($error_message)) {
            echo "<p class='error'>" . htmlspecialchars($error_message) . "</p>";
        }
        ?>

        <form method="post" action="">
            <input type="text" name="nom" placeholder="Nom" required 
                   value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            
            <input type="text" name="prenom" placeholder="Prénom" required
                   value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            
            <input type="email" name="email" placeholder="Adresse email" required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            
            <input type="password" name="password" placeholder="Mot de passe" required>
            
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            
            <button type="submit">S'inscrire</button>
        </form>

        <div class="login-link">
            <p>Déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </div>
</body>
</html>