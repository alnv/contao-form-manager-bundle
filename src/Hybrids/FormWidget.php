<?php

namespace Alnv\ContaoFormManagerBundle\Hybrids;

class FormWidget extends \Widget
{

    public function validate()
    {

        $varValues = $this->getPost($this->strName);

        if (is_string($varValues)) {
            $varValues = json_decode(\StringUtil::decodeEntities($varValues), true);
        }

        if ($this->readOnly) {
            $objEntity = \Database::getInstance()->prepare('SELECT * FROM ' . $this->strTable . ' WHERE id=?')->execute(\Input::get('id'));
            $varValues = \StringUtil::deserialize($objEntity->{$this->id}, true);
            return $varValues;
        }

        if ($this->mandatory && !$this->hasValue($varValues)) {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
        }

        \Input::setGet('params', $this->getParams());
        $arrFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strName]['eval']['form'];

        if (is_array($varValues) && !empty($varValues)) {
            foreach ($varValues as $strIndex => $arrValue) {
                foreach ($arrValue as $strFieldname => $strValue) {

                    $strPost = $this->strName . '_' . $strIndex . '_' . $strFieldname;
                    $strClass = $GLOBALS['TL_FFL'][$arrFields[$strFieldname]['inputType']];

                    if (!class_exists($strClass)) {
                        continue;
                    }

                    \Input::setPost($strPost, $strValue);

                    if (is_string($strValue) && $strValue && (\Validator::isDate($strValue) || \Validator::isDatim($strValue))) {
                        $strValue = (new \Date($strValue, ($arrFields[$strFieldname]['eval']['dateFormat'] ?? 'd.m.Y')))->tstamp;
                    }

                    $varValues[$strIndex][$strFieldname] = $strValue;
                    $arrFields[$strFieldname]['value'] = $strValue;
                    $arrAttributes = $strClass::getAttributesFromDca($arrFields[$strFieldname], $strPost, ($arrFields[$strFieldname]['default'] ?? ''), $strPost, $this->strTable);
                    $arrAttributes['name'] = $strPost;
                    $objField = new $strClass($arrAttributes);
                    $objField->validate();

                    if ($objField->hasErrors()) {
                        $arrErrors = $objField->getErrors();
                        if (is_array($arrErrors) && !empty($arrErrors)) {
                            foreach ($arrErrors as $strError) {
                                $this->addError($strError);
                            }
                        }
                    }

                    if (isset($GLOBALS['TL_HOOKS']['validateFormWidgetField']) && is_array($GLOBALS['TL_HOOKS']['validateFormWidgetField'])) {
                        foreach ($GLOBALS['TL_HOOKS']['validateFormWidgetField'] as $arrCallback) {
                            $this->import($arrCallback[0]);
                            $this->{$arrCallback[0]}->{$arrCallback[1]}($strPost, $arrAttributes, $objField, $this);
                        }
                    }
                }
            }
        }

        $this->varValue = $varValues;
    }

    protected function getValues()
    {

        $this->varValue = \StringUtil::deserialize($this->varValue, true);

        if (empty($this->varValue)) {
            return $this->varValue;
        }

        $arrFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strName]['eval']['form'];
        foreach ($this->varValue as $strIndex => $arrValue) {
            foreach ($arrValue as $strFieldname => $strValue) {
                $this->varValue[$strIndex][$strFieldname] = $this->parseValue($strValue, $arrFields[$strFieldname]);
            }
        }

        return $this->varValue;
    }

    protected function parseValue($strValue, $arrField)
    {

        if (!is_array($strValue) && \Validator::isNumeric($strValue) && in_array($arrField['eval']['rgxp'], ['date', 'time', 'datim'])) {
            $strValue = (new \Date($strValue, \Date::getFormatFromRgxp($arrField['eval']['rgxp'])))->{$arrField['eval']['rgxp']};
        }

        return $strValue;
    }

    protected function getParams()
    {

        return [
            'do' => \Input::get('do'),
            'id' => \Input::get('id'),
            'act' => \Input::get('act'),
            'table' => \Input::get('table') ?: '',
        ];
    }

    protected function hasValue($varValues)
    {

        if (!$varValues || empty($varValues)) {
            return false;
        }

        if (!is_array($varValues)) {
            return false;
        }

        if (isset($varValues[0]) && empty($varValues[0])) {
            return false;
        }

        return true;
    }

    public function generate()
    {
    }
}