<?php

namespace App\Controller\Api;

use App\Repository\TypeCoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;


class TypeCoursController extends AbstractController
{
    public function __construct(
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly SerializerInterface $serializer
    )
    {
    }

    #[Route('/api/getTypeCours', name: 'type_cours_index', methods: ['GET'])]
    public function typeCoursIndex(): JsonResponse
    {
        $typeCours = $this->typeCoursRepository->findAll();
        $jsonTypeCours = $this->serializer->serialize($typeCours, 'json', ['groups' => 'type_cours:index']);
        return new JsonResponse($jsonTypeCours, 200, [], true);
    }
}