<?php

namespace Alnv\ContaoFormManagerBundle\DataContainer;

class FormField {

    public function getFields() {

        $arrFields = (new \tl_form_field())->getFields();
        $arrDisabled = ['formWizard', 'customOptionWizard', 'multiDatesWizard', 'ajaxSelect', 'nouislider'];
        foreach ($arrDisabled as $strField) {
            if (in_array($strField, $GLOBALS['IGNORE_IN_TL_FFL'])) {
                continue;
            }
            if (($intIndex = array_search($strField, $arrFields)) !== false) {
                unset($arrFields[$intIndex]);
            }
        }

        return $arrFields;
    }
}