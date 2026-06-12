<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Intercepte TOUTES les exceptions et renvoie une réponse JSON cohérente,
 * uniquement pour les routes commençant par /api.
 *
 * Sans ça, Symfony renverrait une page HTML d'erreur, inadaptée à une API.
 */
class ApiExceptionListener
{
    public function __construct(private readonly bool $debug = false)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // On ne gère que les routes API
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        // Code HTTP : celui de l'exception si c'en est une "HTTP", sinon 500
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $payload = [
            'error' => [
                'status' => $status,
                'message' => $exception->getMessage() ?: 'Erreur interne du serveur',
            ],
        ];

        // En mode debug uniquement, on ajoute des infos techniques
        if ($this->debug && $status >= 500) {
            $payload['error']['exception'] = $exception::class;
            $payload['error']['file'] = $exception->getFile().':'.$exception->getLine();
        }

        $event->setResponse(new JsonResponse($payload, $status));
    }
}
