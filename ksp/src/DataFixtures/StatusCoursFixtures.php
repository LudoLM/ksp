<?php

namespace App\DataFixtures;

use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusCoursFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (StatusCoursEnum::cases() as $index => $statusCoursEnum) {
            $statusCours = new StatusCours();
            $statusCours->setLibelle($statusCoursEnum->getValue());
            $manager->persist($statusCours);
            $this->addReference('statusCours' . $index, $statusCours);
        }

        $manager->flush();
    }
}
