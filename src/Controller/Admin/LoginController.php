<?php

namespace App\Controller\Admin;

use App\Security\TwoFactorAuthMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/idr-bst/admin')]
class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user (email)
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/resend-auth-code', name: 'app_resend_login_auth_code')]
    public function resendAuthCode(TwoFactorAuthMailer $twoFactorAuthMailer): RedirectResponse
    {
        $twoFactorAuthMailer->resendAuthCode();

        $this->addFlash('info', 'Authentication code re-sent.');

        return $this->redirectToRoute('2fa_login');
    }
}
