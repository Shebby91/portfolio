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

    
    
    public function sendEmail()
    {
        $email = (new TemplatedEmail())
        ->from(new Address('ryan@example.com'))
        ->to('sgrauthoff@gmail.com') // Deine Gmail-Adresse
        ->subject('Test Email')
        ->text('This is a test email.')
        ->htmlTemplate('/admin/test.html.twig');

        $this->mailer->send($email);
    }
}