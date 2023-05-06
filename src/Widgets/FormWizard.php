<?php

namespace Alnv\ContaoFormManagerBundle\Widgets;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;
use Alnv\ContaoFormManagerBundle\Hybrids\FormWidget;

class FormWizard extends FormWidget
{

    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';

    public function generate()
    {

        if ($this->readOnly) {
            return $this->readOnly();
        }

        $arrParams = $this->getParams();
        $arrField = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->name];
        $arrAttributes = $this->getAttributesFromDca($arrField, $this->name, $this->default, $this->name, $this->strTable);
        $strEval = htmlspecialchars(json_encode($arrAttributes), ENT_QUOTES, 'UTF-8');
        $strValue = htmlspecialchars(json_encode($this->getValues()), ENT_QUOTES, 'UTF-8');
        $strParams = htmlspecialchars(json_encode($arrParams), ENT_QUOTES, 'UTF-8');

        return '<div class="v-component"><form-wizard  :value="' . $strValue . '" :eval="' . $strEval . '" :params="' . $strParams . '" name="' . $this->name . '"></form-wizard></div>';
    }

    protected function readOnly()
    {

        if (!$this->varValue) {
            return '';
        }

        if (!is_array($this->varValue) || empty($this->varValue)) {
            return '';
        }

        $strTemplate = '<p>';
        $arrFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strName]['eval']['form'];

        foreach ($this->varValue as $intIndex => $arrValue) {
            if ($intIndex) {
                $strTemplate .= '<br>';
            }
            foreach ($arrValue as $strFieldname => $strValue) {
                $strClass = $GLOBALS['TL_FFL'][$arrFields[$strFieldname]['inputType']];
                if (!class_exists($strClass)) {
                    continue;
                }
                $arrAttributes = $strClass::getAttributesFromDca($arrFields[$strFieldname], $strFieldname, $strValue, $strFieldname, $this->strTable);
                $varCleanValue = Toolkit::getLabelValue($strValue, $arrAttributes);
                if (is_array($varCleanValue)) {
                    $varCleanValue = implode(',', $varCleanValue);
                }
                $strTemplate .= $arrAttributes['label'] . ': ' . $varCleanValue . '<br>';
            }
        }

        $strTemplate .= '</p>';
        return $strTemplate;
    }
}