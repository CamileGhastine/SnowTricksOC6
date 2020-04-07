<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');


        for($i=1; $i<=3; $i++)
        {
            $user = new User();

            $username = $faker->firstName;
            $password = $this->passwordEncoder->encodePassword($user, $username);

            $user->setUsername($username)
                ->setEmail($username.'@'.$username.'.fr')
                ->setPassword($password)
                ->setAvatar('images/users/nobody.jpg')
                ->setRegisteredAt(new \DateTime)
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}
