<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;
use Alnv\ContaoTranslationManagerBundle\Library\Translation;
use Contao\Date;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;

abstract class Resolver
{

    protected $strTable;

    protected $strErrorMessage = '';

    protected $blnValidate = false;

    protected $strRedirect = null;

    public $blnSuccess = true;

    abstract public function getForm();

    abstract protected function saveRecord($arrForm);

    public function setErrorMessage($strMessage)
    {
        $this->strErrorMessage = $strMessage;
    }

    public function getErrorMessage()
    {
        return $this->strErrorMessage;
    }

    public function parseAttributes($arrFieldAttributes)
    {

        $arrFieldAttributes['messages'] = [];
        $arrFieldAttributes['validate'] = true;
        $strClass = Toolkit::convertBackendFieldToFrontendField($arrFieldAttributes['type']);

        if (!class_exists($strClass)) {
            return null;
        }

        if (isset($GLOBALS['TL_HOOKS']['preCompileFormField']) && is_array($GLOBALS['TL_HOOKS']['preCompileFormField'])) {
            foreach ($GLOBALS['TL_HOOKS']['preCompileFormField'] as $arrCallback) {
                $arrFieldAttributes = System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrFieldAttributes, $this);
            }
        }

        if ($arrFieldAttributes['multiple'] === true && is_array(Input::post($arrFieldAttributes['name']))) {
            $arrValues = Input::post($arrFieldAttributes['name']);
            $arrReducedValues = [];
            $blnAssoc = false;
            foreach ($arrValues as $varValue) {
                if (is_array($varValue) && isset($varValue['value'])) {
                    $arrReducedValues[] = $varValue['value'];
                    $blnAssoc = true;
                }
            }
            if ($blnAssoc) {
                Input::setPost($arrFieldAttributes['name'], $arrReducedValues);
            }
        }

        $objField = new $strClass($arrFieldAttributes);

        if ($this->shouldValidate()) {

            $objField->validate();

            if ($objField->hasErrors()) {
                $arrFieldAttributes['validate'] = false;
                $arrFieldAttributes['messages'] = array_map(function ($strError) {
                    return Toolkit::replaceInsertTags(StringUtil::decodeEntities($strError), false);
                }, $objField->getErrors());
            }

            if ($arrImplements = class_implements($objField)) {
                if (\in_array('uploadable', $arrImplements)) {
                    if ($objField->mandatory) {
                        if (!empty(Input::post($arrFieldAttributes['name']))) {
                            $arrFieldAttributes['messages'] = [];
                            $arrFieldAttributes['validate'] = true;
                        }
                    }
                }
            }

            if (!$arrFieldAttributes['validate']) {
                $this->blnSuccess = false;
            }
        }

        foreach ($arrFieldAttributes as $strFieldname => $strValue) {
            if (\in_array($strFieldname, ['validate', 'messages', 'name'])) {
                continue;
            }
            $arrFieldAttributes[$strFieldname] = $objField->{$strFieldname};
        }

        $strLabel = Translation::getInstance()->translate(('field.' . ($this->strTable ? $this->strTable . '.' : '') . $arrFieldAttributes['name']), $arrFieldAttributes['label']);
        $strDescription = Translation::getInstance()->translate(('field.' . ($this->strTable ? $this->strTable . '.' : '') . 'description.' . $arrFieldAttributes['name']), $arrFieldAttributes['description']);

        $arrFieldAttributes['label'] = $this->parseString($strLabel);
        $arrFieldAttributes['isReactive'] = $this->isReactive($arrFieldAttributes);
        $arrFieldAttributes['text'] = $this->parseString($arrFieldAttributes['text']);
        $arrFieldAttributes['description'] = $this->parseString($strDescription);
        $arrFieldAttributes['postValue'] = Input::post($arrFieldAttributes['name']);
        $arrFieldAttributes['blankOptionLabel'] = Translation::getInstance()->translate(('field.' . ($this->strTable ? $this->strTable . '.' : '') . 'blankOptionLabel.' . $arrFieldAttributes['name']), $arrFieldAttributes['blankOptionLabel']);
        $arrFieldAttributes['component'] = Toolkit::convertTypeToComponent($arrFieldAttributes['type'], $arrFieldAttributes['rgxp']);
        $arrFieldAttributes['multiple'] = Toolkit::convertMultiple($arrFieldAttributes['multiple'], $arrFieldAttributes);
        $arrFieldAttributes['value'] = Toolkit::convertValue($arrFieldAttributes['value'], $arrFieldAttributes);
        $arrFieldAttributes['labelValue'] = Toolkit::getLabelValue($arrFieldAttributes['value'], $arrFieldAttributes);

        if (\in_array($arrFieldAttributes['type'], ['checkbox'])) {
            if ($arrFieldAttributes['multiple'] === false) {
                $arrFieldAttributes['options'][0]['label'] = $arrFieldAttributes['label'];
            }
            $strSelectAll = Translation::getInstance()->translate(('field.' . ($this->strTable ? $this->strTable . '.' : '') . $arrFieldAttributes['name'] . '.selectAll'), '');
            $arrFieldAttributes['selectAllLabel'] = $this->parseString($strSelectAll);
        }

        if (\in_array($arrFieldAttributes['rgxp'], ['date', 'time', 'datim'])) {
            $arrFieldAttributes['dateFormat'] = Date::getFormatFromRgxp($arrFieldAttributes['rgxp']);
        }

        if (isset($GLOBALS['TL_HOOKS']['compileFormField']) && is_array($GLOBALS['TL_HOOKS']['compileFormField'])) {
            foreach ($GLOBALS['TL_HOOKS']['compileFormField'] as $arrCallback) {
                $arrFieldAttributes = System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrFieldAttributes, $this);
            }
        }

        return $arrFieldAttributes;
    }

    protected function parseString($strString)
    {

        if (!is_string($strString)) {
            return $strString;
        }

        $strString = StringUtil::decodeEntities($strString);
        $strString = Toolkit::replaceInsertTags($strString, false);

        return $strString;
    }

    public function shouldValidate()
    {

        return $this->blnValidate;
    }

    protected function isReactive($arrField): bool
    {

        if (in_array($arrField['type'], ['select', 'radio', 'checkbox', 'nouislider'])) {
            return true;
        }

        return (bool)$arrField['isReactive'];
    }

    public function save($blnValidateOnly = false)
    {

        $this->blnValidate = true;
        $arrForm = $this->getForm();
        $objPermission = new MemberPermissions();
        if (!$objPermission->hasCredentials($this->strTable)) {
            $this->blnSuccess = false;
        }

        if (isset($GLOBALS['TL_HOOKS']['formResolverBeforeSave']) && is_array($GLOBALS['TL_HOOKS']['formResolverBeforeSave'])) {
            foreach ($GLOBALS['TL_HOOKS']['formResolverBeforeSave'] as $arrCallback) {
                $this->blnSuccess = System::importStatic($arrCallback[0])->{$arrCallback[1]}($this->blnSuccess, $arrForm, $blnValidateOnly, $this);
            }
        }

        if ($this->blnSuccess && !$blnValidateOnly) {
            $this->saveRecord($arrForm);
        }

        return [
            'form' => $arrForm,
            'saved' => !$blnValidateOnly,
            'success' => $this->blnSuccess,
            'id' => Input::post('id'),
            'redirect' => $this->strRedirect,
            'message' => $this->getErrorMessage()
        ];
    }

    public function validate()
    {

        return $this->save(true);
    }
}