<?php

namespace Haffner\JhCaptcha\Validation\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

abstract class AbstractCaptchaValidator extends AbstractValidator
{
    /**
     * Specifies whether this validator accepts empty values.
     *
     * If this is TRUE, the validators isValid() method is not called in case of an empty value
     * Note: A value is considered empty if it is NULL or an empty string!
     * By default all validators except for NotEmpty and the Composite Validators accept empty values
     *
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var array Extension TypoScript
     */
    protected $settings = [];

    protected function getSettings(): array
    {
        if ($this->settings !== []) {
            return $this->settings;
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        if (!$configurationManager instanceof ConfigurationManagerInterface) {
            return [];
        }

        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'JhCaptcha'
        );

        $this->settings = is_array($settings) ? $settings : [];
        return $this->settings;
    }

    /**
     * Creates a new validation error object and adds it to $this->results.
     *
     * @param string $translateKey
     * @param int    $code         The error code (a unix timestamp)
     * @param array  $arguments    Arguments to be replaced in message
     */
    protected function addError(string $translateKey, int $code, array $arguments = []): void
    {
        parent::addError($this->translateErrorMessage($translateKey, 'jh_captcha'), $code, $arguments);
    }
}
