<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DisputeFormController extends AbstractController
{
    #[Route('/start', name: 'app_form_start')]
    public function start(): Response
    {
        return $this->render('app/start.html.twig');
    }
}
