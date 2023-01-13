<?php

namespace App\Controller\Admin;

use App\HealthCheck\HealthChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    #[Route('/idr-bst/admin/healthcheck', name: 'healthcheck')]
    public function login(HealthChecker $healthchecker): Response
    {
        if (extension_loaded('newrelic')) {
            newrelic_ignore_transaction();
        }
        $healthchecks = $healthchecker->runChecks();

        return $this->render('admin/healthchecks.html.twig', ['healthchecks' => $healthchecks]);
    }

    #[Route('/healthz', name: 'healthcheck_api')]
    public function healthcheck(HealthChecker $healthchecker): Response
    {
        if (extension_loaded('newrelic')) {
            newrelic_ignore_transaction();
        }
        $healthchecks = $healthchecker->runChecks();
        $responseData = [];
        $status = 200;
        foreach ($healthchecks as $check) {
            $responseData[$check->getLabel()] = [
              'status' => $check->isHealthy() ? 'healthy' : 'unhealthy',
            ];
            // If any check is not healthy, send back an error response code.
            if (!$check->isHealthy()) {
                $responseData[$check->getLabel()]['errors'] = $check->getErrors();
                $status = 500;
            }
        }

        return new JsonResponse($responseData, $status);
    }

    #[Route('/settings/clearFailed', name: 'admin_clear_failed', methods: ['GET', 'POST'])]
    public function clearFailed(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('queueDelete', (string) $request->request->get('_token'))) {
            $this->addFlash('notice', 'CSRF Token mismatch - try refreshing the page and submitting the form again.');

            return $this->redirectToRoute('healthcheck');
        }

        $connection = $em->getConnection();
        $sql = '
            DELETE FROM messenger_messages WHERE queue_name="failed";
        ';

        $statement = $connection->prepare($sql);
        $statement->executeQuery();
        $this->addFlash('notice', 'Cleared failed queue');

        return $this->redirectToRoute('healthcheck');
    }
}
