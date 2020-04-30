<?php

namespace App\Service\Emailer;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Emailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmailForgotten($adress)
    {
        $uniqId = uniqid();

        $email = (new Email())
            ->from('ghastine@gmail.com')
            ->to($adress)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('SnowTricks : mot de passe oubié')
            ->text('Cliquez sur le lien pour redéfinir votre mot de pass : https://127.0.0.1:8000/reset_password?pass=');

        $this->mailer->send($email);
    }
}