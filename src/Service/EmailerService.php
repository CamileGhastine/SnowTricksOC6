<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmailForgotten(User $user)
    {
        $email = (new Email())
            ->from('ghastine@gmail.com')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('SnowTricks : mot de passe oubié')
            ->text('Cliquez sur le lien pour redéfinir votre mot de pass : https://127.0.0.1:8000/reset_password?email='.$user->getEmail().'&token='.$user->getToken());

        $this->mailer->send($email);
    }
}
