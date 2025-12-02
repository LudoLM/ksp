<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\AssignWeekDTO;
use App\DTO\CreateWeekTypeDTO;
use App\DTO\IntervalDateDTO;
use App\Entity\WeekType;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\WeekTypeRepository;
use App\Serializer\CreateWeekTypeDTOToWeekTypeDenormalizer;
use App\Service\CoursControllerService\UpdateStatusCoursService;
use App\Service\CreateMultiplesCoursFromWeekTypeService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'WeekType')]
class WeekTypeController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly WeekTypeRepository $weekTypeRepository,
        private readonly CreateMultiplesCoursFromWeekTypeService $createMultiplesCoursFromWeekTypeService,
        private readonly CoursRepository $coursRepository,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly UpdateStatusCoursService $updateStatusCoursService,
        private readonly CreateWeekTypeDTOToWeekTypeDenormalizer $createWeekTypeDenormalizer,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('api/admin/week-types', name: 'api_get_week_type', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        $weekTypes = $this->weekTypeRepository->findAll();

        $data = $this->serializer->serialize($weekTypes, 'json', ['groups' => 'week_type:index']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('api/admin/week-types/light', name: 'api_get_super_light_all_week_type', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getSuperLightAllWeekType(): JsonResponse
    {
        $weekTypes = $this->weekTypeRepository->findSuperLightAllWeekType();

        $data = $this->serializer->serialize($weekTypes, 'json', ['groups' => 'week_type:index']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('api/admin/week-types/{id}', name: 'api_get_week_type_by_id', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getWeekTypeById(int $id): Response
    {
        $weekType = $this->weekTypeRepository->find($id);

        if (null === $weekType) {
            return new JsonResponse(['message' => 'Semaine type non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($weekType, 'json', ['groups' => 'week_type:index']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('api/admin/week-types/create', name: 'week_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createWeekType(
        #[MapRequestPayload]
        CreateWeekTypeDTO $createWeekTypeDTO,
    ): JsonResponse {
        $weekType = $this->createWeekTypeDenormalizer->denormalize($createWeekTypeDTO, WeekType::class);

        $this->em->persist($weekType);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Semaine type créée avec succès',
        ], Response::HTTP_CREATED);
    }

    #[Route('api/admin/week/assign', name: 'api_week_assign', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createWeek(
        #[MapRequestPayload] AssignWeekDTO $assignWeekDTO,
    ): JsonResponse {
        $this->createMultiplesCoursFromWeekTypeService->create($assignWeekDTO);
        $date = new \DateTime($assignWeekDTO->dateMonday);

        return new JsonResponse(['message' => 'Semaine type assignée à la semaine du '.$date->format('d/m/y')], Response::HTTP_CREATED);
    }

    #[Route('api/admin/week/open', name: 'api_week_open', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function openWeek(
        #[MapRequestPayload] IntervalDateDTO $intervalDateDTO,
    ): JsonResponse {
        $cours = $this->coursRepository->findAllSortByDate(null, $intervalDateDTO->startDate, $intervalDateDTO->endDate->modify('1 day'), $this->statusCoursRepository->find('4'));
        foreach ($cours as $coursItem) {
            $this->updateStatusCoursService->prepareAndLaunchCours($coursItem);
        }
        $this->em->flush();

        return $this->json([
            'message' => 'Les cours en création de la semaine ont été ouverts avec succès',
            'cours' => $cours,
        ], Response::HTTP_OK, [], ['groups' => 'cours:index']);
    }
}
