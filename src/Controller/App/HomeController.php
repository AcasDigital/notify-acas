<?php

namespace App\Controller\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function homeIndex(): Response
    {
        return $this->redirectToRoute('app_form_start');
    }

    #[Route('/cookies', name: 'app_cookie_policy')]
    public function cookies(): Response
    {
        return $this->render('app/pages/cookie-policy.html.twig');
    }
}
