<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test fonctionnel : on simule de vraies requêtes HTTP sur l'API.
 *
 * Pré-requis : base de test créée + migrée + fixtures chargées en env test.
 *   APP_ENV=test php bin/console doctrine:database:create
 *   APP_ENV=test php bin/console doctrine:migrations:migrate -n
 *   APP_ENV=test php bin/console doctrine:fixtures:load -n
 */
class ProductApiTest extends WebTestCase
{
    public function testListProductsIsPublicAndPaginated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products?limit=3');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertLessThanOrEqual(3, count($data['data']));
    }

    public function testCreateProductRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: json_encode([
            'name' => 'Produit test',
            'priceCents' => 1000,
            'stock' => 5,
            'categoryId' => 1,
        ]));

        // Sans token JWT, l'accès en écriture est refusé (401)
        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginThenCreateProduct(): void
    {
        $client = static::createClient();

        // 1) Connexion → récupération du token
        $client->request('POST', '/api/login', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: json_encode([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]));

        $this->assertResponseIsSuccessful();
        $token = json_decode($client->getResponse()->getContent(), true)['token'] ?? null;
        $this->assertNotNull($token);

        // 2) Création d'un produit avec le token
        $client->request('POST', '/api/products', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ], content: json_encode([
            'name' => 'Produit créé en test',
            'priceCents' => 4200,
            'stock' => 10,
            'categoryId' => 1,
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Produit créé en test', $data['name']);
    }
}
