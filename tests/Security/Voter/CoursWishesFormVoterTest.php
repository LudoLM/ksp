<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Repository\CoursWishesFormRepository;
use App\Security\Voter\CoursWishesFormVoter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(CoursWishesFormVoter::class)]
class CoursWishesFormVoterTest extends TestCase
{
    private CoursWishesFormRepository&MockObject $repository;
    private CoursWishesFormVoter $voter;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->voter = new CoursWishesFormVoter($this->repository);
    }

    /**
     * @param string[] $roles
     */
    private function tokenFor(?User $user, array $roles = []): TokenInterface&MockObject
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $token->method('getRoleNames')->willReturn($roles);

        return $token;
    }

    public function testAdminIsAlwaysGranted(): void
    {
        $user = new User();
        $this->repository->expects($this->never())->method('hasValidatedFormForCurrentSaison');

        $result = $this->voter->vote($this->tokenFor($user, ['ROLE_ADMIN']), null, ['COURS_WISHES_FORM_VALIDE']);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserWithValidatedFormIsGranted(): void
    {
        $user = new User();
        $this->repository->method('hasValidatedFormForCurrentSaison')->with($user)->willReturn(true);

        $result = $this->voter->vote($this->tokenFor($user, ['ROLE_USER']), null, ['COURS_WISHES_FORM_VALIDE']);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testUserWithoutValidatedFormIsDenied(): void
    {
        $user = new User();
        $this->repository->method('hasValidatedFormForCurrentSaison')->with($user)->willReturn(false);

        $result = $this->voter->vote($this->tokenFor($user, ['ROLE_USER']), null, ['COURS_WISHES_FORM_VALIDE']);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testUnauthenticatedIsDenied(): void
    {
        $result = $this->voter->vote($this->tokenFor(null), null, ['COURS_WISHES_FORM_VALIDE']);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testUnrelatedAttributeAbstains(): void
    {
        $result = $this->voter->vote($this->tokenFor(new User(), ['ROLE_USER']), null, ['SOME_OTHER_ATTRIBUTE']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
