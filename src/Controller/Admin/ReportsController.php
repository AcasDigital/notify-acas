<?php

namespace App\Controller\Admin;

use App\EmploymentDispute\Tasks\TaskLocator;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\QuestionMetric;
use App\Entity\UsageReport;
use App\Translation\TranslatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/idr-bst/admin')]
class ReportsController extends AbstractController
{
    #[Route('/reports', name: 'admin.reports')]
    public function reports(): Response
    {
        return $this->render('admin/reports.html.twig');
    }

    #[Route('/reports/usage', name: 'admin.reports.usage')]
    public function usageReport(EntityManagerInterface $entityManager): Response
    {
        // Depending on the data retention policy this query could become very large.
        // We must perform this in the most memory effecient way possible using doctrine
        // iterables and streamed responses to not slowly increase memory usage over time.
        $iterable = $entityManager->createQuery('select u from App\Entity\UsageReport as u')->toIterable();
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($iterable, $entityManager) {
            echo sprintf("created,submitted,time_to_submission,journey_type,representative,contact_method,has_email,has_memorable_word,number_of_returns,number_of_rfd\n");
            foreach ($iterable as $row) {
                assert($row instanceof UsageReport);
                echo sprintf("%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n", $row->getCreated()->format('Y-m-d\TH:i:s'), $row->getSubmitted()->format('Y-m-d\TH:i:s'), $row->getTimeToSubmission(), $row->getJourneyType(), $row->getRepresentative(), $row->getContactMethod(), $row->getEmailProvided(), $row->getMemorableWordProvided(), $row->getNumberOfReturns(), $row->getReasonForDisputeCount());
                $entityManager->detach($row);
            }
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="reports_usage.csv"');

        return $response->send();
    }

    #[Route('/reports/questions', name: 'admin.reports.question')]
    public function questionReport(EntityManagerInterface $entityManager, TaskLocator $taskLocator, TranslatorService $translator): Response
    {
        $iterable = $entityManager->createQuery('select u from App\Entity\QuestionMetric as u')->toIterable();
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($iterable, $entityManager, $taskLocator, $translator) {
            echo sprintf("machine_name,label,filled,not_filled\n");
            foreach ($iterable as $row) {
                assert($row instanceof QuestionMetric);
                $task = $taskLocator->getTaskById($row->getQuestion());
                $options = (new TaskOptions())
                    ->setFlow(TaskOptions::FLOW_CERTIFICATE)
                    ->setRepresentative(TaskOptions::REPRESENTATIVE_MYSELF)
                    ->setContactMethods(TaskOptions::CONTACT_METHOD_EMAIL);
                $task->initialize($translator, $options);
                $label = $task?->getLabel();
                echo sprintf("%s,%s,%s,%s\n", $row->getQuestion(), $label, $row->getFilled(), $row->getNotFilled());
                $entityManager->detach($row);
            }
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="reports_questions.csv"');

        return $response->send();
    }
}
