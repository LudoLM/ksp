<?php

namespace App\Controller\Api;

use App\DTO\CreateCoursDTO;
use App\Entity\TypeCours;
use App\Repository\TypeCoursRepository;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class TypeCoursController extends AbstractController
{
    public function __construct(
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly SerializerInterface $serializer
    )
    {
    }

    #[Route('/api/getTypesCours', name: 'type_cours_index', methods: ['GET'])]
    public function typeCoursIndex(): JsonResponse
    {
        $typeCours = $this->typeCoursRepository->findAll();
        $jsonTypeCours = $this->serializer->serialize($typeCours, 'json', ['groups' => 'type_cours:index']);
        return new JsonResponse($jsonTypeCours, 200, [], true);
    }


    #[Route('/api/getTypeCours/{id}', name: 'type_cours_detail', methods: ['GET'])]
    public function typeCoursFiltered(int $id): JsonResponse
    {
        $typeCours = $this->typeCoursRepository->find($id);
        $jsonTypeCours = $this->serializer->serialize($typeCours, 'json', ['groups' => 'type_cours:detail']);
        return new JsonResponse($jsonTypeCours);
    }

    #[Route('/api/typeCours/create', name: 'type_cours_create', methods: ['POST'])]
    public function typeCoursCreate(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $nom = $request->request->get('libelle') === "null" ? "" : $request->request->get('libelle');
        $descriptif = $request->request->get('descriptif') === "null" ? "" : $request->request->get('descriptif');
        $image = $request->files->get('image');

        $typeCours = new TypeCours();
        $typeCours->setLibelle($nom);
        $typeCours->setDescriptif($descriptif);

        if ($image instanceof UploadedFile) {
            $fileName = "thumbnail-" . uniqid() . "." . $image->guessExtension();
            $image->move($this->getParameter('kernel.project_dir') . "/assets/images/uploads", $fileName);
            $typeCours->setThumbnail($fileName);
        }

        $violations = $validator->validate($typeCours);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    $violation->getPropertyPath() =>  $violation->getMessage(),
                ];
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        $em->persist($typeCours);
        $em->flush();

        return new JsonResponse(['success' => 'Type de cours créé avec succès!'], 201);
    }

    #[Route('/api/typeCours/edit/{id}', name: 'type_cours_update', methods: ['POST'])]
    public function typeCoursEdit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        $typeCours = $this->typeCoursRepository->find($id);
        if (!$typeCours) {
            return new JsonResponse(['error' => 'Type de cours non trouvé'], 404);
        }

        $nom = $request->request->get('nom');
        $image = $request->files->get('image');
        if ($nom !== null) {
            $typeCours->setLibelle($nom);
        }
        if ($image instanceof UploadedFile) {
            $fileName = "thumbnail-" . uniqid() . "." . $image->guessExtension();
            $image->move($this->getParameter('kernel.project_dir') . "/assets/images/uploads", $fileName);
            $typeCours->setThumbnail($fileName);
        }

        $em->persist($typeCours);
        $em->flush();

        return new JsonResponse(['success' => 'Type de cours modifié avec succès!'], 200);

    }
}
