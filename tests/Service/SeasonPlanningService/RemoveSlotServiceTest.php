<?php

declare(strict_types=1);

namespace App\Tests\Service\SeasonPlanningService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\SeasonPlanningSlot;
use App\Entity\TypeCours;
use App\Repository\CoursWishesFormRepository;
use App\Service\SeasonPlanningService\RemoveSlotService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveSlotService::class)]
class RemoveSlotServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private CoursWishesFormRepository&MockObject $wishesFormRepository;
    private RemoveSlotService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->wishesFormRepository = $this->createMock(CoursWishesFormRepository::class);
        $this->service = new RemoveSlotService($this->em, $this->wishesFormRepository);
    }

    private function createForm(SeasonPlanningSlot $slot, bool $asPrimaire): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack());
        $form->setModeReglement('CbComptant');
        $form->setStatus('EnAttente');
        $form->setSubmittedAt(new \DateTimeImmutable());
        if ($asPrimaire) {
            $form->setCreneauPrimaire($slot);
        } else {
            $form->setCreneauSecondaire($slot);
        }

        return $form;
    }

    public function testRemovingTheOnlyOptionDeletesTheSlotAndNullsOutReferencingForms(): void
    {
        $typeCours = new TypeCours();
        $slot = new SeasonPlanningSlot();
        $slot->addTypeCoursOption($typeCours);

        $formAsPrimaire = $this->createForm($slot, asPrimaire: true);
        $formAsSecondaire = $this->createForm($slot, asPrimaire: false);

        $this->wishesFormRepository->method('findByCreneau')->with($slot)->willReturn([$formAsPrimaire, $formAsSecondaire]);
        $this->em->expects($this->once())->method('remove')->with($slot);
        $this->em->expects($this->once())->method('flush');

        $this->service->removeTypeCoursFromSlot($slot, $typeCours);

        $this->assertNull($formAsPrimaire->getCreneauPrimaire());
        $this->assertNull($formAsSecondaire->getCreneauSecondaire());
    }

    public function testRemovingOneOptionAmongSeveralKeepsTheSlotAndDoesNotTouchReferencingForms(): void
    {
        $firstTypeCours = new TypeCours();
        $secondTypeCours = new TypeCours();
        $slot = new SeasonPlanningSlot();
        $slot->addTypeCoursOption($firstTypeCours);
        $slot->addTypeCoursOption($secondTypeCours);

        $form = $this->createForm($slot, asPrimaire: true);

        $this->wishesFormRepository->expects($this->never())->method('findByCreneau');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->once())->method('flush');

        $this->service->removeTypeCoursFromSlot($slot, $firstTypeCours);

        $this->assertFalse($slot->getTypeCoursOptions()->contains($firstTypeCours));
        $this->assertTrue($slot->getTypeCoursOptions()->contains($secondTypeCours));
        $this->assertSame($slot, $form->getCreneauPrimaire());
    }
}
