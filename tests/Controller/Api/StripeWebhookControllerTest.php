<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\StripeWebhookController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\StripeService\StripeCheckoutCreditService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class StripeWebhookControllerTest extends TestCase
{
    private const string SECRET = 'whsec_test_secret';

    private function buildController(?UserRepository $userRepository = null, ?StripeCheckoutCreditService $creditService = null): StripeWebhookController
    {
        $controller = new StripeWebhookController(
            self::SECRET,
            $userRepository ?? $this->createMock(UserRepository::class),
            $creditService ?? $this->createMock(StripeCheckoutCreditService::class),
            $this->createMock(LoggerInterface::class),
        );
        $controller->setContainer($this->createMock(ContainerInterface::class));

        return $controller;
    }

    private function signedRequest(string $payload, ?string $secret = null): Request
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret ?? self::SECRET);

        return Request::create(
            '/api/public/webhook/stripe',
            'POST',
            server: ['HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}"],
            content: $payload,
        );
    }

    public function testInvalidSignatureReturns400(): void
    {
        $controller = $this->buildController();
        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => []]], JSON_THROW_ON_ERROR);
        $request = $this->signedRequest($payload, 'wrong_secret');

        $response = $controller->handle($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testMissingSignatureHeaderReturns400(): void
    {
        $controller = $this->buildController();
        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => []]], JSON_THROW_ON_ERROR);
        $request = Request::create('/api/public/webhook/stripe', 'POST', content: $payload);

        $response = $controller->handle($request);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testUnhandledEventTypeReturns200Ack(): void
    {
        $controller = $this->buildController();
        $payload = json_encode(['type' => 'charge.refunded', 'data' => ['object' => []]], JSON_THROW_ON_ERROR);
        $request = $this->signedRequest($payload);

        $response = $controller->handle($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCheckoutSessionCompletedWithoutClientReferenceIdIsAcknowledgedWithoutCrediting(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->never())->method('find');

        $controller = $this->buildController($userRepository);
        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['object' => 'checkout.session', 'id' => 'cs_test_1', 'client_reference_id' => null]],
        ], JSON_THROW_ON_ERROR);
        $request = $this->signedRequest($payload);

        $response = $controller->handle($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCheckoutSessionCompletedCreditsResolvedUser(): void
    {
        $user = new User();
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('find')->with('42')->willReturn($user);

        $creditService = $this->createMock(StripeCheckoutCreditService::class);
        $creditService->expects($this->once())
            ->method('creditUserForCheckoutSession')
            ->with($user, 'cs_test_2')
            ->willReturn(['status' => 'credited', 'message' => 'Paiement effectué', 'userQuantity' => 5]);

        $controller = $this->buildController($userRepository, $creditService);
        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['object' => 'checkout.session', 'id' => 'cs_test_2', 'client_reference_id' => '42']],
        ], JSON_THROW_ON_ERROR);
        $request = $this->signedRequest($payload);

        $response = $controller->handle($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
