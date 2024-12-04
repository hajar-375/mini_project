<?php

class ProduitManager
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getProduits($search = '')
    {
        if ($search) {
            $stmt = $this->pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? OR description LIKE ?");
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterProduit($nom, $description, $prix)
    {
        if (!$nom || !$description || !is_numeric($prix) || $prix <= 0) {
            throw new InvalidArgumentException("Données invalides");
        }

        $stmt = $this->pdo->prepare("INSERT INTO produits (nom, description, prix, date_ajout) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$nom, $description, $prix]);
    }

    public function modifierProduit($id, $prix)
    {
        if (!is_numeric($prix) || $prix <= 0) {
            throw new InvalidArgumentException("Prix invalide");
        }

        $stmt = $this->pdo->prepare("UPDATE produits SET prix = ? WHERE id = ?");
        return $stmt->execute([$prix, $id]);
    }

    public function supprimerProduit($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM produits WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
