<?php

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['text'] .= ';{filter_legend:hide},isReactive;';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['conditions'] = [
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'clr',
        'allowHtml' => true
    ],
    'exclude' => true,
    'sql' => ['type' => 'blob', 'notnull' => false ]
];
$GLOBALS['TL_DCA']['tl_form_field']['fields']['isReactive'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'multiple' => false
    ],
    'exclude' => true,
    'sql' => [ 'type' => 'string', 'length' => 1, 'fixed' => true, 'default' => '' ]
];