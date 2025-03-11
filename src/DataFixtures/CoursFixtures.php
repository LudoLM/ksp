<?php

namespace App\DataFixtures;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Entity\TypeCours;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CoursFixtures extends Fixture
{

    public function __construct(

    ) {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $typeCoursList = $manager->getRepository(TypeCours::class)->findAll();
        $statusCours = $manager->getRepository(StatusCours::class)->findOneBy(['id' => 4]);

        for ($i = 1; $i < 100; $i++) {
            $cours[$i] = new Cours();
            $date = $faker->dateTimeBetween('+1 day', '+3 month');

            // Générer une heure entre 8h et 20h
            $hour = $faker->numberBetween(8, 20);
            $date->setTime($hour, 0, 0); // Fixer à l'heure entière pour commencer

            // Récupérer les minutes
            $minutes = (int)$date->format('i');

            // Calculer l'incrément pour arrondir au prochain intervalle (par exemple, 15 minutes)
            $increment = 15 - ($minutes % 15);
            $date->modify("+$increment minutes");

            // Assurer que les secondes sont à 0
            $date->setTime((int)$date->format('H'), (int)$date->format('i'), 0);

            // Affecter la date au cours
            $cours[$i]->setDateCours($date);

            $cours[$i]->setDuree($faker->numberBetween(5, 10) * 10);
            $cours[$i]->setNbInscriptionMax($faker->numberBetween(6, 12));
            $cours[$i]->setSpecialNote("Pensez à prendre une bouteille d'eau");

            $randomTypeCours = $typeCoursList[array_rand($typeCoursList)];
            $cours[$i]->setTypeCours($randomTypeCours);

            $cours[$i]->setStatusCours($statusCours);

            $manager->persist($cours[$i]);
        }
        $manager->flush();

    }
}
