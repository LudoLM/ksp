<?php

namespace App\DataFixtures;

use App\Entity\Cours;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CoursFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $sortie1 = Array();
        for ($i = 1; $i < 30; $i++) {
            $list = ["Pilates débutant", 'Stretching', 'Gym Activ Ball', 'Circuit Training', 'Pilates Intermédiaires', 'Gym douce'];
            $cours[$i] = new Cours();
            $cours[$i]->setNomCours(array_rand(array_flip($list), 1));
            $cours[$i]->setDateCours($faker->dateTimeBetween('-1 week', '+1 month'));
            $cours[$i]->setDateLimiteInscription($faker->dateTimeBetween('-3 week', '-1 week'));
            $cours[$i]->setDuree($faker->numberBetween(30, 120));
            $cours[$i]->setNbInscriptionMax($faker->numberBetween(3, 10));
            $cours[$i]->setDescription("blabla");
            $cours[$i]->setTarif(20);
            $manager->persist($cours[$i]);
        }
        $manager->flush();

    }
}
