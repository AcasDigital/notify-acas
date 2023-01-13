<?php

namespace App\Controller;

use App\EmploymentDispute\Submission\CaseNumberHelper;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\TaskList\TaskListCreator;
use App\Entity\CaseNumber;
use App\Entity\EmploymentDispute;
use App\Message\SubmissionMessage;
use App\Repository\EmploymentDisputeRepository;
use App\Services\QuestionMetricLogger;
use App\Services\SessionHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class SubmissionController extends AbstractController
{
    public function __construct(
        private TaskListCreator $taskList,
        private MessageBusInterface $bus,
        private CaseNumberHelper $caseNumberHelper,
        private SessionHelper $session,
        ) {
    }

    #[Route('/task-list/{id}/review', name: 'app_review', methods: ['GET', 'POST'])]
    public function reviewStep(
            Request $request,
            EmploymentDispute $disputeForm,
            string $id,
            EmploymentDisputeRepository $employmentDisputeRepository,
            EmploymentDisputeDataPersister $storage,
            ManagerRegistry $doctrine,
            QuestionMetricLogger $questionMetricLogger,
    ): Response {
        $this->session->denyUnlessAccessToForm($disputeForm);
        if (EmploymentDispute::STATUS_DRAFT !== $disputeForm->getStatus()) {
            return $this->redirectToRoute('app_task_confirmation', ['id' => $disputeForm->getId()]);
        }

        $this->taskList->initialize($disputeForm);
        $sections = $this->taskList->getSections();
        $review = $this->taskList->getAllTaskData(false);
        $valid = $this->taskList->getTaskValidation();

        // If there is any invalid mandatory task (e.g. Organisation type change with empty tasks) on the review page,
        // redirect the user back to the task list.
        if (!$valid) {
            return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId()]);
        }

        $form = $this->createFormBuilder()->add('submit', SubmitType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $valid) {
            $disputeForm->setStatus(EmploymentDispute::STATUS_QUEUED);
            $disputeForm->setSubmissionDateTime(new \DateTime());

            $formaData = $storage->getFormData();
            $respondentSectionData = $formaData->getRespondentSectionData();
            $caseNumbers = $this->caseNumberHelper->createCaseNumbers($respondentSectionData);

            foreach ($caseNumbers as $caseNumber) {
                $disputeForm->addCaseNumber($caseNumber);
                $doctrine->getManager()->persist($caseNumber);
            }
            $doctrine->getManager()->flush();

            $employmentDisputeRepository->add($disputeForm);

            $this->bus->dispatch(new SubmissionMessage($disputeForm->getId()));

            $questionMetricLogger->logTaskListMetrics($this->taskList);

            return $this->redirectToRoute('app_task_confirmation', ['id' => $disputeForm->getId()]);
        }

        return $this->render('app/task-list/review.html.twig', [
            'disputeForm' => $disputeForm,
            'sections' => $sections,
            'review' => $review,
            'valid' => !$valid,
            'submitForm' => $form->createView(),
            'backUrl' => $this->generateUrl('app_task_list', ['id' => $id]),
            'hideRepresentativeSection' => $this->taskList->shouldHideSectionOnReview('representative'),
            'storage' => $storage,
        ]);
    }

    #[Route('/task-list/{id}/confirmation', name: 'app_task_confirmation', methods: ['GET'])]
    public function confirmation(EmploymentDispute $disputeForm, EmploymentDisputeDataPersister $storage): Response
    {
        $this->session->denyUnlessAccessToForm($disputeForm);
        $this->taskList->initialize($disputeForm);

        $firstCaseNumber = $disputeForm->getCaseNumbers()?->first();

        return $this->render('app/pages/confirmation.html.twig',
            [
                'caseNumber' => $firstCaseNumber instanceof CaseNumber ? $firstCaseNumber->getCaseRefNumber() : null, // lead case number
                'flowType' => $disputeForm->getType(),
                'contactMethod' => $disputeForm->getContactMethod(),
                'disputeForm' => $disputeForm,
                'respondentSectionData' => $storage->getFormData()->getRespondentSectionData(),
            ]
        );
    }
}
