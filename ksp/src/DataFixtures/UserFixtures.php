<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher){

    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();
        $user->setnom("Cosqueric");
        $user->setprenom("Servane");
        $user->setEmail("servaneC@free.fr");
        $user->setadresse("10 rue de Rennes");
        $user->setCodePostal("35310");
        $user->setCommune("Mordelles");
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setPassword($this->hasher->hashPassword($user, 'admin'));
        $user->setTelephone("0616666666");
        $manager->persist($user);
        $manager->flush();

        $faker = Faker\Factory::create('fr_FR');
        $faker = Factory::create('fr_FR');
        $user = Array();
        for ($i = 1; $i < 20; $i++) {
            $user[$i] = new USer();
            $user[$i]->setNom($faker->lastName);
            $user[$i]->setPrenom($faker->firstName);
            $user[$i]->setPassword($this->hasher->hashPassword($user[$i], "user"));
            $user[$i]->setAdresse($faker->address);
            $user[$i]->setCodePostal($faker->postcode);
            $user[$i]->setCommune($faker->city);
            $user[$i]->setEmail($faker->unique()->email);
            $user[$i]->setRoles(["ROLE_USER"]);
            $user[$i]->setTelephone($faker->numberBetween(100000000,999999999) );

            $manager->persist($user[$i]);
        }

        $manager->flush();
    }
}
