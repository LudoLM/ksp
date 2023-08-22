<?php

namespace App\DataFixtures;

use App\Entity\Cours;
use App\Enum\CoursEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CoursFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 1; $i < 30; $i++) {
            $cours[$i] = new Cours();
            $cours[$i]->setDateCours($faker->dateTimeBetween('-1 week', '+1 month'));
            $cours[$i]->setDateLimiteInscription($faker->dateTimeBetween('-3 week', '-1 week'));
            $cours[$i]->setDuree($faker->numberBetween(30, 120));
            $cours[$i]->setNbInscriptionMax($faker->numberBetween(3, 10));
            $cours[$i]->setDescription("blabla");
            $cours[$i]->setTarif(20);
            $cours[$i]->setTypeCours($this->getReference(TypeCoursFixtures::COURS[$faker->numberBetween(0, count(TypeCoursFixtures::COURS) -1)]));
            $manager->persist($cours[$i]);
        }
        $manager->flush();

    }

    public function getDependencies()
    {
       return [TypeCoursFixtures::class];
    }
}
