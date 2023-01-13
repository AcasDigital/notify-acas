<?php

namespace App\EmploymentDispute\Submission\Search;

use App\EmploymentDispute\Tasks\Data\FullNameTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalFullNameTaskData;
use App\EmploymentDispute\Tasks\Data\OrgNameTaskData;
use App\EmploymentDispute\Tasks\TaskDataInterface;
use App\Entity\EmploymentDispute;
use App\Entity\SubmissionSearchIndexItemFirstName;
use App\Entity\SubmissionSearchIndexItemLastName;
use App\Entity\SubmissionSearchIndexItemRespondentName;
use App\Repository\SubmissionSearchIndexItemFirstNameRepository;
use App\Repository\SubmissionSearchIndexItemLastNameRepository;
use App\Repository\SubmissionSearchIndexItemRespondentNameRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

class SubmissionSearchIndexer
{
    public const SUBMISSION_SEARCH_TYPE_FIRST_NAME = 'first-name';
    public const SUBMISSION_SEARCH_TYPE_LAST_NAME = 'last-name';
    public const SUBMISSION_SEARCH_TYPE_RESPONDENT_NAME = 'respondent-name';

    private ObjectManager $em;
    private EmploymentDispute $disputeForm;

    public function __construct(
        private ManagerRegistry $doctrine,
        private SubmissionSearchIndexItemFirstNameRepository $firstNameRepo,
        private SubmissionSearchIndexItemLastNameRepository $lastNameRepo,
        private SubmissionSearchIndexItemRespondentNameRepository $respondentNameRepo
    ) {
        $this->em = $this->doctrine->getManager();
    }

    public function indexTaskData(TaskDataInterface $data, string $sectionId, EmploymentDispute $disputeForm): void
    {
        $this->disputeForm = $disputeForm;
        // index claimant/representative first name and last name, respondent first name and last name and respondent org name
        $firstName = null;
        $lastName = null;
        $orgName = null;

        // get data
        if ($data instanceof FullNameTaskData || $data instanceof OptionalFullNameTaskData) {
            $firstName = $data->getFullName()?->getFirstName();
            $lastName = $data->getFullName()?->getLastName();
        }

        if ($data instanceof OrgNameTaskData) {
            $orgName = $data->getOrgName();
        }

        $respondentSection = 'respondent';

        // define searchTypes, and send for indexing
        if ($firstName && str_contains($sectionId, $respondentSection)) {
            $this->indexItems($firstName, self::SUBMISSION_SEARCH_TYPE_RESPONDENT_NAME, $sectionId.'-first-name');
        } elseif ($firstName) {
            $this->indexItems($firstName, self::SUBMISSION_SEARCH_TYPE_FIRST_NAME, $sectionId);
        }

        if ($lastName && str_contains($sectionId, $respondentSection)) {
            $this->indexItems($lastName, self::SUBMISSION_SEARCH_TYPE_RESPONDENT_NAME, $sectionId.'-last-name');
        } elseif ($lastName) {
            $this->indexItems($lastName, self::SUBMISSION_SEARCH_TYPE_LAST_NAME, $sectionId);
        }

        if ($orgName) {
            $this->indexItems($orgName, self::SUBMISSION_SEARCH_TYPE_RESPONDENT_NAME, $sectionId.'-org-name');
        }
    }

    private function indexItems(string $keyword, string $searchType, string $section): void
    {
        // make sure we delete/update the correct items
        // (respondent data can come from a person update or an org name update, and can have multiple of both)
        switch ($searchType) {
            case self::SUBMISSION_SEARCH_TYPE_FIRST_NAME:
                $this->indexFirstName($keyword, $searchType, $section);
                break;
            case self::SUBMISSION_SEARCH_TYPE_LAST_NAME:
                $this->indexLastName($keyword, $searchType, $section);
                break;
            case self::SUBMISSION_SEARCH_TYPE_RESPONDENT_NAME:
                $this->indexRespondentName($keyword, $searchType, $section);
                break;
        }
    }

    private function indexFirstName(string $keyword, string $searchType, string $section): void
    {
        $indexedItems = $this->firstNameRepo->getAllByTaskDetails($this->disputeForm->getId(), $searchType, $section);
        // remove previous task content first
        if (!empty($indexedItems)) {
            foreach ($indexedItems as $itemToDelete) {
                $this->disputeForm->removeSearchIndexItemFirstName($itemToDelete);
                $this->em->persist($itemToDelete);
            }
            $this->em->flush();
        }

        $item = new SubmissionSearchIndexItemFirstName();
        $item->setKeyword($keyword);
        $item->setType($searchType);
        $item->setSection($section);

        $this->disputeForm->addSearchIndexItemFirstName($item);
        $this->em->persist($item);
        $this->em->flush();
    }

    private function indexLastName(string $keyword, string $searchType, string $section): void
    {
        $indexedItems = $this->lastNameRepo->getAllByTaskDetails($this->disputeForm->getId(), $searchType, $section);
        // remove previous task content first
        if (!empty($indexedItems)) {
            foreach ($indexedItems as $itemToDelete) {
                $this->disputeForm->removeSearchIndexItemLastName($itemToDelete);
                $this->em->persist($itemToDelete);
            }
            $this->em->flush();
        }

        $item = new SubmissionSearchIndexItemLastName();
        $item->setKeyword($keyword);
        $item->setType($searchType);
        $item->setSection($section);

        $this->disputeForm->addSearchIndexItemLastName($item);
        $this->em->persist($item);
        $this->em->flush();
    }

    private function indexRespondentName(string $keyword, string $searchType, string $section): void
    {
        // @todo - review deleting respondents and switching respondent type task
        // this handles if the respondent type is switched and a new one is saved
        // limitation: it doesn't handle switching back to the first one.
        // Switch in itself doesn't trigger re-indexing.
        $sectionDetails = explode('-', $section);
        $type = $sectionDetails[2] ?? null;
        $sectionPartial1 = null;
        $sectionPartial2 = null;
        $sectionPartial3 = null;
        if ('org' === $type) {
            // e.g.: respondent-6307850adf3c9-person-first-name > $sectiondPartial: 6307850adf3c9-person
            $sectionPartial1 = $sectionDetails[1].'-first';
            $sectionPartial2 = $sectionDetails[1].'-last';
            $sectionPartial3 = $sectionDetails[1].'-org';
        } elseif ('first' === $type) {
            $sectionPartial1 = $sectionDetails[1].'-org';
            $sectionPartial2 = $sectionDetails[1].'-first';
        } elseif ('last' === $type) {
            $sectionPartial1 = $sectionDetails[1].'-org';
            $sectionPartial2 = $sectionDetails[1].'-last';
        }

        if ($sectionPartial1) {
            $this->deleteIndexedRespondentData($sectionPartial1, $searchType);
        }

        if ($sectionPartial2) {
            $this->deleteIndexedRespondentData($sectionPartial2, $searchType);
        }

        if ($sectionPartial3) {
            $this->deleteIndexedRespondentData($sectionPartial3, $searchType);
        }

        $item = new SubmissionSearchIndexItemRespondentName();
        $item->setKeyword($keyword);
        $item->setType($searchType);
        $item->setSection($section);

        $this->disputeForm->addSearchIndexItemRespondentName($item);
        $this->em->persist($item);
        $this->em->flush();
    }

    private function deleteIndexedRespondentData(string $sectionPartial, string $searchType): void
    {
        $indexedItems = $this->respondentNameRepo->getAllByTaskDetails($this->disputeForm->getId(), $searchType, $sectionPartial);
        // remove previous task content first
        if (!empty($indexedItems)) {
            foreach ($indexedItems as $itemToDelete) {
                $this->disputeForm->removeSearchIndexItemRespondentName($itemToDelete);
                $this->em->persist($itemToDelete);
            }
            $this->em->flush();
        }
    }
}
