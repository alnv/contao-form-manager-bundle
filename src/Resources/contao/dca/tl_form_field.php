<?php

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['text'] .= ';{conditional_legend},conditions;';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['radio'] .= ';{conditional_legend},conditions;';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['select'] .= ';{conditional_legend},conditions;';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['checkbox'] .= ';{conditional_legend},conditions;';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['textarea'] .= ';{conditional_legend},conditions;';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['fieldsetStop'] .= ';{conditional_legend},conditions;';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['fieldsetStart'] .= ';{conditional_legend},conditions;';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['conditions'] = [
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'clr',
        'allowHtml' => true
    ],
    'exclude' => true,
    'sql' => ['type' => 'blob', 'notnull' => false ]
];