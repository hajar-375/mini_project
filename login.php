<?php
session_start();

// Configuration de la base de données (à personnaliser)
$host = 'localhost';
$dbname = 'gestion_projet';
$username = 'root';
$password = '';

// Gestion de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connexion à la base de données avec PDO
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des données du formulaire
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Requête préparée pour vérifier les identifiants
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM utilisateurs WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe
        if ($user && password_verify($password, $user['password_hash'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirection vers la page d'accueil
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = "Identifiants incorrects";
        }
    } catch (PDOException $e) {
        $error_message = "Erreur de connexion à la base de données";
        // Log de l'erreur (à personnaliser)
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .login-container button {
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
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align: center;">Connexion</h2>
        
        <?php
        // Affichage des messages d'erreur
        if (isset($error_message)) {
            echo "<p class='error'>" . htmlspecialchars($error_message) . "</p>";
        }
        ?>

        <form method="post" action="">
            <input type="text" name="email" placeholder="email d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>