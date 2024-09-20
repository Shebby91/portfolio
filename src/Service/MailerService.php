<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    
    
    public function sendEmail(User $user, $link, $subject, $template)
    {
        $email = (new TemplatedEmail())
        ->from(new Address('portfolio@app.com'))
        ->to($user->getEmail()) // Deine Gmail-Adresse
        ->subject($subject)
        ->htmlTemplate('/email/'.$template.'.html.twig')
        ->context([
            'user' => $user,
            'link' => $link,
        ]);
        $this->mailer->send($email);
    }
}