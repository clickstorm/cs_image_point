.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


Add field to tt_content
^^^^^^^^^^^^^^^^^^^^^^^^^

To add the coordinates element to a CType, you have to add this to the tt_content.php:


1. Create a new column
-------------------------

To display the coordinates element you have to create a new column of your tt_content.php.
Your tt_content.php could look like this, for example:

.. code-block:: php

    $GLOBALS['TCA']['tt_content']['columns']['tx_csimagepoint_coordinates'] = [
        'label' => 'LLL:EXT:cs_image_point/Resources/Private/Language/locallang.xlf:tx_csimagepoint_coordinates.label',
        'description' => 'LLL:EXT:cs_image_point/Resources/Private/Language/locallang.xlf:tx_csimagepoint_coordinates.description',
        'config' => [
            'type' => 'user',
            'renderType' => 'imagePointField',
            'parameters' => [
                'imageFieldName' => 'image',
                'isInlineRecord' => 'true'
            ],
        ],
    ];

**Important:** Make sure you have previously added the new table under Admin Tools > Maintenance.

Parameters
~~~~~~~~~~~~
*  imageFieldName: Specify the name of the element from which the image is taken
*  isImageInParentRecord: True if the current element is an inline record and the image field is placed by parent, e.g. multiple tooltips.


2. Add the new column to your types
-------------------------

To display the coordinates element you have to add it to the types of your tt_content.php.
Your tt_content.php could look like this, for example:

.. code-block:: php

	$GLOBALS['TCA']['tt_content']['types']['tx_csimagepoint'] = [
        'showitem' => '
            --div--;Tab, --palette--;;general, image, tx_csimagepoint_coordinates',
    ];

**Important:** Your type must contain an image field.

3. Flush all caches
------------------------------------------------

The last step: Clear the cache so that the TCA change takes effect.
