<?php

use PHPUnit\Framework\TestCase;

class ProduitManagerTest extends TestCase
{
    private $pdo;
    private $produitManager;

    protected function setUp(): void
    {
        // Mock de la connexion PDO
        $this->pdo = $this->createMock(PDO::class);
        $this->produitManager = new ProduitManager($this->pdo);
    }

    public function testAjouterProduitAvecDonneesValides()
    {
        // Mock du PDOStatement
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        // Simulation de la préparation de la requête PDO
        $this->pdo->method('prepare')->willReturn($stmt);

        // Test de l'ajout d'un produit avec des données valides
        $result = $this->produitManager->ajouterProduit("Produit Test", "Description Test", 10.5);

        // Vérification que la méthode retourne true
        $this->assertTrue($result);
    }

    public function testAjouterProduitAvecDonneesInvalides()
    {
        // Vérification que l'ajout avec un nom vide lève une exception
        $this->expectException(InvalidArgumentException::class);
        $this->produitManager->ajouterProduit("", "Description Test", 10.5);
    }

    public function testModifierProduitAvecPrixValide()
    {
        // Mock du PDOStatement
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        // Simulation de la préparation de la requête PDO
        $this->pdo->method('prepare')->willReturn($stmt);

        // Test de la modification d'un produit avec un prix valide
        $result = $this->produitManager->modifierProduit(1, 15.0);

        // Vérification que la méthode retourne true
        $this->assertTrue($result);
    }

    public function testModifierProduitAvecPrixInvalide()
    {
        // Vérification que la modification avec un prix invalide lève une exception
        $this->expectException(InvalidArgumentException::class);
        $this->produitManager->modifierProduit(1, -5.0);
    }

    public function testSupprimerProduit()
    {
        // Mock du PDOStatement
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        // Simulation de la préparation de la requête PDO
        $this->pdo->method('prepare')->willReturn($stmt);

        // Test de la suppression d'un produit
        $result = $this->produitManager->supprimerProduit(1);

        // Vérification que la méthode retourne true
        $this->assertTrue($result);
    }
}
