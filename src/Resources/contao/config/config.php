<?php

$GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] = [

    'text' => [

        'default' => 'text-field',
        'time' => 'date-field',
        'date' => 'date-field',
        'datim' => 'date-field'
    ],

    'textarea' => [

        'default' => 'textarea-field'
    ],

    'select' => [

        'default' => 'select-field'
    ],

    'checkbox' => [

        'default' => 'checkbox-field'
    ],

    'radio' => [

        'default' => 'radio-field'
    ],

    'fileTree' => [

        'default' => 'upload-field'
    ]
];

$objAssetsManager = \Alnv\ContaoAssetsManagerBundle\Library\AssetsManager::getInstance();
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/single-form-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/text-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/date-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/radio-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/select-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/upload-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/checkbox-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/textarea-field-component.js' );