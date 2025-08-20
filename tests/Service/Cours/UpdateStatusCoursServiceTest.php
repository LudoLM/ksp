<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Helper\UpdateStatusCoursHelper;
use App\Repository\StatusCoursRepository;
use App\Service\CoursControllerService\UpdateStatusCoursService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(UpdateStatusCoursService::class)]
class UpdateStatusCoursServiceTest extends TestCase
{
    public UpdateStatusCoursHelper&MockObject $updateStatusCoursHelper;
    private UpdateStatusCoursService $updateStatusCoursService;
    private readonly MockObject $messageBus;
    private readonly MockObject $statusCoursRepository;
    private readonly MockObject $em;

    protected function setUp(): void
    {
        $this->updateStatusCoursHelper = $this->createMock(UpdateStatusCoursHelper::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->statusCoursRepository = $this->createMock(StatusCoursRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->updateStatusCoursService = new UpdateStatusCoursService(
            $this->updateStatusCoursHelper,
            $this->messageBus,
            $this->statusCoursRepository,
            $this->em
        );
    }

    public function testUpdateToEnCours(): void
    {
        $cours = new Cours();
        $cours->setDateCours(new \DateTimeImmutable());
        $cours->setDuree(120);
        $this->updateStatusCoursHelper->expects(self::once())
            ->method('checkCoursUpdatable')
            ->with(
                $cours,
                StatusCoursEnum::EN_COURS,
                $cours->getDateCours(),
                $cours->getDuree() * 60 * 1000
            );
        $this->updateStatusCoursService->updateToEnCours($cours);
    }

    public function testUpdateToPasse(): void
    {
        $cours = new Cours();
        $cours->setDateCours(new \DateTimeImmutable());
        $cours->setDuree(120);
        $this->updateStatusCoursHelper->expects(self::once())
            ->method('checkCoursUpdatable')
            ->with(
                $cours,
                StatusCoursEnum::PASSE,
                $cours->getDateCours()->modify('+'.$cours->getDuree().' minutes'),
                UpdateStatusCoursService::DELAI_ARCHIVE * 24 * 60 * 60 * 1000 - ($cours->getDuree() * 60 * 1000)
            );
        $this->updateStatusCoursService->updateToPasse($cours);
    }

    public function testUpdateToArchive(): void
    {
        $cours = new Cours();
        $cours->setDateCours(new \DateTimeImmutable());
        $this->updateStatusCoursHelper->expects(self::once())
            ->method('checkCoursUpdatable')
            ->with(
                $cours,
                StatusCoursEnum::ARCHIVE,
                $cours->getDateCours()->modify('+'.UpdateStatusCoursService::DELAI_ARCHIVE * 24 * 60 .' minutes'),
                0
            );
        $this->updateStatusCoursService->updateToArchive($cours);
    }
}
