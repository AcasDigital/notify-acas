<?php

namespace App\Controller;

use App\EmploymentDispute\Event\ResetMemorableLinkEvent;
use App\EmploymentDispute\Validator as A12Assert;
use App\EmploymentDispute\Validator\MemorableWordRequirements;
use App\Entity\EmploymentDispute;
use App\Repository\EmploymentDisputeRepository;
use App\Services\SecurityHelper;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/memo')]
class MemorableWordController extends AbstractController
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $defaultOptions;

    public function __construct(
        private EmploymentDisputeRepository $employmentDisputeRepository,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
        private ValidatorInterface $validator
    ) {
        $this->defaultOptions = [
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ];
    }

    #[Route('/reset/start', name: 'reset_memorable_from_start', methods: ['GET', 'POST'], priority: 10)]
    public function resetMemorableFromStart(Request $request): Response
    {
        $form = $this->createFormBuilder(null, $this->defaultOptions)
            ->add('email', TextType::class, [
                'label' => 'Enter your email address',
                'help' => 'If you entered a valid email address when you started your form, we will email you with instructions on how to reset your memorable word. If we cannot match your email address or you did not enter a valid email address you will need to start a new application.',
                'constraints' => [
                    new NotBlank(message: 'Enter a valid email address'),
                    new Email(message: 'Enter an email address in the correct format, like name@example.com'),
                ],
            ])
            ->add('SRNnumber', TextType::class, [
                'label' => 'Save and return code',
                'help' => 'This was emailed to you (if you entered an email address).',
                'constraints' => [
                    new NotBlank(message: 'Enter your save and return code'),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        $errors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $contstrain = new A12Assert\DisputeFormToResetMemorableExists();
            $errors = $this->validator->validate($formData, $contstrain);

            if (!$errors->count() && is_array($formData)) {
                $email = $formData['email'] ?? null;
                $returnNumber = $formData['SRNnumber'] ?? null;
                $disputeForm = $this->employmentDisputeRepository->find($returnNumber);

                if ($disputeForm instanceof EmploymentDispute && $email) {
                    $this->eventDispatcher->dispatch(new ResetMemorableLinkEvent($email, $disputeForm->getId()));

                    $this->addFlash('info', 'Check your email for a reset link');

                    return $this->redirectToRoute('reset_memorable_from_start');
                }
            }
        }

        return $this->render('/app/pages/reset-memorable-from-start.html.twig', [
            'backUrl' => $this->generateUrl('app_index'),
            'title' => 'Reset memorable word',
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    #[Route('/reset/{id}', name: 'reset_memorable_word', methods: ['GET', 'POST'])]
    public function resetStep(string $id, Request $request, ManagerRegistry $doctrine, EmploymentDispute $disputeForm): Response
    {
        $token = $request->query->get('token');
        $expiryTimestamp = $request->query->getInt('timestamp');

        $now = new \DateTime();
        $nowTimestamp = $now->getTimestamp();

        $contact = $disputeForm->getVerificationContact();
        $memorableWord = $disputeForm->getMemorableWord();

        if ($nowTimestamp > $expiryTimestamp) {
            $this->logger->error("[MEMORABLE WORD] Reset link access has been denied for form id: $id. Link has expired for $contact.");

            throw new UnauthorizedHttpException('reset link authentication', 'Access denied. You do not have permission to perform this action');
        }

        assert(is_string($contact));
        assert(is_string($memorableWord));

        $hash = SecurityHelper::generateUrlAccessHash($contact, $memorableWord, $expiryTimestamp);

        if ($token !== $hash) {
            $this->logger->error("[MEMORABLE WORD] Reset link access has been denied for form id: $id. Hash check failed for $contact.");

            throw new UnauthorizedHttpException('reset link authentication', 'Access denied. You do not have permission to perform this action');
        }

        $form = $this->createFormBuilder(null, $this->defaultOptions)
            ->add('memorableWord', TextType::class, [
                'label' => 'Set your new memorable word',
                'constraints' => [
                    new NotBlank(message: 'Enter a memorable word that is 6 characters or more'),
                    new MemorableWordRequirements(),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            assert(is_array($formData));

            $memorableWord = $formData['memorableWord'] ?? null;

            $disputeForm->setMemorableWord($memorableWord);
            $disputeForm->setModified(new \DateTime());
            $doctrine->getManager()->persist($disputeForm);
            $doctrine->getManager()->flush();

            $this->addFlash('info', 'Your memorable word has been saved.');

            return $this->redirectToRoute('return_from_start');
        }

        return $this->render('/app/pages/reset-memorable-word.html.twig', [
            'title' => 'Reset memorable word',
            'form' => $form->createView(),
            'data' => $disputeForm,
            'errors' => $form->getErrors(),
        ]);
    }
}
