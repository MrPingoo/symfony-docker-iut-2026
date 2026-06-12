<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Un utilisateur de démonstration ------------------------------
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // --- Catégories ----------------------------------------------------
        $categoriesData = ['Informatique', 'Périphériques', 'Accessoires'];
        $categories = [];
        foreach ($categoriesData as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[$name] = $category;
        }

        // --- Produits ------------------------------------------------------
        $productsData = [
            ['Clavier mécanique', 'Switches rouges, rétroéclairage RGB', 8990, 42, 'Périphériques'],
            ['Souris sans fil', 'Capteur 16000 DPI, 6 boutons', 4990, 87, 'Périphériques'],
            ['Écran 27" 144Hz', 'Dalle IPS, QHD', 24900, 15, 'Informatique'],
            ['SSD NVMe 1To', 'Lecture 7000 Mo/s', 9990, 60, 'Informatique'],
            ['Tapis de souris XL', '900x400mm, surface tissée', 1990, 120, 'Accessoires'],
            ['Casque USB', 'Micro antibruit, son surround', 6990, 33, 'Périphériques'],
        ];

        foreach ($productsData as [$name, $desc, $price, $stock, $cat]) {
            $product = new Product();
            $product->setName($name)
                ->setDescription($desc)
                ->setPriceCents($price)
                ->setStock($stock)
                ->setCategory($categories[$cat]);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
