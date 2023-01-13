<?php

namespace App\Serializer;

use App\EmploymentDispute\Tasks\TaskOptions;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ReviewEncoder implements EncoderInterface
{
    public function encode(mixed $data, string $format, array $context = []): string
    {
        // @todo should we refactor this?
        $content = '';
        $taskId = $context['taskId'] ?? null;
        $isDate = false;
        $isExtraInfo = false;
        $isExtraMoney = false;
        switch ($taskId) {
            case 't53_when_wages_owed':
            case 't53b_when_redundancy_owed':
            case 't58_termination_date':
            case 't62_when_discriminated':
            case 't69_resignation_date':
            case 't56_holiday_year':
                $isDate = true;
                break;
            case 't81_date_of_detriment':
            case 't84_dispute_date':
            case 't71_about_notice_pay':
                $isExtraInfo = true;
                break;
        }

        if (is_array($data)) {
            if ('respondent_org_name' === $taskId) {
                $orgName = $data['orgName'] ?? null;
                $stillWorkingForOrg = $data['stillWorkingForOrg'] ?? null;
                $orgOutOfBusiness = $data['orgOutOfBusiness'] ?? null;
                if ($orgName) {
                    $content = $orgName;
                }
                if ($stillWorkingForOrg) {
                    $content .= PHP_EOL.'Still working there';
                }
                if ($orgOutOfBusiness) {
                    $content .= PHP_EOL.'Organisation no longer in business';
                }
            } elseif ('t77_raised_with' === $taskId) {
                $selectedChoices = $data['data'] ?? [];
                $readable = [];
                foreach ($selectedChoices as $choice) {
                    $text = TaskOptions::REVIEW_DISPLAY_RAISED_WITH[$choice] ?? null;
                    if ($text) {
                        $readable[] = ucfirst($text);
                    }
                }
                $content = implode(', ', $readable);
            } elseif ('t79_why_is_it_about_whistleblowing' === $taskId) {
                $selectedChoices = $data['data'] ?? [];
                $readable = [];
                foreach ($selectedChoices as $choice) {
                    $text = array_search($choice, TaskOptions::CHOICE_IS_IT_ABOUT_WHISTLEBLOWING);
                    if (is_string($text)) {
                        $readable[] = ucfirst($text);
                    }
                }
                $content = implode(', ', $readable);
            } elseif ('t61_why_discrimated' === $taskId) {
                $selectedChoices = $data['data'] ?? [];
                $readable = [];
                foreach ($selectedChoices as $choice) {
                    $text = array_search($choice, TaskOptions::CHOICE_WHY_DISCRIMINATED);
                    if (is_string($text)) {
                        $readable[] = ucfirst($text);
                    }
                }
                $content = implode(', ', $readable);
            } elseif ($isDate) {
                $dateString = $data['start'] ?? null;
                $dateStringTo = $data['end'] ?? null;
                if ($dateString) {
                    $date = \DateTime::createFromFormat("Y-m-d\TH:i:sP", $dateString);
                    if ($date) {
                        $content = 't56_holiday_year' === $taskId ? 'Start date: '.$date->format('d F Y').PHP_EOL : $date->format('d F Y');
                    }
                }
                if ($dateStringTo) {
                    $dateTo = \DateTime::createFromFormat("Y-m-d\TH:i:sP", $dateStringTo);
                    if ($dateTo) {
                        $content .= 'End date: '.$dateTo->format('d F Y');
                    }
                }
            } elseif ($isExtraInfo) {
                $taskData = $data['data'] ?? [];
                $hasExtra = $taskData['hasExtra'] ?? null;
                if ('no' === $hasExtra) {
                    $content = $hasExtra;
                } elseif ('yes' === $hasExtra) {
                    $value = $taskData['extraInformation'] ?? null;
                    $date = $value ? \DateTime::createFromFormat("Y-m-d\TH:i:sP", $value) : null;
                    if ($date) {
                        $content = $hasExtra.PHP_EOL.$date->format('d F Y');
                    } elseif ($value && 'yes' === $hasExtra) {
                        $content = $hasExtra.PHP_EOL.$value;
                    } elseif ('yes' === $hasExtra) {
                        $content = $hasExtra;
                    }
                }
            } else {
                foreach ($data as $details) {
                    if (is_array($details)) {
                        if (isset($details['empty'])) {
                            unset($details['empty']);
                        }
                        foreach ($details as $element) {
                            if (empty($element)) {
                                continue;
                            }
                            if (is_string($element)) {
                                $content .= $element.PHP_EOL;
                            }
                            if (is_array($element)) {
                                foreach ($element as $item) {
                                    if ($item) {
                                        $content .= $item.PHP_EOL;
                                    }
                                }
                            }
                        }
                    } else {
                        if ($details) {
                            $content .= $details.PHP_EOL;
                        }
                    }
                }
            }
        } else {
            // we should never get here
        }

        return $content;
    }

    public function supportsEncoding(string $format): bool
    {
        return 'review' === $format;
    }
}
