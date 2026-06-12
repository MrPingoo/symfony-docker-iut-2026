<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Recherche paginée avec filtres optionnels.
     *
     * @return array{items: Product[], total: int}
     */
    public function search(
        ?string $q = null,
        ?int $categoryId = null,
        int $page = 1,
        int $limit = 10,
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->orderBy('p.id', 'DESC');

        // Filtre texte sur le nom
        if ($q !== null && $q !== '') {
            $qb->andWhere('p.name LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        // Filtre par catégorie
        if ($categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        // Bornage des paramètres de pagination
        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        $paginator = new Paginator($qb, fetchJoinCollection: false);

        return [
            'items' => iterator_to_array($paginator),
            'total' => count($paginator),
        ];
    }
}
