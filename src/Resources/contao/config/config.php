<?php

array_insert( $GLOBALS['FE_MOD'], 2, [
    'form-manager-bundle' => [
        'form-manager' => 'Alnv\ContaoFormManagerBundle\Modules\FormManagerModule',
    ]
]);

$GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] = [
    'text' => [
        'default' => 'text-field',
        'datim' => 'date-field',
        'time' => 'date-field',
        'date' => 'date-field'
    ],
    'number' => [
        'default' => 'text-field'
    ],
    'url' => [
        'default' => 'text-field'
    ],
    'email' => [
        'default' => 'email-field'
    ],
    'hidden' => [
        'default' => 'hidden-field'
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
    ],
    'formWizard' => [
        'default' => 'form-wizard'
    ],
    'nouislider' => [
        'default' => 'number-field'
    ],
    'fieldsetStart' => [
        'default' => 'fieldset-start'
    ]
];

$GLOBALS['BE_FFL']['formWizard'] = 'Alnv\ContaoFormManagerBundle\Widgets\FormWizard';
$GLOBALS['TL_FFL']['formWizard'] = 'Alnv\ContaoFormManagerBundle\Forms\FormWizard';

if ( \Alnv\ContaoFormManagerBundle\Helper\Toolkit::shouldLoadVueScripts() ) {

    $objFormAssetsManager = \Alnv\ContaoAssetsManagerBundle\Library\AssetsManager::getInstance();
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/libs/dropzone/js/dropzone.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/libs/flatpickr/js/flatpickr.min.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/libs/flatpickr/js/vue-flatpickr-component.min.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/single-form-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/multi-form-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/text-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/date-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/radio-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/form-wizard-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/email-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/select-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/hidden-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/upload-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/number-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/checkbox-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/textarea-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/fieldset-start-component.js' );

    $objFormCssCombiner = new \Combiner();
    $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/js/libs/flatpickr/styles/flatpickr.min.scss' );
    $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/js/libs/dropzone/styles/basic.scss' );
    $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/js/libs/dropzone/styles/dropzone.scss' );

    if ( TL_MODE == 'BE' ) {
        $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/css/form-wizard-component.scss' );
    }

    $GLOBALS['TL_CSS']['form-manager-bundle'] = $objFormCssCombiner->getCombinedFile();
}