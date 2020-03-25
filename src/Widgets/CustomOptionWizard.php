<?php

namespace Alnv\ContaoFormManagerBundle\Widgets;

class CustomOptionWizard extends \Widget {

    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';

    public function generate() {

        \DataContainer::loadDataContainer($this->strTable);
        \System::loadLanguageFile($this->strTable);
        $arrAttributes = $this->getAttributesFromDca($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strName],$this->strName, null, $this->strName, $this->strTable, null);

        return '<div class="v-component"><custom-option-wizard-field :no-label="true" :value="[]" :eval="'. htmlspecialchars(json_encode($arrAttributes),ENT_QUOTES,'UTF-8') .'" name="'.$this->strName.'"></custom-option-wizard-field></div>';
    }
}