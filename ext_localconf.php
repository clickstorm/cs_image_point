<?php

use Clickstorm\CsImagePoint\Elements\ImagePointElement;

defined('TYPO3_MODE') || die();

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1650359230] = [
        'nodeName' => 'imagePointField',
        'priority' => 40,
        'class' => ImagePointElement::class,
    ];
})();
