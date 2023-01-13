<?php

namespace App\Controller;

use App\EmploymentDispute\Event\SaveAndReturnEmailEvent;
use App\EmploymentDispute\Tasks\Data\OptionalEmailTaskData;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\EmploymentDispute\Validator as A12Assert;
use App\Entity\EmploymentDispute;
use App\Repository\EmploymentDisputeRepository;
use App\Services\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/return')]
class ReturnController extends AbstractController
{
    /**
     * @var array<string, string|array<string, string>>
     */
    private array $defaultOptions;

    public function __construct(
        private EmploymentDisputeRepository $employmentDisputeRepository,
    ) {
        $this->defaultOptions = [
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ];
    }

    #[Route('/start', name: 'return_from_start', methods: ['GET', 'POST'])]
    public function returnFromStart(
        Request $request,
        EmploymentDisputeRepository $employmentDisputeRepository,
        SessionHelper $session,
        ValidatorInterface $validator): Response
    {
        $form = $this->createFormBuilder(null, $this->defaultOptions)
            ->add('SRNnumber', TextType::class, [
                'label' => 'Enter you save and return code',
                'help' => 'This was emailed to you (if you entered your email address)',
                'constraints' => [
                    new NotBlank(message: 'Enter your save and return code'),
                ],
            ])
            ->add('memorableWord', TextType::class, [
                'label' => 'Enter your memorable word',
                'constraints' => [
                    new NotBlank(message: 'Enter a memorable word that is 6 characters or more'),
                    new Length(min: 6, minMessage: 'Enter a memorable word that is 6 characters or more'),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        $errors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            assert(is_array($formData));

            $constraint = new A12Assert\DisputeFormToReturnToExists();
            $errors = $validator->validate($formData, $constraint);

            if (!$errors->count()) {
                $returnNumber = $formData['SRNnumber'];
                $disputeForm = $employmentDisputeRepository->find($returnNumber);

                if ($disputeForm instanceof EmploymentDispute) {
                    $session->setCurrentForm($disputeForm);
                    $disputeForm->incrementNumberOfReturns();
                    $this->employmentDisputeRepository->add($disputeForm);

                    return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId()]);
                }
            }
        }

        return $this->render('/app/pages/return-from-start.html.twig', [
            'backUrl' => $this->generateUrl('app_index'),
            'title' => 'Return to your form',
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}/code', name: 'save_and_return_code', methods: ['GET', 'POST'])]
    public function renderReturnCode(Request $request, EmploymentDispute $disputeForm, EventDispatcherInterface $eventDispatcher): Response
    {
        // No access to the page if there is no memorable word set already, send them to the start page.
        if (null === $disputeForm->getMemorableWord()) {
            return $this->redirectToRoute('app_index');
        }

        $title = 'Save and return';
        $emailMethods = [TaskOptions::CONTACT_METHOD_EMAIL, TaskOptions::CONTACT_METHOD_PHONE_EMAIL];

        // set condition to not display email
        // redirect to the task list directly
        if (in_array($disputeForm->getContactMethod(), $emailMethods) || (TaskOptions::FLOW_CERTIFICATE === $disputeForm->getType() && TaskOptions::CONTACT_METHOD_POST === $disputeForm->getContactMethod())) {
            return $this->render('/app/pages/save-and-return.html.twig', [
                'title' => $title,
                'returnNumber' => $disputeForm->getId(),
                'form' => null,
                'data' => $disputeForm,
            ]);
        }

        $this->defaultOptions['data_class'] = OptionalEmailTaskData::class;
        $form = $this->createFormBuilder(null, $this->defaultOptions)
        ->add('data', TextType::class, [
            'label' => 'Enter your email address to receive a copy of your save and return code (optional)',
            'constraints' => [
                new Email(),
            ],
        ])
        ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            assert($formData instanceof OptionalEmailTaskData);

            $eventDispatcher->dispatch(new SaveAndReturnEmailEvent($formData, $disputeForm->getId()));

            return $this->redirectToRoute('app_task_list', ['id' => $disputeForm->getId()]);
        }

        return $this->render('/app/pages/save-and-return.html.twig', [
            'title' => $title,
            'returnNumber' => $disputeForm->getId(),
            'form' => $form->createView(),
            'data' => $disputeForm,
        ]);
    }
}
