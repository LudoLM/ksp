<?php

namespace App\DataFixtures;

use App\Entity\TypeCours;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeCoursFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $typeCours = new TypeCours();
        $typeCours->setLibelle("Gym douce");
        $manager->persist($typeCours);
        $this->addReference("typeCours0", $typeCours);

        $typeCours1 = new TypeCours();
        $typeCours1->setLibelle("Stretching");
        $manager->persist($typeCours1);
        $this->addReference("typeCours1", $typeCours1);

        $typeCours2 = new TypeCours();
        $typeCours2->setLibelle("Circuit Training");
        $manager->persist($typeCours2);
        $this->addReference("typeCours2", $typeCours2);

        $typeCours3 = new TypeCours();
        $typeCours3->setLibelle("Gym Activ Ball");
        $manager->persist($typeCours3);
        $this->addReference("typeCours3", $typeCours3);

        $typeCours4 = new TypeCours();
        $typeCours4->setLibelle("Pilates débutant");
        $manager->persist($typeCours4);
        $this->addReference("typeCours4", $typeCours4);

        $typeCours5 = new TypeCours();
        $typeCours5->setLibelle("Pilates intermédiaires");
        $manager->persist($typeCours5);
        $this->addReference("typeCours5", $typeCours5);

        $manager->flush();

    }

}
