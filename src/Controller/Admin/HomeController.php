<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/idr-bst/admin')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'admin_index', methods: ['GET'])]
    public function adminIndex(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('healthcheck');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}
