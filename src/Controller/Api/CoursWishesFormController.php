<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\SubmitCoursWishesFormDTO;
use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Exception\InvalidCoursWishesFormSubmissionException;
use App\Helper\SaisonHelper;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\FetchCoursWishesFormService;
use App\Service\CoursWishesFormService\ResolveWishesFormByTokenService;
use App\Service\CoursWishesFormService\SubmitCoursWishesFormRequestService;
use App\Service\CoursWishesFormService\ValidateCoursWishesFormService;
use App\Service\Security\RateLimitGuard;
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
        private readonly SubmitCoursWishesFormRequestService $submitCoursWishesFormRequestService,
        private readonly FetchCoursWishesFormService $fetchService,
        private readonly ValidateCoursWishesFormService $validateService,
        private readonly CoursWishesFormRepository $repository,
        private readonly ResolveWishesFormByTokenService $resolveWishesFormByTokenService,
        private readonly RateLimiterFactory $coursWishesFormSubmitLimiter,
        private readonly RateLimiterFactory $coursWishesFormPrefillLimiter,
        private readonly RateLimitGuard $rateLimitGuard,
    ) {
    }

    #[Route('api/public/cours-wishes-form', name: 'api_cours_wishes_form_submit', methods: ['POST'])]
    public function submit(#[MapRequestPayload] SubmitCoursWishesFormDTO $dto, Request $request): JsonResponse
    {
        $sessionUser = $this->getUser();
        $effectiveUser = $sessionUser instanceof User ? $sessionUser : null;
        $targetForm = null;

        if (!$sessionUser instanceof User) {
            $response = $this->rateLimitGuard->checkOrRespondWithErrorShape($this->coursWishesFormSubmitLimiter, $request->getClientIp() ?? 'unknown', 'Trop de tentatives. Veuillez réessayer plus tard.');
            if ($response instanceof JsonResponse) {
                return $response;
            }
        }

        if (null !== $dto->correctionToken) {
            $targetForm = $this->resolveWishesFormByTokenService->resolveByCorrectionToken($dto->correctionToken);
            if (!$targetForm instanceof CoursWishesForm) {
                return new JsonResponse(['error' => 'Lien invalide ou expiré.'], Response::HTTP_NOT_FOUND);
            }
            $effectiveUser = $targetForm->getUser();
        }

        try {
            return $this->submitForm($dto, $effectiveUser, filledByAdmin: false, targetForm: $targetForm);
        } catch (InvalidCoursWishesFormSubmissionException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
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
        } catch (InvalidCoursWishesFormSubmissionException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UniqueConstraintViolationException) {
            return new JsonResponse(
                ['error' => 'Un dossier existe déjà pour cet email pour cette saison.'],
                Response::HTTP_CONFLICT
            );
        }
    }

    private function submitForm(SubmitCoursWishesFormDTO $dto, ?User $user, bool $filledByAdmin, ?CoursWishesForm $targetForm = null): JsonResponse
    {
        $result = $this->submitCoursWishesFormRequestService->handle($dto, $user, $filledByAdmin, $targetForm);

        if ($result->accountAlreadyExists) {
            return $this->json(['id' => null, 'status' => StatusCoursWishesFormEnum::EN_ATTENTE->value], Response::HTTP_CREATED);
        }

        return $this->json([
            'id' => $user instanceof User ? $result->form()->getId() : null,
            'status' => $result->form()->getStatus(),
        ], Response::HTTP_CREATED);
    }

    #[Route('api/public/cours-wishes-form/prefill', name: 'api_cours_wishes_form_prefill', methods: ['GET'])]
    public function prefill(Request $request, #[MapQueryParameter] string $token): JsonResponse
    {
        $response = $this->rateLimitGuard->checkOrRespondWithErrorShape($this->coursWishesFormPrefillLimiter, $request->getClientIp() ?? 'unknown', 'Trop de tentatives. Veuillez réessayer plus tard.');
        if ($response instanceof JsonResponse) {
            return $response;
        }

        $form = $this->resolveWishesFormByTokenService->resolveByCorrectionToken($token);
        if (!$form instanceof CoursWishesForm) {
            return new JsonResponse(['error' => 'Lien invalide ou expiré.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'email' => $form->getEmail(),
            'nom' => $form->getContactNom(),
            'prenom' => $form->getContactPrenom(),
            'telephone' => $form->getContactTelephone(),
            'hasAccount' => $form->getUser() instanceof User,
        ]);
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
