<?php

$GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] = [
    'text' => [
        'default' => 'text-field',
        'time' => 'date-field',
        'date' => 'date-field',
        'datim' => 'date-field'
    ],
    'select' => [
        'default' => 'select-field'
    ]
];

$objAssetsManager = \Alnv\ContaoAssetsManagerBundle\Library\AssetsManager::getInstance();
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/single-form-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/text-field-component.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/select-field-component.js' );