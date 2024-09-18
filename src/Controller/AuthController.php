<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class AuthController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'title' => 'Sign in',
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            //TODO: send this as an email
            $this->addFlash('success', 'Confirm your email at: ' . $signatureComponents->getSignedUrl());
            return $this->redirectToRoute('app_login');
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
        


        if (!$user instanceof User) {
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
        
        $this->addFlash('success', 'Account verfied! You can now log in.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function resendVerifyEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, AuthenticationUtils $authenticationUtils)
    {
        if ($request->isMethod('POST') && filter_var($authenticationUtils->getLastUsername(), FILTER_VALIDATE_EMAIL)) {
            $user = $userRepository->findOneBy(['email' => $authenticationUtils->getLastUsername()]);

            if (!$user instanceof User) {
                throw new \Exception('Unexpected user type');
            }

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            //TODO: send this as an email
            $this->addFlash('success', 'Confirm your email at: ' . $signatureComponents->getSignedUrl());
            return $this->redirectToRoute('app_login');

        }
        
        return $this->render('security/resend_verify_email.html.twig', [
            'title' => 'Resend Email'
        ]);
    }

    #[Route('/verify/request', name: 'app_request_reset_password')]
    public function resetPassword(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, AuthenticationUtils $authenticationUtils)
    {
        if ($request->isMethod('POST')) {
            if (filter_var($authenticationUtils->getLastUsername(), FILTER_VALIDATE_EMAIL)) {
                $user = $userRepository->findOneBy(['email' => $authenticationUtils->getLastUsername()]);
                
                if (!$user instanceof User) {
                    throw new \Exception('Unexpected user type');
                }
    
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_password',
                    $user->getId(),
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );
    
                //TODO: send this as an email
                $this->addFlash('success', 'Reset your password by clicking this link: ' . $signatureComponents->getSignedUrl());
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


            //TODO: send this as an email
            $this->addFlash('success', 'Password successfully resetted. Try to login again.');
            return $this->redirectToRoute('app_login');
        }

        //$user->setIsVerified(true);
        //$em->flush();
        
        //$this->addFlash('success', 'Account verfied! You can now log in.');
        //return $this->redirectToRoute('app_login');
        return $this->render('security/reset_password.html.twig', [
            'title' => 'Reset Password',
            'resetPasswordForm' => $form,
        ]);
    }
}
