<?php

namespace Haffner\JhCaptcha\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ReCaptchaViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'String', 'reCaptcha uid', false);
        $this->registerArgument('type', 'String', 'form type', false);
    }

    public function render(): string
    {
        $settings = $this->getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'JhCaptcha'
        );
        $settings = is_array($settings) ? $settings : [];
        $reCaptchaSettings = is_array($settings['reCaptcha'] ?? null) ? $settings['reCaptcha'] : [];

        $captchaResponseId = 'captchaResponse';
        if ($this->arguments['uid']) {
            $captchaResponseId = $captchaResponseId . '-' . $this->arguments['uid'];
        }

        if ((int)($reCaptchaSettings['version'] ?? 3) === 2) {
            // render v2
            if (!empty($reCaptchaSettings['v2']['siteKey'])) {
                return $this->renderV2($captchaResponseId, $reCaptchaSettings);
            } else {
                return (string)LocalizationUtility::translate('setApiKey', 'jh_captcha');
            }
        } else {
            // render v3
            if (!empty($reCaptchaSettings['v3']['siteKey'])) {
                return $this->renderV3($captchaResponseId, $reCaptchaSettings);
            } else {
                return (string)LocalizationUtility::translate('setApiKey', 'jh_captcha');
            }
        }
    }

    private function renderV2(string $captchaResponseId, array $settings): string
    {
        $siteKey = htmlspecialchars((string)($settings['v2']['siteKey'] ?? ''));
        $theme = htmlspecialchars((string)($settings['v2']['theme'] ?? 'light'));
        $lang = htmlspecialchars((string)($settings['v2']['lang'] ?? 'en'));
        $size = htmlspecialchars((string)($settings['v2']['size'] ?? 'normal'));

        $callBack = '';
        $reCaptcha = '<div id="recaptcha' . $this->arguments['uid'] . '"></div>';
        $renderReCaptcha = '<script type="text/javascript">var apiCallback' . str_replace("-", "", $this->arguments['uid']) . ' = function() { reCaptchaWidget' . str_replace("-", "", $this->arguments['uid']) . ' = grecaptcha.render("recaptcha' . $this->arguments['uid'] . '", { "sitekey" : "' . $siteKey .'", "callback" : "captchaCallback' . str_replace("-", "", $this->arguments['uid']) .'", "theme" : "' . $theme . '", "size" : "' . $size . '" }); }</script>';
        $reCaptchaApi = '<script src="https://www.google.com/recaptcha/api.js?onload=apiCallback' . str_replace("-", "", $this->arguments['uid']) . '&hl=' . $lang . '&render=explicit" async defer></script>';
        if (!$this->isPowermail()) {
            $callBack = '<script type="text/javascript">var captchaCallback' . str_replace("-", "", $this->arguments['uid']) . ' = function() { document.getElementById("' . $captchaResponseId . '").value = grecaptcha.getResponse(reCaptchaWidget' . str_replace("-", "", $this->arguments['uid']) . ') }</script>';
        }

        return $reCaptcha . $callBack . $renderReCaptcha . $reCaptchaApi;
    }

    private function renderV3(string $captchaResponseId, array $settings): string
    {
        $callBackFunctionName = 'onLoad' .
            $this->arguments['type'] . str_replace("-", "", $this->arguments['uid']);

        $captchaResponseField = '';
        if ($this->isPowermail()) {
            $captchaResponseField = '<input type="hidden" id="' . $captchaResponseId . '" name="g-recaptcha-response">';
        }

        $callBack =
            '<script type="text/javascript">'.
                'var ' . $callBackFunctionName . ' = function() {'.
                    'grecaptcha.execute('.
                        '"' . htmlspecialchars((string)($settings['v3']['siteKey'] ?? '')) . '",'.
                        '{action: "' . htmlspecialchars((string)($settings['v3']['action'] ?? 'homepage')) . '"})'.
                        '.then(function(token) {'.
                            'document.getElementById("' . $captchaResponseId . '").value = token;'.
                        '}'.
                    ');'.
                '};'.
                'setInterval(' . $callBackFunctionName . ', 100000);'.
            '</script>';
        $api =
            '<script src="https://www.google.com/recaptcha/api.js?'.
                'render=' . htmlspecialchars((string)($settings['v3']['siteKey'] ?? '')) . '&'.
                'onload=' . $callBackFunctionName . '"></script>';

        return $captchaResponseField . $callBack . $api;
    }

    /**
     * @return bool
     */
    private function isPowermail(): bool
    {
        return ($this->arguments['type'] == "powermail" ? true : false);
    }

    private function getConfigurationManager(): ConfigurationManagerInterface
    {
        if ($this->configurationManager instanceof ConfigurationManagerInterface) {
            return $this->configurationManager;
        }

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->configurationManager = $configurationManager;
        return $configurationManager;
    }
}
