<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserFixtures extends Fixture
{
    const NB_USERS = 3;
    private $passwordEncoder;
    private $token;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGeneratorInterface $token)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->token = $token;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::NB_USERS; ++$i) {
            $user = new User();

            $username = $faker->firstName;
            $password = $this->passwordEncoder->encodePassword($user, $username);

            $user->setUsername($username)
                ->setEmail($username.'@'.$username.'.fr')
                ->setPassword($password)
                ->setAvatar('images/users/nobody.jpg')
                ->setValidate(true)
                ->setRegisteredAt($faker->dateTimebetween('-7 days'))
                ->setToken($this->token->generateToken())
            ;

            $this->addReference('user'.$i, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
