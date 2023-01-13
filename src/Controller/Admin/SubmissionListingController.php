<?php

namespace App\Controller\Admin;

use App\EmploymentDispute\Submission\PayloadGenerator;
use App\Entity\EmploymentDispute;
use App\Form\Data\SubmissionSearchData;
use App\Form\Type\SubmissionSearchType;
use App\Message\SubmissionMessage;
use App\Repository\EmploymentDisputeRepository;
use App\Services\Pagination\Pagination;
use App\Services\SessionHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/idr-bst/admin/submissions')]
class SubmissionListingController extends AbstractController
{
    public function __construct(private EmploymentDisputeRepository $repository, private MessageBusInterface $bus)
    {
    }

    #[Route('/', name: 'admin_submission_list', methods: ['GET', 'POST'])]
    public function index(Request $request, Pagination $paginationService, NormalizerInterface $normalizer, DenormalizerInterface $denormalizer): Response
    {
        $defaultOptions = [
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ];

        $searchData = $denormalizer->denormalize($request->query->all(), SubmissionSearchData::class);
        $form = $this->createForm(SubmissionSearchType::class, $searchData, $defaultOptions);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchData = $form->getData();
            assert($searchData instanceof SubmissionSearchData);

            $filterParams = $normalizer->normalize($searchData);
            assert(is_array($filterParams));

            return $this->redirectToRoute('admin_submission_list', $filterParams);
        }

        $options = $paginationService->createOptionsFromRequest($request);
        $pagination = $paginationService->generatePagination($this->repository, $options, $searchData);

        $status = $request->query->get('status');

        return $this->render('admin/submission_list.html.twig', [
            'pagination' => $pagination,
            'options' => $options,
            'filters' => $searchData,
            'form' => $form->createView(),
            'no_of_records' => $status ? count($this->repository->findBy(['status' => $status])) : count($this->repository->findAll()),
        ]);
    }

    #[Route('/{id}', name: 'admin_form_show', methods: ['GET', 'POST'])]
    public function show(Request $request, EmploymentDispute $employmentDispute, EmploymentDisputeRepository $employmentDisputeRepository, PayloadGenerator $payloadGenerator): Response
    {
        try {
            $payload = $payloadGenerator->createPayload($employmentDispute);
            $incidentInfo = $payload->incidentInfo;
        } catch (\InvalidArgumentException $e) {
            $incidentInfo = 'Error: '.$e->getMessage();
        }

        $form = $this->createFormBuilder($employmentDispute)
                ->add('status', ChoiceType::class, ['choices' => ['Submitted' => EmploymentDispute::STATUS_SUBMITTED, 'Draft' => EmploymentDispute::STATUS_DRAFT, 'Failed' => EmploymentDispute::STATUS_FAILED]])
                ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Status updated');
            $data = $form->getData();
            $employmentDisputeRepository->add($data);
        }

        return $this->render('admin/submission_show.html.twig', [
            'employmentDispute' => $employmentDispute,
            'submissions' => $employmentDispute->getEmploymentDisputeSubmissions(),
            'payload' => $payloadGenerator->generateJSONPayload($employmentDispute),
            'incidentInfo' => $incidentInfo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/view/{id}', name: 'admin_view_form', methods: ['GET', 'POST'])]
    public function adminViewForm(SessionHelper $sessionHelper, EmploymentDispute $employmentDispute): Response
    {
        $sessionHelper->setCurrentForm($employmentDispute);

        return $this->redirectToRoute('app_task_list', ['id' => $employmentDispute->getId()]);
    }

    #[Route('/retry/all', name: 'admin_form_retry_all', methods: ['GET', 'POST'])]
    public function retryAll(Request $request, EmploymentDisputeRepository $employmentDisputeRepository): Response
    {
        $form = $this->createFormBuilder()->add('retry', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $failed = $employmentDisputeRepository->findByStatus(EmploymentDispute::STATUS_FAILED);
            foreach ($failed as $disputeForm) {
                $disputeForm->setStatus(EmploymentDispute::STATUS_QUEUED);
                $employmentDisputeRepository->add($disputeForm);

                $this->bus->dispatch(new SubmissionMessage($disputeForm->getId()));
            }

            return $this->redirectToRoute('admin_submission_list', ['status' => 'queued']);
        }

        return $this->render('admin/submission_retry_all.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/retry', name: 'admin_form_retry', methods: ['GET', 'POST'])]
    public function retry(Request $request, EmploymentDispute $employmentDispute, PayloadGenerator $payloadGenerator): Response
    {
        if (!$employmentDispute->isRetryable()) {
            throw new BadRequestHttpException('This form is not in a state that can be retried.');
        }

        $submissionType = $request->query->get('type');

        $payload = $payloadGenerator->generateJSONPayload($employmentDispute);
        $form = $this->createFormBuilder()
            ->add('payload', TextareaType::class, ['data' => $payload])
            ->getForm();
        $form->handleRequest($request);

        $lastSubmission = $employmentDispute->getEmploymentDisputeSubmissions()->last();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            assert(is_array($data));
            $retryPayload = $data['payload'];
            $employmentDispute->setStatus(EmploymentDispute::STATUS_QUEUED);
            $this->repository->add($employmentDispute);

            if ('forced' === $submissionType) {
                $message = new SubmissionMessage($employmentDispute->getId(), $retryPayload, true);
            } else {
                $message = new SubmissionMessage($employmentDispute->getId(), $retryPayload);
            }
            $this->bus->dispatch($message);

            return $this->redirectToRoute('admin_form_show', ['id' => $employmentDispute->getId()]);
        }

        return $this->render('admin/submission_retry.html.twig', [
            'employmentDispute' => $employmentDispute,
            'form' => $form->createView(),
            'payload' => $payload,
            'submission' => $lastSubmission,
            'isForced' => 'forced' === $submissionType ? true : false,
        ]);
    }
}
