<?php

declare(strict_types=1);

namespace App\Tests\Service\Security;

use App\Service\Security\RateLimitGuard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(RateLimitGuard::class)]
class RateLimitGuardTest extends TestCase
{
    private RateLimitGuard $guard;

    protected function setUp(): void
    {
        $this->guard = new RateLimitGuard();
    }

    /**
     * RateLimiterFactory est `final` (non mockable) : on utilise une vraie
     * instance adossée à un stockage en mémoire, avec une limite volontairement
     * basse pour déclencher/éviter le rejet de façon déterministe.
     */
    private function createLimiterFactory(int $limit): RateLimiterFactory
    {
        return new RateLimiterFactory(
            ['id' => 'test_'.uniqid(), 'policy' => 'fixed_window', 'limit' => $limit, 'interval' => '1 minute'],
            new InMemoryStorage(),
        );
    }

    public function testCheckOrRespondReturnsNullWhenAllowed(): void
    {
        $result = $this->guard->checkOrRespond($this->createLimiterFactory(5), 'user-1', 'Trop de tentatives.');

        $this->assertNull($result);
    }

    public function testCheckOrRespondReturnsTypeMessageShapeWhenRejected(): void
    {
        $factory = $this->createLimiterFactory(1);
        $this->guard->checkOrRespond($factory, 'user-1', 'Trop de tentatives.');

        $result = $this->guard->checkOrRespond($factory, 'user-1', 'Trop de tentatives.');

        $this->assertNotNull($result);
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $result->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"type":"error","message":"Trop de tentatives."}',
            (string) $result->getContent(),
        );
    }

    public function testCheckOrRespondWithErrorShapeReturnsNullWhenAllowed(): void
    {
        $result = $this->guard->checkOrRespondWithErrorShape($this->createLimiterFactory(5), 'user-1', 'Trop de tentatives.');

        $this->assertNull($result);
    }

    public function testCheckOrRespondWithErrorShapeReturnsErrorShapeWhenRejected(): void
    {
        $factory = $this->createLimiterFactory(1);
        $this->guard->checkOrRespondWithErrorShape($factory, 'user-1', 'Trop de tentatives.');

        $result = $this->guard->checkOrRespondWithErrorShape($factory, 'user-1', 'Trop de tentatives.');

        $this->assertNotNull($result);
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $result->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"Trop de tentatives."}',
            (string) $result->getContent(),
        );
    }
}
