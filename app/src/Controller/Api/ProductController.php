<?php

namespace App\Controller\Api;

use App\Dto\ProductInput;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractApiController
{
    /**
     * GET /api/products?q=clavier&category=2&page=1&limit=10
     * Liste paginée et filtrable.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, ProductRepository $repository): JsonResponse
    {
        $q = $request->query->get('q');
        $categoryId = $request->query->get('category');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $result = $repository->search(
            q: $q,
            categoryId: $categoryId !== null ? (int) $categoryId : null,
            page: $page,
            limit: $limit,
        );

        // Réponse enveloppée : données + métadonnées de pagination
        $payload = [
            'data' => $result['items'],
            'meta' => [
                'total' => $result['total'],
                'page' => max(1, $page),
                'limit' => $limit,
                'pages' => (int) ceil($result['total'] / max(1, $limit)),
            ],
        ];

        return $this->json2($payload, 200, ['product:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json2($product, 200, ['product:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
    ): JsonResponse {
        $input = $this->deserializeInput($request);

        if ($response = $this->validateOrFail($input)) {
            return $response;
        }

        // Résolution de la catégorie à partir de son id
        $category = $categoryRepository->find($input->categoryId);
        if ($category === null) {
            return new JsonResponse([
                'error' => [
                    'status' => 422,
                    'message' => 'Données invalides',
                    'violations' => ['categoryId' => ["La catégorie {$input->categoryId} n'existe pas."]],
                ],
            ], 422);
        }

        $product = new Product();
        $product->setName($input->name)
            ->setDescription($input->description)
            ->setPriceCents($input->priceCents)
            ->setStock($input->stock)
            ->setCategory($category);

        $em->persist($product);
        $em->flush();

        return $this->json2($product, 201, ['product:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(
        Request $request,
        Product $product,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
    ): JsonResponse {
        $input = $this->deserializeInput($request);

        if ($response = $this->validateOrFail($input)) {
            return $response;
        }

        $category = $categoryRepository->find($input->categoryId);
        if ($category === null) {
            return new JsonResponse([
                'error' => [
                    'status' => 422,
                    'message' => 'Données invalides',
                    'violations' => ['categoryId' => ["La catégorie {$input->categoryId} n'existe pas."]],
                ],
            ], 422);
        }

        $product->setName($input->name)
            ->setDescription($input->description)
            ->setPriceCents($input->priceCents)
            ->setStock($input->stock)
            ->setCategory($category);

        $em->flush();

        return $this->json2($product, 200, ['product:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return new JsonResponse(null, 204);
    }

    private function deserializeInput(Request $request): ProductInput
    {
        try {
            return $this->serializer->deserialize(
                $request->getContent(),
                ProductInput::class,
                'json'
            );
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('JSON invalide : '.$e->getMessage());
        }
    }
}
