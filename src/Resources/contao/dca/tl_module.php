<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'cmForm';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'cmSource';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['cmSource_dc'] = 'cmIdentifier';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['cmSource_form'] = 'cmIdentifier';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['cmForm'] = 'cmFormPage,cmFormModule';

$GLOBALS['TL_DCA']['tl_module']['palettes']['form-manager'] = '{title_legend},name,headline,type;{form_setting},cmSource,cmSuccessRedirect,cmFormHint;{template_legend:hide},customTpl;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['cmForm'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'multiple' => false,
        'tl_class' => 'clr',
        'submitOnChange' => true
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['cmFormModule'] = [
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'tl_class' => 'w50',
        'includeBlankOption'=> true
    ],
    'foreignKey' => 'tl_module.name',
    'relation' => [
        'load' => 'lazy',
        'type' => 'hasOne'
    ],
    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['cmFormPage'] = [
    'inputType' => 'pageTree',
    'eval' => [
        'tl_class' => 'w50 clr',
        'mandatory' => true
    ],
    'foreignKey' => 'tl_page.title',
    'relation' => [
        'load' => 'lazy',
        'type' => 'hasOne'
    ],
    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['cmSuccessRedirect'] = [
    'inputType' => 'pageTree',
    'eval' => [
        'tl_class' => 'w50 clr',
        'mandatory' => true
    ],
    'foreignKey' => 'tl_page.title',
    'relation' => [
        'load' => 'lazy',
        'type' => 'hasOne'
    ],
    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['cmIdentifier'] = [
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'multiple' => false,
        'tl_class' => 'w50',
        'maxlength' => 128,
        'mandatory' => true,
        'includeBlankOption'=> true
    ],
    'exclude' => true,
    'options_callback' => ['catalogmanager.datacontainer.module', 'getFormIdentifier'],
    'sql' => "varchar(128) NOT NULL default ''"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['cmSource'] = [
    'inputType' => 'radio',
    'eval' => [
        'maxlength' => 16,
        'tl_class' => 'clr',
        'mandatory' => true,
        'submitOnChange' => true
    ],
    'options' => ['dc', 'form'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['cmSource'],
    'exclude' => true,
    'sql' => "varchar(16) NOT NULL default ''"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['cmFormHint'] = [
    'inputType' => 'textarea',
    'eval' => [
        'tl_class' => 'clr',
        'rte' => 'tinyMCE'
    ],
    'exclude' => true,
    'sql' => "text NULL"
];