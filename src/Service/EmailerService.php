<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class EmailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param User $user
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmailForgotten(User $user)
    {
        $token = password_hash('forgotten_password'.$user->getId().$user->getEmail(), PASSWORD_DEFAULT);

        $email = (new Email())
            ->from('ghastine@gmail.com')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('SnowTricks : mot de passe oubié')
            ->text('Cliquez sur le lien pour redéfinir votre mot de pass : https://127.0.0.1:8000/reset_password?email='.$user->getEmail().'&token='.$token);

        $this->mailer->send($email);
    }
}