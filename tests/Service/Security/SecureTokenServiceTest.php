<?php

declare(strict_types=1);

namespace App\Tests\Service\Security;

use App\Service\Security\SecureTokenService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[CoversClass(SecureTokenService::class)]
class SecureTokenServiceTest extends TestCase
{
    private TokenGeneratorInterface&MockObject $tokenGenerator;
    private SecureTokenService $service;

    protected function setUp(): void
    {
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $this->service = new SecureTokenService($this->tokenGenerator);
    }

    public function testGenerateDelegatesToTheTokenGenerator(): void
    {
        $this->tokenGenerator->method('generateToken')->willReturn('raw-token-value');

        $this->assertSame('raw-token-value', $this->service->generate());
    }

    public function testHashIsDeterministicSha256(): void
    {
        $this->assertSame(hash('sha256', 'abc'), $this->service->hash('abc'));
    }

    public function testMatchesAcceptsTheCorrectToken(): void
    {
        $storedHash = hash('sha256', 'correct-token');

        $this->assertTrue($this->service->matches('correct-token', $storedHash));
    }

    public function testMatchesRejectsAWrongToken(): void
    {
        $storedHash = hash('sha256', 'correct-token');

        $this->assertFalse($this->service->matches('wrong-token', $storedHash));
    }

    public function testMatchesRejectsWhenNoHashIsStored(): void
    {
        $this->assertFalse($this->service->matches('any-token', null));
    }
}
