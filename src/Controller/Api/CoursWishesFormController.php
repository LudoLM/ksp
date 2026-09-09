<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\SubmitCoursWishesFormDTO;
use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Helper\SaisonHelper;
use App\Repository\CoursWishesFormRepository;
use App\Repository\PackRepository;
use App\Repository\SeasonPlanningSlotRepository;
use App\Service\CoursWishesFormService\FetchCoursWishesFormService;
use App\Service\CoursWishesFormService\SubmitCoursWishesFormService;
use App\Service\CoursWishesFormService\ValidateCoursWishesFormService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CoursWishesFormController extends AbstractController
{
    private const int LIMIT_PER_PAGE = 15;

    public function __construct(
        private readonly SubmitCoursWishesFormService $submitService,
        private readonly FetchCoursWishesFormService $fetchService,
        private readonly ValidateCoursWishesFormService $validateService,
        private readonly CoursWishesFormRepository $repository,
        private readonly PackRepository $packRepository,
        private readonly SeasonPlanningSlotRepository $seasonPlanningSlotRepository,
        private readonly RateLimiterFactory $coursWishesFormSubmitLimiter,
    ) {
    }

    #[Route('api/public/cours-wishes-form', name: 'api_cours_wishes_form_submit', methods: ['POST'])]
    public function submit(#[MapRequestPayload] SubmitCoursWishesFormDTO $dto, Request $request): JsonResponse
    {
        $user = $this->getUser();

        // Seules les soumissions anonymes (sans compte) sont limitées : une
        // resoumission par un utilisateur déjà connecté n'a pas besoin d'être
        // throttlée, elle est déjà protégée par l'authentification.
        if (!$user instanceof User) {
            $limiter = $this->coursWishesFormSubmitLimiter->create($request->getClientIp() ?? 'unknown');
            if (!$limiter->consume(1)->isAccepted()) {
                return new JsonResponse(['error' => 'Trop de tentatives. Veuillez réessayer plus tard.'], Response::HTTP_TOO_MANY_REQUESTS);
            }
        }

        try {
            return $this->submitForm($dto, $user instanceof User ? $user : null, filledByAdmin: false);
        } catch (UniqueConstraintViolationException) {
            return new JsonResponse(
                ['error' => 'Un dossier existe déjà pour cet email pour cette saison. Contactez l\'administration pour le modifier.'],
                Response::HTTP_CONFLICT
            );
        }
    }

    #[Route('api/admin/users/{id}/cours-wishes-form', name: 'api_admin_cours_wishes_form_submit', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function submitForUser(User $targetUser, #[MapRequestPayload] SubmitCoursWishesFormDTO $dto): JsonResponse
    {
        try {
            return $this->submitForm($dto, $targetUser, filledByAdmin: true);
        } catch (UniqueConstraintViolationException) {
            return new JsonResponse(
                ['error' => 'Un dossier existe déjà pour cet email pour cette saison.'],
                Response::HTTP_CONFLICT
            );
        }
    }

    private function submitForm(SubmitCoursWishesFormDTO $dto, ?User $user, bool $filledByAdmin): JsonResponse
    {
        $pack = $this->packRepository->find($dto->packSouhaiteId);
        if (null === $pack) {
            return new JsonResponse(['error' => 'Pack introuvable'], Response::HTTP_BAD_REQUEST);
        }

        if (!$user instanceof User) {
            if (null === $dto->nom || '' === trim($dto->nom)) {
                return new JsonResponse(['error' => 'Le nom est requis.'], Response::HTTP_BAD_REQUEST);
            }
            if (null === $dto->prenom || '' === trim($dto->prenom)) {
                return new JsonResponse(['error' => 'Le prénom est requis.'], Response::HTTP_BAD_REQUEST);
            }
            if (null === $dto->telephone || '' === trim($dto->telephone)) {
                return new JsonResponse(['error' => 'Le téléphone est requis.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $creneauPrimaire = $this->seasonPlanningSlotRepository->find($dto->creneauPrimaireId);
        if (null === $creneauPrimaire) {
            return new JsonResponse(['error' => 'Créneau prioritaire introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $creneauSecondaire = null !== $dto->creneauSecondaireId ? $this->seasonPlanningSlotRepository->find($dto->creneauSecondaireId) : null;

        $form = $this->submitService->submit(
            email: $dto->email,
            user: $user,
            nom: $dto->nom,
            prenom: $dto->prenom,
            telephone: $dto->telephone,
            creneauPrimaire: $creneauPrimaire,
            creneauSecondaire: $creneauSecondaire,
            packSouhaite: $pack,
            modeReglement: $dto->modeReglement,
            filledByAdmin: $filledByAdmin,
        );

        return $this->json(['id' => $form->getId(), 'status' => $form->getStatus()], Response::HTTP_CREATED);
    }

    #[Route('api/cours-wishes-form', name: 'api_cours_wishes_form_mine', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function mine(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->repository->findCurrentForUser($user, SaisonHelper::current());

        return $this->json($form, Response::HTTP_OK, [], ['groups' => 'wishes_form:read']);
    }

    #[Route('api/admin/cours-wishes-form/pending', name: 'api_admin_cours_wishes_form_pending', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pending(#[MapQueryParameter] int $page = 1): JsonResponse
    {
        return $this->json($this->fetchService->getPendingForms(max(1, $page), self::LIMIT_PER_PAGE));
    }

    #[Route('api/admin/cours-wishes-form/{id}/validate', name: 'api_admin_cours_wishes_form_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(CoursWishesForm $form, Request $request): JsonResponse
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $action = $request->request->getString('action');
        if (!\in_array($action, ['approve', 'correction'], true)) {
            return new JsonResponse(['error' => 'Action invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $reason = $request->request->get('reason');
        $reason = null !== $reason ? trim((string) $reason) : null;

        if ('correction' === $action && (null === $reason || '' === $reason)) {
            return new JsonResponse(['error' => 'Le motif est requis.'], Response::HTTP_BAD_REQUEST);
        }

        if ('approve' === $action && !$form->getCreneauPrimaire() instanceof \App\Entity\SeasonPlanningSlot) {
            return new JsonResponse(['error' => 'Impossible de valider un dossier sans créneau prioritaire.'], Response::HTTP_BAD_REQUEST);
        }

        $this->validateService->updateStatus($form, $action, $admin, $reason);

        return $this->json(['status' => $form->getStatus()]);
    }
}
