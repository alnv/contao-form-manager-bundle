<?php

array_insert( $GLOBALS['FE_MOD'], 2, [
    'form-manager-bundle' => [
        'form-manager' => 'Alnv\ContaoFormManagerBundle\Modules\FormManagerModule',
        'table-list-view' => 'Alnv\ContaoFormManagerBundle\Modules\TableListViewModule'
    ]
]);

$GLOBALS['TL_HOOKS']['parseCatalogField'][] = ['Alnv\ContaoFormManagerBundle\Hooks\CatalogField', 'parseCatalogField'];
$GLOBALS['TL_HOOKS']['parseEntity'][] = ['Alnv\ContaoFormManagerBundle\Hooks\View','parseEntity'];

$GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] = [
    'text' => [
        'default' => 'text-field',
        'datim' => 'date-field',
        'time' => 'date-field',
        'date' => 'date-field',
        'natural' => 'number-field'
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
    'explanation' => [
        'default' => 'explain-field'
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
    'customOptionWizard' => [
        'default' => 'custom-option-wizard-field'
    ],
    'nouislider' => [
        'default' => 'number-field'
    ],
    'fieldsetStart' => [
        'default' => 'fieldset-start'
    ]
];

$GLOBALS['IGNORE_IN_TL_FFL'] = [];
$GLOBALS['CM_FIELDS'][] = 'customOptionWizard';
$GLOBALS['BE_FFL']['customOptionWizard'] = 'Alnv\ContaoFormManagerBundle\Widgets\CustomOptionWizard';
$GLOBALS['TL_FFL']['customOptionWizard'] = 'Alnv\ContaoFormManagerBundle\Forms\CustomOptionWizard';
$GLOBALS['BE_FFL']['formWizard'] = 'Alnv\ContaoFormManagerBundle\Widgets\FormWizard';
$GLOBALS['TL_FFL']['formWizard'] = 'Alnv\ContaoFormManagerBundle\Forms\FormWizard';

if ( \Alnv\ContaoFormManagerBundle\Helper\Toolkit::shouldLoadVueScripts() ) {
    $objFormAssetsManager = \Alnv\ContaoAssetsManagerBundle\Library\AssetsManager::getInstance();
    $objFormAssetsManager->addIfNotExist('bundles/alnvcontaoformmanager/js/helpers/validator.js');
    $objFormAssetsManager->addIfNotExist('bundles/alnvcontaoformmanager/js/libs/flatpickr/js/flatpickr.min.js');
    $objFormAssetsManager->addIfNotExist('bundles/alnvcontaoformmanager/js/libs/dropzone/js/dropzone.min.js');
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/libs/flatpickr/js/vue-flatpickr-component.min.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/libs/flatpickr/js/de.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/single-form-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/forms/multi-form-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/lists/table-list-component.js' );
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
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/explain-field-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/modals/modal-view-component.js' );
    $objFormAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/components/fields/custom-option-wizard-field-component.js' );
    $objFormCssCombiner = new \Combiner();
    $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/js/libs/flatpickr/styles/flatpickr.min.scss' );
    $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/js/libs/dropzone/styles/basic.scss' );
    $objFormCssCombiner->add( 'bundles/alnvcontaoformmanager/js/libs/dropzone/styles/dropzone.scss' );
    if ( TL_MODE == 'BE' ) {
        $objFormCssCombiner->add('bundles/alnvcontaoformmanager/css/form-wizard-component.scss');
    }
    $objFormCssCombiner->add('bundles/alnvcontaoformmanager/css/form-manager-bundle.scss');
    $GLOBALS['TL_CSS']['form-manager-bundle'] = $objFormCssCombiner->getCombinedFile();
}

$arrFormManagerTokens = [
    'recipients' => ['admin_email', 'form_*'],
    'email_subject' => ['admin_email', 'form_*'],
    'email_text' => ['admin_email', 'form_*'],
    'email_html' => ['admin_email', 'form_*'],
    'file_name' => ['admin_email', 'form_*'],
    'file_content' => ['admin_email', 'form_*'],
    'email_sender_name' => ['admin_email', 'form_*'],
    'email_sender_address' => ['admin_email', 'form_*'],
    'email_recipient_cc' => ['admin_email', 'form_*'],
    'email_recipient_bcc' => ['admin_email', 'form_*'],
    'email_replyTo' => ['admin_email', 'form_*'],
    'attachment_tokens' => ['admin_email', 'form_*']
];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['form-manager-bundle'] = [
    'onCreate' => $arrFormManagerTokens,
    'onUpdate' => $arrFormManagerTokens,
    'onDelete' => $arrFormManagerTokens
];