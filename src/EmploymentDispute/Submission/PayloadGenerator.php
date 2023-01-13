<?php

namespace App\EmploymentDispute\Submission;

use App\EmploymentDispute\TaskList\TaskListCreator;
use App\Entity\EmploymentDispute;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

class PayloadGenerator
{
    public function __construct(
        private TaskListCreator $taskListCreator,
        private Environment $twig,
        private NormalizerInterface $normalizer,
        private EncoderInterface $encoder,
        private PayloadNormaliser $payloadNormaliser,
    ) {
    }

    public function generateJSONPayload(EmploymentDispute $employmentDispute): string
    {
        return $this->serializePayload($this->createPayload($employmentDispute));
    }

    public function serializePayload(Payload $payload): string
    {
        $normalized = $this->normalizer->normalize($payload);
        assert(is_array($normalized));
        $normalized = array_filter($normalized, fn ($value) => !is_null($value));

        return $this->encoder->encode($normalized, 'json');
    }

    public function createPayload(EmploymentDispute $employmentDispute): Payload
    {
        $this->taskListCreator->initialize($employmentDispute);
        $storage = $this->taskListCreator->getStorage();
        $sections = $this->taskListCreator->getSections();
        $payload = new Payload($this->payloadNormaliser);
        foreach ($sections as $section) {
            $section->applyPayload($payload, $storage);
        }
        $payload->addContactMethod($employmentDispute->getContactMethod());

        $renderedText = $this->renderIncidentInfo($payload);

        $payload->setIncidentInfo($renderedText);
        $payload->setType($employmentDispute->getType());
        assert($employmentDispute->getSubmissionDateTime() instanceof \DateTimeInterface);
        $payload->setDateTime($employmentDispute->getSubmissionDateTime());
        assert(is_iterable($employmentDispute->getCaseNumbers()));
        $payload->setCaseNumbers($employmentDispute->getCaseNumbers());
        $payload->setRelationshipToClaimant($employmentDispute->getRepresenting());

        return $payload;
    }

    private function renderIncidentInfo(Payload $payload): string
    {
        $tokens = $payload->retrieveIncidentTokens();
        // Remove any tab characters twig inserts from the template.
        $rendered = preg_replace('/\t+/', '', $this->twig->render('api/incident_info.html.twig', $tokens));
        assert(is_string($rendered));
        if (mb_strlen($rendered) > 12000) {
            throw new \InvalidArgumentException('Rendered incident info is greater than 12,000 characters. Calculated length: '.mb_strlen($rendered));
        }

        return html_entity_decode($rendered, ENT_QUOTES);
    }
}
