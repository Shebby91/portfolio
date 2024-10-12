<?php
namespace App\EventSubscriber;
use App\Entity\User;
use App\Security\AccountNotVerifiedAuthenticationException;
use App\Security\TwoFactorNotEnabledException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Logger\Logger;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private Security $security;
    private Logger $logger;

    public function __construct(RouterInterface $router, Security $security, Logger $logger)
    {
        $this->router = $router;
        $this->security = $security;
        $this->logger = $logger;
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        /** @var Passport $passport */
        $passport = $event->getPassport();

        /** @var User $user */
        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!$user->getIsVerified()) {
            throw new AccountNotVerifiedAuthenticationException();
        }
    }

    public function onLoginFailure(LoginFailureEvent $event)
    {
        if (!$event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            return;
        }

        $response = new RedirectResponse(
            $this->router->generate('app_verify_resend_email')
        );

        $event->setResponse($response);
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        /** @var Passport $passport */
        $passport = $event->getPassport();

        /** @var User $user */
        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!$user->isGoogleAuthenticatorEnabled()) {
            // Umleitung zur 2FA-Aktivierungsseite, falls 2FA nicht aktiviert ist
            $response = new RedirectResponse(
                $this->router->generate('app_enable_2fa')
            );
            $event->setResponse($response);
            return;
        }

        // Standard-Umleitung für normale Benutzer und Admins
        $response = new RedirectResponse(
            in_array('ROLE_ADMIN', $user->getRoles())
                ? $this->router->generate('app_admin')
                : $this->router->generate('app_user')
        );

        if ($event->getRequest()->getRequestUri() == '/') {
            $this->logger->info('user_login_success_password', ['user' => $user->getEmail()]);
        }

        if ($event->getRequest()->getRequestUri() == '/2fa_check') {
            $this->logger->info('user_login_success_twoFactor', ['user' => $user->getEmail()]);
        }

        $event->setResponse($response);
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $user = $this->security->getUser();
        // Wenn der Benutzer eingeloggt ist und 2FA nicht aktiviert ist
        if ($user instanceof User && !$user->isGoogleAuthenticatorEnabled()) {
            // Erlaube nur den Zugriff auf die 2FA-Aktivierungsseite und Logout
            $currentRoute = $request->attributes->get('_route');
            if ($currentRoute !== 'app_enable_2fa' && $currentRoute !== 'app_logout' && $currentRoute !== 'app_login') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_enable_2fa')));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}