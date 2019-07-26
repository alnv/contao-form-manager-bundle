<?php

$GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] = [

    'text' => [

        'default' => 'text-field',
        'email' => 'text-field',
        'datim' => 'date-field',
        'time' => 'date-field',
        'date' => 'date-field'
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

$objFormAssetsManager = \Alnv\ContaoAssetsManagerBundle\Library\AssetsManager::getInstance();
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/single-form-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/multi-form-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/text-field-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/date-field-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/radio-field-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/select-field-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/upload-field-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/checkbox-field-component.js' );
$objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/textarea-field-component.js' );