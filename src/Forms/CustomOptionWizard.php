<?php

namespace Alnv\ContaoFormManagerBundle\Forms;

use Contao\FormCheckbox;

class CustomOptionWizard extends FormCheckBox
{

    protected $blnSubmitInput = true;

    protected $blnForAttribute = true;

    protected $strTemplate = 'form_custom-option-wizard';

    protected $strPrefix = 'widget widget-custom-option-wizard';
}