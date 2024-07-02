<?php

namespace App\Controller\Api;

use App\Enum\CoursEnum;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Service\DefaultContext;
use App\Service\DefaultContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: "/", name:"api")]
class CoursController extends AbstractController
{

    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly SerializerInterface $serializer
    )
    {

    }

    #[Route('api/getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(Request $request): JsonResponse
    {
        $cours = $this->coursRepository->findAllSortByDate();
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:read']);

        return new JsonResponse($jsonCours, 200, [], true);
    }


    #[Route('api/getCours/{id}', name: 'cours_show', methods: ['GET'])]
    public function coursFiltered(int $id): JsonResponse
    {
        $cours = $this->coursRepository->find($id);
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:read']);

        return new JsonResponse($jsonCours);
    }

}