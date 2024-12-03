<?php
use PHPUnit\Framework\TestCase;

class ProduitsTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Créer une connexion PDO avec une base de données en mémoire pour les tests
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer une table temporaire pour les tests
        $this->pdo->exec("
            CREATE TABLE produits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                description TEXT NOT NULL,
                prix REAL NOT NULL,
                date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    // Simuler la fonction getProduits pour le test
    private function getProduits(PDO $pdo, $search = ''): array
    {
        if ($search) {
            $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE :search OR description LIKE :search");
            $stmt->execute([':search' => "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testGetProduitsReturnsAllRecords(): void
    {
        // Ajouter des données dans la table
        $this->pdo->exec("INSERT INTO produits (nom, description, prix) VALUES 
            ('Produit 1', 'Description 1', 10.5),
            ('Produit 2', 'Description 2', 20.5)
        ");

        // Appeler la fonction testée
        $result = $this->getProduits($this->pdo);

        // Vérifier que le résultat est correct
        $this->assertCount(2, $result);
        $this->assertEquals('Produit 1', $result[0]['nom']);
        $this->assertEquals(20.5, $result[1]['prix']);
    }

    public function testGetProduitsWithSearch(): void
    {
        // Ajouter des données
        $this->pdo->exec("INSERT INTO produits (nom, description, prix) VALUES 
            ('Produit A', 'Description A', 30.0),
            ('Produit B', 'Description B', 40.0)
        ");

        // Tester la recherche
        $result = $this->getProduits($this->pdo, 'A');

        // Vérifier les résultats
        $this->assertCount(1, $result);
        $this->assertEquals('Produit A', $result[0]['nom']);
    }
}
