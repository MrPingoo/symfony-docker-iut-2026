<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', name: 'api_categories_')]
class CategoryController extends AbstractApiController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(CategoryRepository $repository): JsonResponse
    {
        $categories = $repository->findBy([], ['name' => 'ASC']);

        return $this->json2($categories, 200, ['category:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Category $category): JsonResponse
    {
        // Symfony récupère automatiquement la Category par son id (ParamConverter).
        // Si elle n'existe pas, une 404 est levée avant d'entrer dans la méthode.
        return $this->json2($category, 200, ['category:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $category = $this->deserialize($request, Category::class);

        if ($response = $this->validateOrFail($category)) {
            return $response;
        }

        $em->persist($category);
        $em->flush();

        return $this->json2($category, 201, ['category:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(Request $request, Category $category, EntityManagerInterface $em): JsonResponse
    {
        // On désérialise PAR-DESSUS l'objet existant (mise à jour partielle possible)
        $this->serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json',
            [
                'object_to_populate' => $category,
                'groups' => ['category:write'],
            ]
        );

        if ($response = $this->validateOrFail($category)) {
            return $response;
        }

        $em->flush();

        return $this->json2($category, 200, ['category:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Category $category, EntityManagerInterface $em): JsonResponse
    {
        // Refus si la catégorie contient encore des produits
        if ($category->getProducts()->count() > 0) {
            return new JsonResponse([
                'error' => [
                    'status' => 409,
                    'message' => 'Impossible de supprimer une catégorie contenant des produits.',
                    'productCount' => $category->getProducts()->count(),
                ],
            ], 409);
        }

        $em->remove($category);
        $em->flush();

        return new JsonResponse(null, 204);
    }

    /**
     * Désérialise le corps JSON en une nouvelle entité Category.
     */
    private function deserialize(Request $request, string $class): Category
    {
        try {
            return $this->serializer->deserialize(
                $request->getContent(),
                $class,
                'json',
                ['groups' => ['category:write']]
            );
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('JSON invalide : '.$e->getMessage());
        }
    }
}
