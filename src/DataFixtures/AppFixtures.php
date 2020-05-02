<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $token;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGeneratorInterface $token)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->token = $token;
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
            ->setApiToken($this->token->generateToken())
        ;

        $this->addReference('user0', $user);

        $manager->persist($user);

        $manager->flush();
    }
}
