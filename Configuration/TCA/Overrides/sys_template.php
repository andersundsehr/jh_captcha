<?php
defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'jh_captcha',
    'Configuration/TypoScript',
    'Google reCAPTCHA (v2/v3)'
);
