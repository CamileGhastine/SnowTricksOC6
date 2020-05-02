<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $username = 'camile';
        $password = $this->passwordEncoder->encodePassword($user, $username);

        $user->setUsername($username)
            ->setEmail($username.'@'.$username.'.fr')
            ->setPassword($password)
            ->setAvatar('images/users/camile.jpg')
            ->setRole('ROLE_ADMIN')
            ;

        $this->addReference('user0', $user);

        $manager->persist($user);

        $manager->flush();
    }
}
