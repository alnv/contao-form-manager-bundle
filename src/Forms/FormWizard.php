<?php

namespace Alnv\ContaoFormManagerBundle\Forms;

use Alnv\ContaoFormManagerBundle\Hybrids\FormWidget;

class FormWizard extends FormWidget
{

    protected $blnSubmitInput = true;

    protected $blnForAttribute = true;

    protected $strTemplate = 'form_wizard';

    protected $strPrefix = 'widget widget-form_wizard';
}