.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. _installation:

Installation
================

The coordinates field can be easily added to any content element via TCA.

.. toctree::
	:maxdepth: 5
	:titlesonly:

	pages/Index
	tt_content/Index

Add new table
^^^^^^^^^^^^^^^^^^^^^^^^^

A new column must be created so that the coordinates field can be displayed and the coordinates can be saved later.

Add the following lines to your ext_tables.sql:

.. code-block:: php

    CREATE TABLE tt_content
    (
        tx_cs_image_point varchar(255) DEFAULT '0;0' NOT NULL,
    );

Then register the new column in the backend:

#. Go to Admin Tools > Maintenance
#. Select "Analyze database" from 'Analyze Database Structure'
#. Add the new column and confirm with Save
