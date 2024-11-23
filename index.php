<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'gestion_produits';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Initialize variables
$search = $_GET['search'] ?? '';
$produits = [];

// Fetch products
function getProduits($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ?");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? 0;

    if ($nom && $description && is_numeric($prix) && $prix > 0) {
        $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $description, $prix]);
        header('Location: index.php');
        exit;
    } else {
        die("Tous les champs sont obligatoires et le prix doit être un nombre positif.");
    }
}

// Delete a product
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php');
    exit;
}

// Search for products
$produits = getProduits($pdo, $search);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestion des Produits</h1>
    <p>Connecté en tant que : <?php echo htmlspecialchars($_SESSION['username']); ?> 
        | <a href="index.php?logout=true">Déconnexion</a>
    </p>

    <?php
    // Logout logic
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    ?>

    <!-- Add Product Form -->
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

    <!-- Search Form -->
    <h2>Rechercher un Produit</h2>
    <form method="GET" action="index.php">
        <label for="search">Rechercher :</label>
        <input type="text" id="search" name="search" placeholder="Nom ou description" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Rechercher</button>
    </form>

    <!-- Product List -->
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
