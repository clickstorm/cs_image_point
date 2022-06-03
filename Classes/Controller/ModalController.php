<?php

declare(strict_types=1);

namespace Clickstorm\CsImagePoint\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * renders the modal and passes the image
 */
class ModalController
{
    /**
     * @var StandaloneView
     */
    private $templateView;

    /**
     * @param StandaloneView $templateView
     */
    public function __construct(StandaloneView $templateView = null)
    {
        if (!$templateView) {
            $templateView = GeneralUtility::makeInstance(StandaloneView::class);
            $templateView->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:backend/Resources/Private/Layouts/')]);
            $templateView->setPartialRootPaths([GeneralUtility::getFileAbsFileName('EXT:backend/Resources/Private/Partials/')]);
            $templateView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:cs_image_point/Resources/Private/Templates/ModalBody.html'));
        }
        $this->templateView = $templateView;
    }

    /**
     * Returns the HTML for the wizard inside the modal
     * @param ServerRequestInterface $request
     * @return ResponseInterface $response
     * @throws ResourceDoesNotExistException
     */
    public function getModalContent(ServerRequestInterface $request): ResponseInterface
    {
        $sysFileReferenceUid = (int)$_GET['sysFileReferenceUid'];

        // if the SysFileReferenceUid was passed, the image is searched and passed
        if ($sysFileReferenceUid > 0) {
            $image = GeneralUtility::makeInstance(ResourceFactory::class)->getFileReferenceObject($sysFileReferenceUid);
        } else {
            $image = null;
        }

        $viewData = [
            'image' => $image,
            'sysFileReferenceUid' => $sysFileReferenceUid,
        ];
        $content = $this->templateView->renderSection('Main', $viewData);
        return new HtmlResponse($content);
    }
}
