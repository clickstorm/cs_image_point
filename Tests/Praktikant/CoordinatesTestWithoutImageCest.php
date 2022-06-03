<?php

namespace CodeceptionTests\Praktikant;

use AcceptanceTester;

class CoordinatesTestWithoutImageCest
{
    public static $URL_TO_TEST = '/typo3/record/edit?edit%5Bpages%5D%5B5%5D=edit';
    public static $CSS_Resources_Tab = 'li:nth-child(7)';

    public function _before(AcceptanceTester $I)
    {
        $I->wantTo('tests the error message when no image is available');

        if ($I->loadSessionSnapshot('login')) {
            return;
        }
        $I->amOnPage('/typo3');
        $I->fillField('#t3-username', '');
        $I->fillField('#t3-password', '');
        $I->click('#t3-login-submit');
        $I->saveSessionSnapshot('login');
    }

    public function checkErrorMessage(AcceptanceTester $I)
    {
        $I->amOnPage(static::$URL_TO_TEST);
        $I->switchToIFrame('list_frame');
        $I->clickWithLeftButton(static::$CSS_Resources_Tab);
        $I->see('Error: Please add maximum one image to the [image] field and confirm with "Save".');
    }
}
