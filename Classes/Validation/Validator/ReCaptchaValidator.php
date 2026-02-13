<?php

namespace Haffner\JhCaptcha\Validation\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ReCaptchaValidator extends AbstractCaptchaValidator
{
    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to Result.
     *
     * @param mixed $value
     */
    protected function isValid($value): void
    {
        $settings = $this->getSettings();

        if ((int)($settings['reCaptcha']['version'] ?? 3) === 2) {
            $secret = htmlspecialchars((string)($settings['reCaptcha']['v2']['secretKey'] ?? ''));
        } else {
            $secret = htmlspecialchars((string)($settings['reCaptcha']['v3']['secretKey'] ?? ''));
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $apiResponse = json_decode(
            GeneralUtility::getUrl($url.'?secret='.$secret.'&response='.$value),
            true
        );

        if (!is_array($apiResponse) || ($apiResponse['success'] ?? false) !== true) {
            if (is_array($apiResponse['error-codes'] ?? null)) {
                foreach ($apiResponse['error-codes'] as $errorCode) {
                    switch ($errorCode) {
                        case 'missing-input-secret':
                            $this->addError('missingInputSecret', 1426877004);
                            break;
                        case 'invalid-input-secret':
                            $this->addError('invalidInputSecret', 1426877455);
                            break;
                        case 'missing-input-response':
                            $this->addError('missingInputResponse', 1426877525);
                            break;
                        case 'invalid-input-response':
                            $this->addError('invalidInputResponse', 1426877590);
                            break;
                        case 'bad-request':
                            $this->addError('badRequest', 1426877490);
                            break;
                        case 'timeout-or-duplicate':
                            $this->addError('timeoutOrDuplicate', 1426877420);
                            break;
                        default:
                            $this->addError('defaultError', 1427031929);
                    }
                }
            } else {
                $this->addError('defaultError', 1427031929);
            }
        } else {
            if ((int)($settings['reCaptcha']['version'] ?? 3) !== 2 && isset($apiResponse['score'])) {
                if ((float)$apiResponse['score'] < (float)($settings['reCaptcha']['v3']['minimumScore'] ?? 0.0)) {
                    $this->addError('scoreError', 1541173838);
                }
            }
        }
    }
}
