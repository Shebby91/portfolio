<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\File;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WebPWriter;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Logger\Logger;
use Psr\Log\LoggerInterface;
use App\Service\MailerService;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthController extends BaseController
{
    #[Route('/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();
        if ($user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_admin');
            }
            return $this->redirectToRoute('app_user');
        }

        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'title' => 'Sign in',
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, Mailerservice $mailer, TranslatorInterface $translator, Logger $logger): Response
    {
        $user = new User();
        $user->setRegisteredSince(new \DateTime());
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        $logger->debug('Debug log entry');
        $logger->info('Info log entry');

        if ($form->isSubmitted() && $form->isValid()) {

            if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Invalid email address.');
                return $this->redirectToRoute('app_register');
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $entityManager->persist($user);
            /*
            for ($i = 0; $i < 40; $i++) {
                $randomUser = new User();
                $randomUser->setEmail('user' . $i . '@test.com');
                $randomUser->setPlainPassword('randomPassword' . $i);
                $randomUser->setFirstName('RandomFirstName' . $i);
                $randomUser->setLastName('RandomLastName' . $i);
                $randomUser->setRegisteredSince(new \DateTime());
                $randomUser->setIsTwoFactorEnabled(true);
                $randomUser->setIsVerified(true);
                // Passwort-Hashing für den zufälligen Benutzer
                $randomUser->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                // Speichere den zufälligen Benutzer
                $entityManager->persist($randomUser);
            }*/
            $entityManager->flush();

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $mailer->sendEmail($user, $signatureComponents->getSignedUrl(), 'Confirm your email address', 'verify_email');
            $this->addFlash('success', $translator->trans('resend_verify_email.flash'));
            return $this->redirectToRoute('app_verify_resend_email');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
            'title' => 'Register',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        return $this->render('security/login.html.twig', [
                'loggedOut' => true,
            ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $user = $userRepository->find($request->query->get('id'));
        
        if (!($user instanceof User)) {
            throw new \Exception('Unexpected user type');
        }
        
        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                $user->getId(),
                $user->getEmail(),

            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_login');
        }
        
        $user->setIsVerified(true);
        $em->flush();
        
        return $this->redirectToRoute('app_login', [
            'auth' => true
        ]);
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function resendVerifyEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, AuthenticationUtils $authenticationUtils, Mailerservice $mailer, TranslatorInterface $translator)
    {
        //TODO was anderes überlegen, nach register keine email senden   
        $lastUsername = $authenticationUtils->getLastUsername();
  
        if ($request->isMethod('POST') && filter_var($lastUsername, FILTER_VALIDATE_EMAIL)) {
           
            $user = $userRepository->findOneBy(['email' => $authenticationUtils->getLastUsername()]);

            if (!($user instanceof User)) {
                throw new \Exception('Unexpected user type');
            }

            if (!$user->getEmail()) {
                throw new \Exception('Email address is not set for the user.');
            }

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );
         
            $mailer->sendEmail($user, $signatureComponents->getSignedUrl(), 'Confirm your email address', 'verify_email');
         
            $this->addFlash('success', $translator->trans('resend_verify_email.flash'));

            return $this->redirectToRoute('app_verify_resend_email');

        }
      
        return $this->render('security/resend_verify_email.html.twig', [
            'title' => 'Verify Email'
        ]);
    }

    #[Route('/verify/request', name: 'app_request_reset_password')]
    public function resetPassword(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, AuthenticationUtils $authenticationUtils, Mailerservice $mailer)
    {
        if ($request->isMethod('POST')) {
            if (filter_var($request->request->get('email'), FILTER_VALIDATE_EMAIL)) {
                $user = $userRepository->findOneBy(['email' => $request->request->get('email'),]);
                
                if (!($user instanceof User)) {
                    throw new \Exception('Unexpected user type');
                }
    
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_password',
                    $user->getId(),
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );
                
                $this->addFlash('success', 'You have received an email to reset your password. Please click the link in the email to reset your password.');
                $mailer->sendEmail($user, $signatureComponents->getSignedUrl(), 'Reset password', 'reset_password');
                return $this->redirectToRoute('app_request_reset_password_success');
            }

            throw new \Exception('Please enter a valid emailaddress.');
        }
        
        return $this->render('security/request_resend_password.html.twig', [
            'title' => 'Reqeust reset password',
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/request/success', name: 'app_request_reset_password_success')]
    public function resetPasswordRequestSuccess():Response
    {
        return $this->render('security/resend_verify_password.html.twig', [
            'title' => 'Reqeust reset password',
        ]);
    }

    #[Route('/verify/reset', name: 'app_verify_password')]
    public function verifyUserNewPassword(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository, EntityManagerInterface $em, AuthenticationUtils $authenticationUtils)
    {
        $user = $userRepository->find($request->query->get('id'));
        
        if(!$user){
            throw $this->createNotFoundException();
        }
        
        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                $user->getId(),
                $user->getEmail(),

            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_login');
        }
        

        $form = $this->createForm(ResetPasswordFormType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $em->flush();

            $this->addFlash('success', 'Password successfully resetted. Try to login again.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'title' => 'Reset Password',
            'resetPasswordForm' => $form,
        ]);
    }

    #[Route('/enable/2fa', name: 'app_enable_2fa')]
    public function enable2fa(Request $request, UserRepository $userRepository, GoogleAuthenticatorInterface $googleAuthenticator, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if(!$user->isGoogleAuthenticatorEnabled()){

            if (!($user instanceof TwoFactorInterface)) {
                throw new NotFoundHttpException('Cannot display QR code');
            }

            if ($request->isMethod('POST')) {
                if($googleAuthenticator->checkCode($user, $request->request->get('code'))){
                    $user->setIsTwoFactorEnabled(true);
                    $em->flush();
    
                    if (in_array('ROLE_ADMIN',$user->getRoles())) {
                        return $this->redirectToRoute('app_admin');
                    }
    
                    return $this->redirectToRoute('app_user');
                }
                $this->addFlash('error', 'Code expired. Please try again.');
                return $this->redirectToRoute('app_enable_2fa');
            };

            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);

            $em->flush();

            return $this->render('security/enable_two_factor.html.twig', [
                'qr' => $this->displayQrCode($googleAuthenticator->getQRContent($user)),
                'checkPathUrl' => '2fa_check',
                'title' => 'Setup Authenticator',
            ]);
        }

        return $this->render('security/enable_two_factor.html.twig', [
            'title' => 'Enable 2FA',
            'resetPasswordForm' => '$form',
        ]);
    }

    private function displayQrCode(string $qrCodeContent)
    {
        $result = Builder::create()
            ->writer(new WebPWriter())
            ->writerOptions(['quality' => 100])
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(0)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            //->logoPath($this->getParameter('kernel.project_dir').'/assets/images/symfony.jpg')
            //->logoResizeToWidth(20)
            //->logoPunchoutBackground(true)
            //->labelText('This is the label')
            //->labelFont(new NotoSans(20))
            //->labelAlignment(LabelAlignment::Center)
            ->validateResult(true)
            ->build();

        return $result->getDataUri();;
    }
}
