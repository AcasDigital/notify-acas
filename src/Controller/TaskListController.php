<?php

namespace App\Controller;

use App\EmploymentDispute\Submission\Search\SubmissionSearchIndexer;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\TaskList\TaskListCreator;
use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\EmploymentDispute;
use App\Services\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task-list')]
class TaskListController extends AbstractController
{
    public function __construct(private TaskListCreator $taskList, private SessionHelper $session)
    {
    }

    #[Route('/{id}', name: 'app_task_list', methods: ['GET'])]
    public function renderStep(EmploymentDispute $disputeForm, EmploymentDisputeDataPersister $storage): Response
    {
        $this->session->denyUnlessAccessToForm($disputeForm);
        if (EmploymentDispute::STATUS_DRAFT !== $disputeForm->getStatus()) {
            return $this->redirectToRoute('app_task_confirmation', ['id' => $disputeForm->getId()]);
        }

        $this->taskList->initialize($disputeForm);
        $sections = $this->taskList->getSections();

        $status = $this->taskList->getAllTaskStatus();

        $title = $this->taskList->getTitle();

        // is form valid
        $valid = $this->taskList->getTaskValidation();

        return $this->render('app/task-list/task-list.html.twig', [
            'disputeForm' => $disputeForm,
            'sections' => $sections,
            'status' => $status,
            'backUrl' => $this->generateUrl('app_form_start'),
            'title' => $title,
            'valid' => $valid,
            'storage' => $storage,
        ]);
    }

    #[Route('/{id}/add/{sectionId}', name: 'app_add_section', methods: ['GET', 'POST'])]
    public function addSection(Request $request, string $sectionId, EmploymentDispute $disputeForm): Response
    {
        $this->session->denyUnlessAccessToForm($disputeForm);
        if (EmploymentDispute::STATUS_DRAFT !== $disputeForm->getStatus()) {
            return $this->redirectToRoute('app_task_confirmation', ['id' => $disputeForm->getId()]);
        }

        $this->taskList->initialize($disputeForm);
        $this->taskList->addRepeatedSection($sectionId);
        $fragment = $request->query->get('sectionAnchor');

        return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId(), '_fragment' => $fragment]);
    }

    #[Route('/{id}/remove/{sectionId}', name: 'app_remove_section', methods: ['GET', 'POST'])]
    public function removeSection(Request $request, string $sectionId, EmploymentDispute $disputeForm): Response
    {
        $this->session->denyUnlessAccessToForm($disputeForm);
        if (EmploymentDispute::STATUS_DRAFT !== $disputeForm->getStatus()) {
            return $this->redirectToRoute('app_task_confirmation', ['id' => $disputeForm->getId()]);
        }

        $this->taskList->initialize($disputeForm);
        $this->taskList->removeRepeatedSection($sectionId);
        $fragment = $request->query->get('sectionAnchor');

        return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId(), '_fragment' => $fragment]);
    }

    #[Route('/{id}/{sectionId}/{taskId}', name: 'app_render_task', methods: ['GET', 'POST'])]
    public function renderTask(Request $request, EmploymentDispute $disputeForm, string $sectionId, string $taskId, string $id, SubmissionSearchIndexer $indexer): Response
    {
        $this->session->denyUnlessAccessToForm($disputeForm);
        if (EmploymentDispute::STATUS_DRAFT !== $disputeForm->getStatus()) {
            return $this->redirectToRoute('app_task_confirmation', ['id' => $disputeForm->getId()]);
        }

        // Make verified emails uneditable. If email is included in the contact methods, it always gets verified.
        $isVerifiedClaimantEmail = ('claimant_email_optional' === $taskId && TaskOptions::REPRESENTATIVE_MYSELF === $disputeForm->getRepresenting());
        $isVerifiedRepresentativeEmail = ('representative_email_optional' === $taskId && TaskOptions::REPRESENTATIVE_OTHER === $disputeForm->getRepresenting());
        $fragment = $request->query->get('sectionAnchor');

        if ($isVerifiedClaimantEmail || $isVerifiedRepresentativeEmail) {
            return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId(), '_fragment' => $fragment]);
        }

        $source = $request->query->get('source');

        $this->taskList->initialize($disputeForm);

        $form = $this->taskList->getTaskForm($sectionId, $taskId);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            assert($data instanceof TaskDataInterface);
            $this->taskList->saveTask($sectionId, $taskId, $data);
            $indexer->indexTaskData($data, $sectionId, $disputeForm);
            $fragment = $request->query->get('sectionAnchor');

            return 'review' === $source ? $this->redirectToRoute('app_review', ['id' => $disputeForm->getId()]) : $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId(), '_fragment' => $fragment]);
        }

        return $this->render($this->taskList->getTaskTemplate($sectionId, $taskId), [
            'form' => $form->createView(),
            'disputeForm' => $disputeForm,
            'title' => $this->taskList->getTaskTitle($sectionId, $taskId),
            'backUrl' => 'review' === $source ? $this->generateUrl('app_review', ['id' => $id]) : $this->generateUrl('app_task_list', ['id' => $id]),
            'options' => $this->taskList->createTaskOptions(),
            'templateOptions' => $this->taskList->getTemplateOptions($sectionId, $taskId),
        ]);
    }
}
