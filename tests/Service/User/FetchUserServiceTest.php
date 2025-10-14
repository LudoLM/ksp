<?php

namespace App\Tests\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\UserControllerService\FetchUserService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FetchUserServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $userRepository;
    private \PHPUnit\Framework\MockObject\MockObject $security;
    private FetchUserService $fetchUserService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->security = $this->createMock(Security::class);

        $this->fetchUserService = new FetchUserService(
            $this->userRepository,
            $this->security
        );
    }

    public static function dataProviderUser(): iterable
    {
        yield 'user_exists_and_access_granted' => [
            'id' => 1,
            'userExists' => true,
            'accessGranted' => true,
            'isAuthenticated' => null,
            'expectedException' => null,
        ];

        yield 'user_does_not_exist' => [
            'id' => 999,
            'userExists' => false,
            'accessGranted' => null,
            'isAuthenticated' => null,
            'expectedException' => NotFoundHttpException::class,
        ];

        yield 'user_exists_but_access_denied' => [
            'id' => 2,
            'userExists' => true,
            'accessGranted' => false,
            'isAuthenticated' => null,
            'expectedException' => AccessDeniedException::class,
        ];

        yield 'no_id_user_not_authenticated' => [
            'id' => null,
            'userExists' => null,
            'accessGranted' => null,
            'isAuthenticated' => false,
            'expectedException' => AccessDeniedException::class,
        ];

        yield 'no_id_user_authenticated' => [
            'id' => null,
            'userExists' => null,
            'accessGranted' => null,
            'isAuthenticated' => true,
            'expectedException' => null,
        ];
    }

    /**
     * @dataProvider dataProviderUser
     */
    public function testFetchUser(
        ?int $id,
        ?bool $userExists,
        ?bool $accessGranted,
        ?bool $isAuthenticated,
        ?string $expectedException,
    ): void {
        $mockUser = $this->createMock(User::class);

        if (null !== $id) {
            if ($userExists === true) {
                $this->userRepository
                    ->expects($this->once())
                    ->method('find')
                    ->with($id)
                    ->willReturn($mockUser);

                $this->security
                    ->expects($this->once())
                    ->method('isGranted')
                    ->with(UserVoter::VIEW, $mockUser)
                    ->willReturn($accessGranted);
            } else {
                $this->userRepository
                    ->expects($this->once())
                    ->method('find')
                    ->with($id)
                    ->willReturn(null);
            }
        } else {
            $this->security
                ->expects($this->once())
                ->method('getUser')
                ->willReturn($isAuthenticated === true ? $mockUser : null);
        }

        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }

        $result = $this->fetchUserService->fetchUser($id);

        if (null === $expectedException) {
            $this->assertInstanceOf(User::class, $result);
            $this->assertSame($mockUser, $result);
        }
    }
}
