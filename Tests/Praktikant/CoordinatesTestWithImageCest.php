<?php

namespace CodeceptionTests\Praktikant;

use AcceptanceTester;

class CoordinatesTestWithImageCest
{
    public static $URL_TO_TEST = '/typo3/record/edit?edit%5Bpages%5D%5B1%5D=edit';
    public static $CSS_Resources_Tab = 'li:nth-child(7)';
    public static $CSS_Coordinates_Modal_Button = '.tx-cs-image-point-codeception-btn';
    public static $CSS_Coordinates_Modal = '.modal-coordinates';
    public static $CSS_Coordinates_Image = '.tx-cs-image-point-image';
    public static $CSS_Pointer = '.tx-cs-image-point-point';
    public static $CSS_Dropzone = '.tx-cs-image-point-codeception-dropzone';
    public static $CSS_Output = '.tx-cs-image-point-text-output';

    public function _before(AcceptanceTester $I)
    {
        $I->wantTo('Test the Drag&Drop Functionality');
        if ($I->loadSessionSnapshot('login')) {
            return;
        }
        $I->amOnPage('/typo3');
        $I->fillField('#t3-username', '');
        $I->fillField('#t3-password', '');
        $I->click('#t3-login-submit');
        $I->saveSessionSnapshot('login');

        $I->amOnPage(static::$URL_TO_TEST);
        $I->switchToIFrame('list_frame');
        $I->clickWithLeftButton(static::$CSS_Resources_Tab);
        $I->click(static::$CSS_Coordinates_Modal_Button);
        $I->switchToIFrame();
        $I->waitForElement(static::$CSS_Coordinates_Modal);
    }

    public function allTests(AcceptanceTester $I)
    {
        $this->checkDragAndDrop($I, 900, 700);
        $I->see('Percentage coordinates: X: 10.1% Y: 09.8%');

        $this->checkDragAndDrop($I, 600, 500);
        $I->see('Percentage coordinates: X: 10.1% Y: 09.7%');

        $this->checkDragAndDrop($I, 1200, 800);
        $I->see('Percentage coordinates: X: 10.0% Y: 09.9%');
    }

    public function checkDragAndDrop(AcceptanceTester $I, $windowWidth = 0, $windowHeight = 0)
    {
        $I->resizeWindow($windowWidth, $windowHeight);
        $I->waitForElementVisible(static::$CSS_Coordinates_Image);
        $I->dragAndDrop(static::$CSS_Pointer, static::$CSS_Dropzone);
    }
}
