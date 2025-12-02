<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\StatusCoursRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'StatusCours')]
class StatusCoursController extends AbstractController
{
    public function __construct(
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/get-status-cours', name: 'status_cours_index', methods: ['GET'])]
    public function statusCoursIndex(): JsonResponse
    {
        $statusCours = $this->statusCoursRepository->findAll();
        $jsonStatusCours = $this->serializer->serialize($statusCours, 'json', ['groups' => 'status_cours:index']);

        return new JsonResponse($jsonStatusCours, \Symfony\Component\HttpFoundation\Response::HTTP_OK, [], true);
    }
}
