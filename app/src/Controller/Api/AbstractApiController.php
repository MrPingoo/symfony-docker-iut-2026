<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contrôleur de base : factorise la sérialisation JSON et la validation,
 * utilisées par tous les contrôleurs de l'API.
 */
abstract class AbstractApiController extends AbstractController
{
    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Sérialise des données en JSON avec des groupes de sérialisation donnés.
     *
     * @param string[] $groups
     */
    protected function json2(mixed $data, int $status = 200, array $groups = []): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', [
            'groups' => $groups,
        ]);

        return new JsonResponse($json, $status, [], json: true);
    }

    /**
     * Valide une entité. Renvoie une JsonResponse 422 si invalide, sinon null.
     */
    protected function validateOrFail(object $entity): ?JsonResponse
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) === 0) {
            return null;
        }

        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()][] = $error->getMessage();
        }

        return new JsonResponse([
            'error' => [
                'status' => 422,
                'message' => 'Données invalides',
                'violations' => $messages,
            ],
        ], 422);
    }
}
