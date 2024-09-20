<?php
namespace Service\MailsService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailController extends AbstractController
{
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('sgrauthoff@gmail.com')
            ->to('sgrauthoff@gmail.com')
            ->subject('Test Email')
            ->text('This is a test email.')
            ->html('<p>This is a test email from Symfony Mailer.</p>');

        $mailer->send($email);

        return true;
    }
}