<?php

declare(strict_types=1);

use Clickstorm\CsImagePoint\Controller;

return [

    // retrieved the modal content
    'tx_cs_image_point_get_modal_content' => [
        'path' => '/cs_image_point/modal',
        'target' => Controller\ModalController::class . '::getModalContent',
    ],
];
