<?php

namespace App\Controller;

use App\EmploymentDispute\Event\FormPreparedEvent;
use App\EmploymentDispute\TaskList\EmploymentDisputeCreator;
use App\EmploymentDispute\Tasks\Data\ConfirmationCodeResendTaskData;
use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\EmploymentDispute\Wizard\Event\StepCompletedEvent;
use App\EmploymentDispute\Wizard\WizardCreator;
use App\Services\QuestionMetricLogger;
use App\Services\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wizard')]
class WizardController extends AbstractController
{
    public function __construct(
        private WizardCreator $wizard,
        private EmploymentDisputeCreator $employmentDisputeCreator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('/start', name: 'app_wizard_start', methods: ['GET'])]
    public function start(): RedirectResponse
    {
        $this->wizard->startNewForm();

        return $this->redirectToRoute('app_wizard');
    }

    #[Route('/form', name: 'app_wizard', methods: ['GET', 'POST'])]
    public function renderStep(Request $request, SessionHelper $session, QuestionMetricLogger $questionMetricLogger): Response|RedirectResponse
    {
        // The browser back button would make the completed wizard (step: 999999)
        // break when switching steps back and forth. We redirect them to the home page to avoid this.
        if ($this->wizard->isCompleted() || $this->wizard->proceedToStart()) {
            return $this->redirectToRoute('app_index');
        }

        $formBuilder = $this->wizard->getFormBuilder();
        $formBuilder->setAction($this->generateUrl('app_wizard'));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        $this->wizard->clearConfirmationData();

        if ($form->isSubmitted() && $form->isValid()) {
            $questionMetricLogger->logWizardMetric($this->wizard->getCurrentTask()->getId(), $request->request->all('form'));
            $formData = $form->getData();
            assert($formData instanceof TaskDataInterface);
            $this->wizard->saveTaskData($formData);
            $this->eventDispatcher->dispatch(new StepCompletedEvent($formData));
            $this->wizard->proceedNextStep();

            // Handle completed wizard flow.
            if ($this->wizard->isCompleted()) {
                $disputeForm = $this->employmentDisputeCreator->createFromWizard($this->wizard->getData(), $this->wizard->getDataPersister());
                $session->setCurrentForm($disputeForm);

                if (!$disputeForm->getMemorableWord()) {
                    return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId()]);
                }

                $this->eventDispatcher->dispatch(new FormPreparedEvent($formData, $disputeForm->getId()));

                return $this->redirectToRoute('save_and_return_code', ['id' => $disputeForm->getId()]);
            }

            return $this->redirectToRoute('app_wizard');
        }

        return $this->render($this->wizard->getTemplate(), [
            'backUrl' => $this->generateUrl('app_wizard_back'),
            'title' => $this->wizard->getTitle(),
            'form' => $form->createView(),
            'data' => $this->wizard->getDataPersister(),
            'options' => $this->wizard->getTaskOptions(),
            'templateOptions' => $this->wizard->getTemplateOptions(),
        ]);
    }

    #[Route('/back', name: 'app_wizard_back', methods: ['GET'])]
    public function back(): Response
    {
        $this->wizard->proceedPreviousStep();

        return $this->redirectToRoute('app_wizard');
    }

    #[Route('/code-resend/{type}', name: 'app_wizard_code_resend', methods: ['GET'])]
    public function resend(string $type): Response
    {
        return $this->render('app/task-list/code-resend.html.twig', [
            'backUrl' => $this->generateUrl('app_wizard'),
            'title' => 'Request a new code',
            'type' => $type,
        ]);
    }

    #[Route('/re-send', name: 'app_wizard_verification_resend', methods: ['GET'])]
    public function reSendEmailVerification(): RedirectResponse
    {
        $formData = new ConfirmationCodeResendTaskData();
        $this->eventDispatcher->dispatch(new StepCompletedEvent($formData));

        return $this->redirectToRoute('app_wizard');
    }
}
