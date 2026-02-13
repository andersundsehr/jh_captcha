<?php

namespace Haffner\JhCaptcha\Validation\Validator;

use In2code\Powermail\Domain\Model\Field;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Powermail extends \In2code\Powermail\Domain\Validator\SpamShield\AbstractMethod
{
    /**
     * @return bool true if spam recognized
     */
    public function spamCheck(): bool
    {
        $powermailArguments = $this->getRequestArgument('tx_powermail_pi1');

        # Skip captcha check on confirmation page
        if (
            property_exists($this, 'flexForm')
                && is_array($powermailArguments)
                && ($powermailArguments['action'] ?? '') === 'create'
                && ($this->flexForm['settings']['flexform']['main']['confirmation'] ?? '0') === '1'
        ) {
            return false;
        }

        foreach ($this->mail->getForm()->getPages() as $page) {
            /** @var Field $field */
            foreach ($page->getFields() as $field) {
                if ($field->getType() === 'JhCaptchaRecaptcha') {
                    /** @var ReCaptchaValidator $reCaptchaValidator */
                    $reCaptchaValidator = GeneralUtility::makeInstance(ReCaptchaValidator::class);
                    $captchaResponse = $this->getRequestArgument('g-recaptcha-response');
                    $result = $reCaptchaValidator->validate(
                        is_string($captchaResponse) ? $captchaResponse : ''
                    );
                    if (!empty($result->getErrors())) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Reads request arguments without using deprecated GeneralUtility::_GP* APIs.
     *
     * @param string $argumentName
     *
     * @return mixed
     */
    protected function getRequestArgument(string $argumentName)
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof ServerRequestInterface) {
            $queryArguments = $request->getQueryParams();
            $bodyArguments = $request->getParsedBody();
            $bodyArguments = is_array($bodyArguments) ? $bodyArguments : [];
            $requestArguments = array_replace_recursive($queryArguments, $bodyArguments);
            return $requestArguments[$argumentName] ?? null;
        }

        return null;
    }
}
