<?php

declare(strict_types=1);

namespace Clickstorm\CsImagePoint\Elements;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * renders the output field of coordinates and the button to select coordinates,
 * sends information to involved files
 */
class ImagePointElement extends AbstractFormElement
{

    /**
     * Default field information enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    /**
     * @var string
     */
    private $wizardRouteName = 'ajax_tx_cs_image_point_get_modal_content';
    public bool $tooManyImages = false;

    /**
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        parent::__construct($nodeFactory, $data);
        $this->templateView = GeneralUtility::makeInstance(StandaloneView::class);
        $this->templateView->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:cs_image_point/Resources/Private/Layouts/')]);
        $this->templateView->setPartialRootPaths([GeneralUtility::getFileAbsFileName('EXT:cs_image_point/Resources/Private/Partials/')]);
        $this->templateView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:cs_image_point/Resources/Private/Templates/ImagePointElement.html'));
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    }

    public function render(): array
    {
        $parameterArray = $this->data['parameterArray'];
        $imageFieldName = $parameterArray['fieldConf']['config']['parameters']['imageFieldName'];
        $isImageInParentRecord = filter_var($parameterArray['fieldConf']['config']['parameters']['isImageInParentRecord'], FILTER_VALIDATE_BOOLEAN);
        $databaseRow = $this->data['databaseRow'];

        // message if too many images are included in the record
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->getLanguageService()->sL(
                'LLL:EXT:cs_image_point/Resources/Private/Language/locallang.xlf:error.wrongNumberOfImages.instruction'
            ),
            $this->getLanguageService()->sL(
                'LLL:EXT:cs_image_point/Resources/Private/Language/locallang.xlf:error.wrongNumberOfImages.header'
            ),
            FlashMessage::ERROR,
            true
        );

        // if element is inline record, a data query is made
        if ($isImageInParentRecord == true) {
            $uidForeign = (int)$this->data['inlineParentUid'];
            $tablename = $this->data['inlineParentTableName'];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
            $result = $queryBuilder
                ->select('uid')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($uidForeign)),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($imageFieldName)),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tablename))
                )
                ->execute();
            $result = $result->fetchAllAssociative();
            $sysFileReferenceUid = $result[0]['uid'] ?? 0;

            // if multiple images are included, then an error will be output
            if (count($result) > 1) {
                $this->tooManyImagesError($message);
            }
        // otherwise it takes the image from the global array
        } else {
            $sysFileReferenceUid = (int)$databaseRow[$imageFieldName];

            // if multiple images are included, then an error will be output
            if (str_contains((string)($databaseRow[$imageFieldName]), ',')) {
                $this->tooManyImagesError($message);
            }
        }

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        $fieldId = StringUtility::getUniqueId('formengine-textarea-');
        $buttonId = StringUtility::getUniqueId('btn-get-coords-');

        $attributes = [
            'id' => $fieldId,
            'name' => htmlspecialchars($parameterArray['itemFormElName']),
            'data-formengine-input-name' => htmlspecialchars($parameterArray['itemFormElName']),
        ];

        $attributes['placeholder'] = 'Coordinates';
        $classes = [
            'form-control',
            't3js-formengine-textarea',
            'formengine-textarea',
        ];
        $itemValue = $parameterArray['itemFormElValue'];
        $attributes['class'] = implode(' ', $classes);

        $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS(
            'TYPO3/CMS/CsImagePoint/ImagePointElement'
        )->invoke('init', $fieldId, $buttonId);

        $arguments = [
            'uri' => $this->getWizardUri($sysFileReferenceUid),
            'id' => $fieldId,
            'fieldInformationHtml' => $fieldInformationHtml,
            'itemValue' => htmlspecialchars($itemValue, ENT_QUOTES),
            'attributesString' => GeneralUtility::implodeAttributes($attributes, true),
            'attributes' => $attributes,
            'iconHtml' => $this->iconFactory->getIcon('actions-thumbtack', Icon::SIZE_SMALL)->render(),
            'sysFileReferenceUid' => $sysFileReferenceUid,
            'btnGetCoords' => $buttonId,
            'tooManyImages' => $this->tooManyImages,
        ];

        $this->templateView->assignMultiple($arguments);
        $resultArray['html'] = $this->templateView->render();

        return $resultArray;
    }

    /**
     * @return string
     * @throws RouteNotFoundException
     */
    protected function getWizardUri(int $sysFileReferenceUid): string
    {
        return (string)$this->uriBuilder->buildUriFromRoute($this->wizardRouteName, ['sysFileReferenceUid' => $sysFileReferenceUid]);
    }

    /**
     * returns an error message if too many images are included
     */
    public function tooManyImagesError($message)
    {
        $this->tooManyImages = true;
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->getAllMessagesAndFlush();
        $messageQueue->addMessage($message);
    }
}
