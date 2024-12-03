<<<<<<< HEAD
<?php

 


// Connexion à la base de données
$host = 'localhost';
$dbname = 'gestion_projets';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Initialisation des variables
$produits = [];
$search = '';

// Fonction pour récupérer les produits
function getProduits($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ?");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? 0;

    // Validation des données
    if ($nom && $description && is_numeric($prix) && $prix > 0) {
        // Insertion dans la base de données
        $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix, date_ajout) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$nom, $description, $prix]);

        header('Location: index.php');
        exit;
    } else {
        die("Tous les champs sont obligatoires et le prix doit être un nombre positif.");
    }
}

// Supprimer un produit
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: index.php');
    exit;
}

// Rechercher un produit
$search = $_GET['search'] ?? '';
$produits = getProduits($pdo, $search);

// Fonction pour récupérer les produits de l'API Fake Store
function getFakeStoreProducts() {
    try {
        $url = 'https://fakestoreapi.com/products';
        $context = stream_context_create([
            'http' => [
                'timeout' => 10
            ]
        ]);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            return [];
        }
        
        $products = json_decode($response, true);
        
        return array_map(function($product) {
            return [
                'id' => 'API_' . $product['id'],
                'nom' => $product['title'],
                'description' => $product['description'],
                'prix' => $product['price'],
                'image' => $product['image'],
                'categorie' => $product['category'],
                'source' => 'API'
            ];
        }, $products);
    } catch (Exception $e) {
        error_log("Erreur de récupération des produits API: " . $e->getMessage());
        return [];
    }
}

// Récupérer les produits de l'API
$fakeStoreProducts = getFakeStoreProducts();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Reset and Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(-45deg, #000080,#ff6b6b, #ee7752, #e73c7e, #23a6d5, #23d5ab ,#000000);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #fff;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* Navbar Styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 5%;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(45deg, gold, #4ecdc4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: textShine 3s infinite alternate;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-form {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            padding: 5px 15px;
            transition: all 0.3s ease;
        }

        .navbar-form input {
            border: none;
            background: transparent;
            color: white;
            outline: none;
            margin-right: 10px;
        }

        .add-product-btn {
            background-color: #4ecdc4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Product List Styles */
.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 15px;
}
        /* Responsive grid for product list */
.product-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Dynamic card size */
    gap: 15px;
    margin-top: 20px;
}

.product-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: auto; /* Let the card grow based on content */
    padding: 10px;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.product-card:hover {
    transform: scale(1.05);
}

.product-card img {
    width: 100%;
    height: auto; /* Adjust height based on image aspect ratio */
    object-fit: cover;
    border-radius: 10px;
}

.product-card-content {
    flex-grow: 1; /* Allow content to stretch and fill space */
    margin-top: 10px;
}

.product-card h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.product-card p {
    font-size: 14px;
    line-height: 1.5;
}

.product-card .price {
    font-size: 16px;
    font-weight: bold;
    margin-top: 10px;
}

/* Make the product card smaller on smaller screens */
@media (max-width: 768px) {
    .product-card {
        min-height: 250px; /* Minimum height for smaller cards */
    }

    .product-card img {
        height: 150px; /* Adjust image height on smaller screens */
    }

    .product-card h3 {
        font-size: 16px;
    }

    .product-card p {
        font-size: 13px;
    }
}

        /* Modal Styles */
        #addProductModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        #addProductModal form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
        }

        /* Footer Styles */
        /* Footer Styles */
footer {
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 20px 0;
    text-align: center;
    /* Remove position: fixed and positioning properties */
    margin-top: 40px; /* Add margin-top to prevent overlapping with content */
}

footer .social-links {
    margin-top: 10px;
}

footer .social-links a {
    color: white;
    text-decoration: none;
    margin: 0 10px;
    font-size: 20px;
}

footer .social-links a:hover {
    color: #4ecdc4;
}

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">Gestion Produits</div>
        <div class="navbar-menu">
            <form class="navbar-form" method="GET" action="index.php">
                <input type="text" name="search" placeholder="Rechercher un produit">
                <button type="submit">Rechercher</button>
            </form>
            <button class="add-product-btn" onclick="openAddProductModal()">Ajouter un produit</button>
        </div>
    </nav>

    <!-- Modal -->
    <div id="addProductModal">
        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="ajouter">
            <input type="text" name="nom" placeholder="Nom du produit" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="number" name="prix" step="0.01" placeholder="Prix" required>
            <button type="submit">Ajouter Produit</button>
        </form>
    </div>

    <!-- Liste des produits -->
    <div class="container">
        <h2>Our Products</h2>
        <?php if (empty($fakeStoreProducts)): ?>
            <p>Aucun produit disponible depuis l'API.</p>
        <?php else: ?>
            <div class="product-list">
                <?php foreach ($fakeStoreProducts as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
                        <div class="product-card-content">
                            <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p><?php echo number_format($product['prix'], 2); ?> €</p>
                            <p>Catégorie: <?php echo htmlspecialchars($product['categorie']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Vos Produits</h2>
        <?php if (empty($produits)): ?>
            <p>Aucun produit ajouté.</p>
        <?php else: ?>
            <div class="product-list">
                <?php foreach ($produits as $produit): ?>
                    <div class="product-card">
                        <div class="product-card-content">
                            <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($produit['description']); ?></p>
                            <p><?php echo number_format($produit['prix'], 2); ?> €</p>
                            <a href="?supprimer=<?php echo $produit['id']; ?>">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>Contactez-nous :</p>
        <div class="social-links">
            <a href="mailto:example@example.com"><i class="fas fa-envelope"></i> Email</a>
            <a href="https://wa.me/1234567890"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <a href="https://instagram.com/example"><i class="fab fa-instagram"></i> Instagram</a>
        </div>
    </footer>

   
          
    </script>
</body>
</html>
=======
<?php

 


// Connexion à la base de données
$host = 'localhost';
$dbname = 'gestion_projets';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Initialisation des variables
$produits = [];
$search = '';

// Fonction pour récupérer les produits
function getProduits($pdo, $search = '') {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ?");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? 0;

    // Validation des données
    if ($nom && $description && is_numeric($prix) && $prix > 0) {
        // Insertion dans la base de données
        $stmt = $pdo->prepare("INSERT INTO produits (nom, description, prix, date_ajout) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$nom, $description, $prix]);

        header('Location: index.php');
        exit;
    } else {
        die("Tous les champs sont obligatoires et le prix doit être un nombre positif.");
    }
}

// Supprimer un produit
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: index.php');
    exit;
}

// Rechercher un produit
$search = $_GET['search'] ?? '';
$produits = getProduits($pdo, $search);

// Fonction pour récupérer les produits de l'API Fake Store
function getFakeStoreProducts() {
    try {
        $url = 'https://fakestoreapi.com/products';
        $context = stream_context_create([
            'http' => [
                'timeout' => 10
            ]
        ]);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            return [];
        }
        
        $products = json_decode($response, true);
        
        return array_map(function($product) {
            return [
                'id' => 'API_' . $product['id'],
                'nom' => $product['title'],
                'description' => $product['description'],
                'prix' => $product['price'],
                'image' => $product['image'],
                'categorie' => $product['category'],
                'source' => 'API'
            ];
        }, $products);
    } catch (Exception $e) {
        error_log("Erreur de récupération des produits API: " . $e->getMessage());
        return [];
    }
}

// Récupérer les produits de l'API
$fakeStoreProducts = getFakeStoreProducts();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Reset and Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(-45deg, #000080,#ff6b6b, #ee7752, #e73c7e, #23a6d5, #23d5ab ,#000000);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #fff;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* Navbar Styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 5%;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(45deg, gold, #4ecdc4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: textShine 3s infinite alternate;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-form {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            padding: 5px 15px;
            transition: all 0.3s ease;
        }

        .navbar-form input {
            border: none;
            background: transparent;
            color: white;
            outline: none;
            margin-right: 10px;
        }

        .add-product-btn {
            background-color: #4ecdc4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Product List Styles */
.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 15px;
}
        /* Responsive grid for product list */
.product-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Dynamic card size */
    gap: 15px;
    margin-top: 20px;
}

.product-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: auto; /* Let the card grow based on content */
    padding: 10px;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.product-card:hover {
    transform: scale(1.05);
}

.product-card img {
    width: 100%;
    height: auto; /* Adjust height based on image aspect ratio */
    object-fit: cover;
    border-radius: 10px;
}

.product-card-content {
    flex-grow: 1; /* Allow content to stretch and fill space */
    margin-top: 10px;
}

.product-card h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.product-card p {
    font-size: 14px;
    line-height: 1.5;
}

.product-card .price {
    font-size: 16px;
    font-weight: bold;
    margin-top: 10px;
}

/* Make the product card smaller on smaller screens */
@media (max-width: 768px) {
    .product-card {
        min-height: 250px; /* Minimum height for smaller cards */
    }

    .product-card img {
        height: 150px; /* Adjust image height on smaller screens */
    }

    .product-card h3 {
        font-size: 16px;
    }

    .product-card p {
        font-size: 13px;
    }
}

        /* Modal Styles */
        #addProductModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        #addProductModal form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
        }

        /* Footer Styles */
        /* Footer Styles */
footer {
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 20px 0;
    text-align: center;
    /* Remove position: fixed and positioning properties */
    margin-top: 40px; /* Add margin-top to prevent overlapping with content */
}

footer .social-links {
    margin-top: 10px;
}

footer .social-links a {
    color: white;
    text-decoration: none;
    margin: 0 10px;
    font-size: 20px;
}

footer .social-links a:hover {
    color: #4ecdc4;
}

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">Gestion Produits</div>
        <div class="navbar-menu">
            <form class="navbar-form" method="GET" action="index.php">
                <input type="text" name="search" placeholder="Rechercher un produit">
                <button type="submit">Rechercher</button>
            </form>
            <button class="add-product-btn" onclick="openAddProductModal()">Ajouter un produit</button>
        </div>
    </nav>

    <!-- Modal -->
    <div id="addProductModal">
        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="ajouter">
            <input type="text" name="nom" placeholder="Nom du produit" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="number" name="prix" step="0.01" placeholder="Prix" required>
            <button type="submit">Ajouter Produit</button>
        </form>
    </div>

    <!-- Liste des produits -->
    <div class="container">
        <h2>Our Products</h2>
        <?php if (empty($fakeStoreProducts)): ?>
            <p>Aucun produit disponible depuis l'API.</p>
        <?php else: ?>
            <div class="product-list">
                <?php foreach ($fakeStoreProducts as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
                        <div class="product-card-content">
                            <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p><?php echo number_format($product['prix'], 2); ?> €</p>
                            <p>Catégorie: <?php echo htmlspecialchars($product['categorie']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Vos Produits</h2>
        <?php if (empty($produits)): ?>
            <p>Aucun produit ajouté.</p>
        <?php else: ?>
            <div class="product-list">
                <?php foreach ($produits as $produit): ?>
                    <div class="product-card">
                        <div class="product-card-content">
                            <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($produit['description']); ?></p>
                            <p><?php echo number_format($produit['prix'], 2); ?> €</p>
                            <a href="?supprimer=<?php echo $produit['id']; ?>">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>Contactez-nous :</p>
        <div class="social-links">
            <a href="mailto:example@example.com"><i class="fas fa-envelope"></i> Email</a>
            <a href="https://wa.me/1234567890"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <a href="https://instagram.com/example"><i class="fab fa-instagram"></i> Instagram</a>
        </div>
    </footer>

   
          
    </script>
</body>
</html>
>>>>>>> 1a9ce9c25ab524099a74a9b34c628e3224e8518a
