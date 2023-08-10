<?php

namespace App\DataFixtures;

use App\Entity\Cours;
use App\Enum\CoursEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CoursFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /*$faker = Factory::create('fr_FR');
        $sortie1 = Array();
        for ($i = 1; $i < 30; $i++) {
            $cours[$i] = new Cours();
            $cours[$i]->setDateCours($faker->dateTimeBetween('-1 week', '+1 month'));
            $cours[$i]->setDateLimiteInscription($faker->dateTimeBetween('-3 week', '-1 week'));
            $cours[$i]->setDuree($faker->numberBetween(30, 120));
            $cours[$i]->setNbInscriptionMax($faker->numberBetween(3, 10));
            $cours[$i]->setDescription("blabla");
            $cours[$i]->setTarif(20);
            $cours[$i]->setTypeCours($this->getReference(CoursEnum::CIRCUIT_TRAINING->getValue()));
            $manager->persist($cours[$i]);
        }
        $manager->flush();*/

    }
}
