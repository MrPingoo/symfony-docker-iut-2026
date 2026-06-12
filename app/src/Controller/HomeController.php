<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    /**
     * Page d'accueil de l'API : liste les endpoints disponibles.
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'name' => 'Cours API Symfony',
            'version' => '1.0',
            'endpoints' => [
                'POST   /api/register' => 'Créer un compte',
                'POST   /api/login' => 'Obtenir un token JWT',
                'GET    /api/me' => 'Profil connecté (auth requise)',
                'GET    /api/categories' => 'Lister les catégories',
                'GET    /api/products' => 'Lister les produits (filtres: q, category, page, limit)',
                'POST   /api/products' => 'Créer un produit (auth requise)',
            ],
        ]);
    }
}
