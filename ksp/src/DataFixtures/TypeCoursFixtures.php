<?php

namespace App\DataFixtures;

use App\Enum\CoursEnum;
use Doctrine\Persistence\ObjectManager;


class TypeCoursFixtures
{
    public function load(ObjectManager $manager, ) : void
    {
        $typeCours = new TypeCours();
        $typeCours->setLibelle(CoursEnum::CIRCUIT_TRAINING->getValue());
        $manager->persist($typeCours);

        $typeCours1 = new TypeCours();
        $typeCours1->setLibelle(CoursEnum::GYM_ACTIV_BALL->getValue());
        $manager->persist($typeCours1);

        $typeCours2 = new TypeCours();
        $typeCours2->setLibelle(CoursEnum::GYM_DOUCE->getValue());
        $manager->persist($typeCours2);

        $typeCours3 = new TypeCours();
        $typeCours3->setLibelle(CoursEnum::PILATES_DEBUTANT->getValue());
        $manager->persist($typeCours3);

        $typeCours4 = new TypeCours();
        $typeCours4->setLibelle(CoursEnum::PILATES_INTERMEDIAIRES->getValue());
        $manager->persist($typeCours4);

        $typeCours5 = new TypeCours();
        $typeCours5->setLibelle(CoursEnum::STRETCHING->getValue());
        $manager->persist($typeCours5);

        $manager->flush();
    }

}