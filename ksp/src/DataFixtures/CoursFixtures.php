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
        for ($i = 1; $i < 300; $i++) {
            $cours[$i] = new Cours();
            $cours[$i]->setDateCours($faker->dateTimeBetween('-1 week', '+3 month'));
            $cours[$i]->setDuree($faker->numberBetween(30, 120));
            $cours[$i]->setNbInscriptionMax($faker->numberBetween(3, 10));
            $cours[$i]->setSpecialNote("blabla");
            $cours[$i]->setTypeCours($this->getReference("typeCours" . $faker->numberBetween(0, 5)));
            $cours[$i]->setStatusCours($this->getReference("statusCours" . $faker->numberBetween(0, 6)));
            $manager->persist($cours[$i]);
        }
        $manager->flush();

    }

    public function getDependencies(): array
    {
       return [TypeCoursFixtures::class, StatusCoursFixtures::class];
    }
}
