<?php

namespace Alnv\ContaoFormManagerBundle\Widgets;

class CustomOptionWizard extends \CheckBoxWizard {

    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';

    public function generate() {

        \DataContainer::loadDataContainer($this->strTable);
        \System::loadLanguageFile($this->strTable);

        $arrAttributes = $this->getAttributesFromDca($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strName], $this->strName, null, $this->strName, $this->strTable, null);
        $arrAttributes['_table'] = $this->strTable;

        if (empty($this->varValue)) {
            $this->varValue = [];
        }

        if (is_string($this->varValue)) {
            $this->varValue = explode(',', $this->varValue);
        }

        $strValue = htmlspecialchars(json_encode($this->varValue),ENT_QUOTES,'UTF-8');

        return '<div class="v-component"><custom-option-wizard-field :no-label="true" :value="'.$strValue.'" :eval="'. htmlspecialchars(json_encode($arrAttributes),ENT_QUOTES,'UTF-8') .'" name="'.$this->strName.'"></custom-option-wizard-field></div>';
    }
}