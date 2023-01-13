<?php

namespace App\EmploymentDispute\Tasks;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

/**
 * PHPStan is configured to ignore the lack of array type definition for the config
 * variable. We should refactor this to use a class to benefit from typing when we
 * have the chance.
 */
class ConfigFileTask extends BaseTask implements TaskInterface
{
    public function __construct(private string $id, private array $config)
    {
    }

    public function buildForm(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        foreach ($this->config['form'] as $key => $field) {
            $options = $this->evaluateOptions($field['options'] ?? []);
            // If no label defined in config, default to not display
            $options['label'] = $options['label'] ?? false;
            $templateOptions = $this->getTemplateOptions();
            $options['label_is_title'] = $templateOptions['no_title_display'] ?? false;

            if ($options['maxlength'] ?? null) {
                $message = $options['maxlength_error'] ?? null;
                $options['constraints'] = [new Length(max: $options['maxlength'], maxMessage: $message)];
            }

            $formBuilder->add($key, $field['type'], $options);
        }

        return $formBuilder;
    }

    private function evaluateOptions(array $options): array
    {
        foreach ($options as $key => $value) {
            if ('choices' == $key && isset($value['id'])) {
                $options[$key] = $this->options->getChoiceOptions($value['id']);

                // loop to replace tokens in keys
                $option_key_array = [];
                foreach ($options[$key] as $option_key => $option_value) {
                    $replaced_key = $this->tokenReplace($option_key);
                    $option_key_array[$replaced_key] = $option_value;
                }
                $options[$key] = $option_key_array;
            } elseif (is_array($value) && isset($value['other']) && isset($value['myself'])) {
                $options[$key] = TaskOptions::REPRESENTATIVE_MYSELF === $this->options->getRepresentative() ? $value['myself'] : $value['other'];
            } elseif (is_string($value)) {
                $options[$key] = $this->tokenReplace($value);
            }
        }

        return $options;
    }

    private function tokenReplace(string $value): string
    {
        $replacements = [];
        preg_match_all('#(?<match>\[.*?\])#', $value, $matches);
        foreach ($matches['match'] as $match) {
            $contents = trim($match, '[]');
            list($myself, $other) = explode('|', $contents);
            $replacements[$match] = TaskOptions::REPRESENTATIVE_MYSELF === $this->options->getRepresentative() ? $myself : $other;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }

    public function getTemplate(): string
    {
        return $this->config['template'] ?? 'app/task_base.html.twig';
    }

    public function getTemplateOptions(): array
    {
        $options = $this->config['template_options'] ?? [];

        return $this->evaluateOptions($options);
    }

    public function getLabel(): string
    {
        return $this->getText('label');
    }

    public function getTitle(): string
    {
        return $this->getText('title');
    }

    public function getReviewPageLabel(): string
    {
        return $this->getText('review');
    }

    private function getText(string $type): string
    {
        $labels = $this->getLabels($type);

        if (TaskOptions::FLOW_CERTIFICATE === $this->options->getFlow()) {
            if (TaskOptions::REPRESENTATIVE_MYSELF === $this->options->getRepresentative()) {
                $value = $labels['certificate_myself'];
            } else {
                $value = $labels['certificate_other'];
            }
        } else {
            if (TaskOptions::REPRESENTATIVE_MYSELF === $this->options->getRepresentative()) {
                $value = $labels['ec_myself'];
            } else {
                $value = $labels['ec_other'];
            }
        }

        return $this->tokenReplace($value);
    }

    /**
     * @return array<string, string>
     */
    private function getLabels(string $type = 'label'): array
    {
        if (!isset($this->config[$type])) {
            $type = 'label';
        }

        return [
            'certificate_myself' => $this->config[$type]['certificate']['myself'] ?? $this->config[$type]['myself'] ?? $this->config[$type] ?? $this->config['label'],
            'certificate_other' => $this->config[$type]['certificate']['other'] ?? $this->config[$type]['other'] ?? $this->config[$type] ?? $this->config['label'],
            'ec_myself' => $this->config[$type]['ec']['myself'] ?? $this->config[$type]['myself'] ?? $this->config[$type] ?? $this->config['label'],
            'ec_other' => $this->config[$type]['ec']['other'] ?? $this->config[$type]['other'] ?? $this->config[$type] ?? $this->config['label'],
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDataKey(): string
    {
        return $this->config['data_key'];
    }

    public function getDataClass(): string
    {
        return $this->config['data_class'];
    }

    public function getFormField(): array
    {
        return $this->config['form'];
    }

    public function getWeight(): int
    {
        if (!isset($this->config['weight'])) {
            return 0;
        }

        return $this->config['weight'];
    }
}
