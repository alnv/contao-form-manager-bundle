<?php

namespace Alnv\ContaoFormManagerBundle\Forms;


class FormWizard extends \Alnv\ContaoFormManagerBundle\Hybrids\FormWidget {

    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'form_wizard';
    protected $strPrefix = 'widget widget-form_wizard';
}