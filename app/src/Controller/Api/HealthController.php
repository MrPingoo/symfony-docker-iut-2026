<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_health_')]
class HealthController extends AbstractApiController
{

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'timestamp' => (new \DateTime())->format(DATE_ATOM),
        ]);
    }
}
