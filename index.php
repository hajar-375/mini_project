<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';        // Adresse du serveur MySQL
$dbname = 'gestion_produits'; // Nom de la base de données
$username = 'root';         // Nom d'utilisateur MySQL
$password = '';             // Mot de passe MySQL

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? 0;

    if ($nom && $description && $prix >= 0) {
        $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix) VALUES (:nom, :description, :prix)");
        $stmt->execute([
            'nom' => $nom,
            'description' => $description,
            'prix' => floatval($prix)
        ]);
    }

    header('Location: index.php');
    exit;
}

// Supprimer un produit
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header('Location: index.php');
    exit;
}

// Recherche de produits
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE :search OR description LIKE :search");
    $stmt->execute(['search' => '%' . $search . '%']);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $produits = $pdo->query("SELECT * FROM produits")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Produits</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestion de Produits</h1>

    <!-- Formulaire d'ajout -->
    <h2>Ajouter un Produit</h2>
    <form method="POST" action="index.php">
        <input type="hidden" name="action" value="ajouter">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required>
        <br>
        <label for="description">Description :</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <label for="prix">Prix (€) :</label>
        <input type="number" id="prix" name="prix" step="0.01" required>
        <br>
        <button type="submit">Ajouter</button>
    </form>

    <!-- Formulaire de recherche -->
    <h2>Rechercher un Produit</h2>
    <form method="GET" action="index.php">
        <label for="search">Rechercher :</label>
        <input type="text" id="search" name="search" placeholder="Nom ou description" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Rechercher</button>
    </form>

    <!-- Liste des produits -->
    <h2>Liste des Produits</h2>
    <?php if (empty($produits)): ?>
        <p>Aucun produit trouvé.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($produits as $produit): ?>
                <li>
                    <strong><?php echo htmlspecialchars($produit['nom']); ?></strong> - 
                    <?php echo number_format($produit['prix'], 2); ?> €
                    <p><?php echo htmlspecialchars($produit['description']); ?></p>
                    <a href="index.php?supprimer=<?php echo $produit['id']; ?>">Supprimer</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
