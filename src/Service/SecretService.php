<?php

namespace App\Service;

use App\Entity\Secret;
use App\Repository\SecretRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class SecretService extends AbstractController
{
    public function __construct(
        private SecretRepository $secretRepository,
        private SerializerInterface $serializer,
    ) {
    }

    public function createSecret(Request $request): Response
    {

        $data = json_decode($request->getContent(), true);
        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new Response('Invalid JSON', Response::HTTP_BAD_REQUEST);
        }
        // Validate required fields and their types
        $errors = $this->validateSecretData($data);

        if (!empty($errors)) {
            return new Response(implode(', ', $errors), Response::HTTP_BAD_REQUEST);
        }

        // Create and set up Secret entity
        $secret = new Secret();
        $secret->setSecretText($data['secret']);
        $secret->setRemainingViews($data['expireAfterViews']);
        $secret->setExpiresAt($data['expireAfter']);

        $this->saveSecret($secret);

        $acceptHeader = $request->headers->get('Accept');
        $format = $request->getPreferredFormat('json'); // Default to JSON if not specified

        if ($acceptHeader === 'text/html') {
            // Render HTML response
            $html = $this->render('secret.html.twig', ['secret' => $secret]);
            return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }

        // Handle JSON or XML formats
        $responseContent = $this->serializer->serialize($secret, $format, [AbstractNormalizer::IGNORED_ATTRIBUTES => ['id']]);
        return new Response($responseContent, Response::HTTP_OK, ['Content-Type' => "application/$format"]);
    }

    public function getSecret(string $hash, Request $request): Response
    {
        // Fetch the secret by hash from the repository
        $secret = $this->findSecret($hash);

        // Check if the secret exists
        if (!$secret) {
            return new Response('Secret not found', Response::HTTP_NOT_FOUND);
        }

        // Check if the secret has expired
        $now = new \DateTimeImmutable('now');
        $expiresAt = $secret->getExpiresAt();
        if ($expiresAt < $now) {
            return new Response('Secret has expired', Response::HTTP_NOT_FOUND);
        }

        // Check if there are remaining views
        if ($secret->getRemainingViews() <= 0) {
            return new Response('No more views available', Response::HTTP_NOT_FOUND);
        }

        // Decrement the remaining views count
        $secret->setRemainingViews($secret->getRemainingViews() - 1);
        $this->saveSecret($secret);

        $acceptHeader = $request->headers->get('Accept');
        $format = $request->getPreferredFormat('json'); // Default to JSON if not specified

        if ($acceptHeader === 'text/html') {
            // Render HTML response
            $html = $this->render('secret.html.twig', ['secret' => $secret]);
            return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }

        $responseContent = $this->serializer->serialize($secret, $format, [AbstractNormalizer::IGNORED_ATTRIBUTES => ['id']]);
        return new Response($responseContent, Response::HTTP_OK, ['Content-Type' => "application/$format"]);
    }


    public function saveSecret(Secret $secret)
    {
        $this->secretRepository->save($secret, true);
    }

    public function findSecret(string $hash)
    {
        return $this->secretRepository->findValidHash($hash);
    }

    private function validateSecretData(array $data): array
    {
        $errors = [];

        // Check for required fields and their types
        if (empty($data['secret'])) {
            $errors[] = 'Field "secret" is required and cannot be empty.';
        } elseif (!is_string($data['secret'])) {
            $errors[] = 'Field "secret" must be a string.';
        }

        if (isset($data['expireAfterViews'])) {
            if (!is_int($data['expireAfterViews']) || $data['expireAfterViews'] <= 0) {
                $errors[] = 'Field "expireAfterViews" must be a positive integer.';
            }
        } else {
            $errors[] = 'Field "expireAfterViews" is required.';
        }

        if (isset($data['expireAfter'])) {
            if (!is_int($data['expireAfter']) || $data['expireAfter'] < 0) {
                $errors[] = 'Field "expireAfter" must be a non-negative integer.';
            }
        } else {
            $errors[] = 'Field "expireAfter" is required.';
        }

        return $errors;
    }
}
