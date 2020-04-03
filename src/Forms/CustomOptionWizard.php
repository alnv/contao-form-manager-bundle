<?php

namespace Alnv\ContaoFormManagerBundle\Forms;

class CustomOptionWizard extends \CheckBoxWizard {

    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'form_custom-option-wizard';
    protected $strPrefix = 'widget widget-custom-option-wizard';
}