<?php

namespace App\Controller;

use App\Service\SecretService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/', name: 'api_')]
class SecretController extends AbstractController
{

    public function __construct(
        private SecretService $secretService
    ) {
    }

    #[Route('v1/secret', methods: ['POST'])]
    public function postSecret(Request $request): Response
    {
        //use the service to store secret
        return $this->secretService->createSecret($request);
    }

    #[Route('v1/secret/{hash}', methods: ['GET'])]
    public function getSecret(string $hash, Request $request): Response
    {
        // Use the service to get and validate the secret
        return $this->secretService->getSecret($hash, $request);
    }
}
