<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;

abstract class Resolver extends \System {

    protected $strErrorMessage = '';
    protected $blnValidate = false;
    protected $strRedirect = null;
    public $blnSuccess = true;

    abstract public function getForm();
    abstract protected function saveRecord( $arrForm );

    public function setErrorMessage($strMessage) {
        $this->strErrorMessage = $strMessage;
    }

    public function getErrorMessage() {
        return $this->strErrorMessage;
    }

    public function parseAttributes( $arrFieldAttributes ) {

        $arrFieldAttributes['messages'] = [];
        $arrFieldAttributes['validate'] = true;
        $strClass = Toolkit::convertBackendFieldToFrontendField($arrFieldAttributes['type']);

        if (!class_exists($strClass)) {
            return null;
        }

        if (isset($GLOBALS['TL_HOOKS']['preCompileFormField']) && is_array($GLOBALS['TL_HOOKS']['preCompileFormField'])) {
            foreach ($GLOBALS['TL_HOOKS']['preCompileFormField'] as $arrCallback) {
                $this->import($arrCallback[0]);
                $arrFieldAttributes = $this->{$arrCallback[0]}->{$arrCallback[1]}($arrFieldAttributes, $this);
            }
        }

        $objField = new $strClass($arrFieldAttributes);

        if ($this->shouldValidate()) {

            $objField->validate();

            if ($objField->hasErrors()) {
                $this->blnSuccess = false;
                $arrFieldAttributes['validate'] = false;
                $arrFieldAttributes['messages'] = array_map(function ($strError){
                    return \Controller::replaceInsertTags(\StringUtil::decodeEntities($strError), false);
                }, $objField->getErrors());
            }

            if ($arrImplements = class_implements($objField)) {
                if (in_array('uploadable', $arrImplements)) {
                    if ($objField->mandatory) {
                        if (!empty(\Input::post($arrFieldAttributes['name']))) {
                            $this->blnSuccess = true;
                            $arrFieldAttributes['messages'] = [];
                            $arrFieldAttributes['validate'] = true;
                        }
                    }
                }
            }
        }

        foreach ($arrFieldAttributes as $strFieldname => $strValue) {
            if (in_array($strFieldname, ['validate', 'messages'])) {
                continue;
            }
            $arrFieldAttributes[$strFieldname] = $objField->{$strFieldname};
        }

        $arrFieldAttributes['label'] = \StringUtil::decodeEntities($arrFieldAttributes['label']);
        $arrFieldAttributes['label'] = \Controller::replaceInsertTags($arrFieldAttributes['label'], false);
        $arrFieldAttributes['isReactive'] = $this->isReactive($arrFieldAttributes);
        $arrFieldAttributes['postValue'] = \Input::post($arrFieldAttributes['name']);
        $arrFieldAttributes['component'] = Toolkit::convertTypeToComponent($arrFieldAttributes['type'], $arrFieldAttributes['rgxp']);
        $arrFieldAttributes['multiple'] = Toolkit::convertMultiple($arrFieldAttributes['multiple'], $arrFieldAttributes);
        $arrFieldAttributes['value'] = Toolkit::convertValue($arrFieldAttributes['value'], $arrFieldAttributes);
        $arrFieldAttributes['labelValue'] = Toolkit::getLabelValue($arrFieldAttributes['value'], $arrFieldAttributes);

        if ( in_array( $arrFieldAttributes['type'], ['checkbox'] ) && $arrFieldAttributes['multiple'] === false ) {
            $arrFieldAttributes['options'][0]['label'] = $arrFieldAttributes['label'];
        }

        if (in_array( $arrFieldAttributes['rgxp'], ['date', 'time', 'datim' ])) {
            $arrFieldAttributes['dateFormat'] = \Date::getFormatFromRgxp($arrFieldAttributes['rgxp']);
        }

        if (isset( $GLOBALS['TL_HOOKS']['compileFormField'] ) && is_array( $GLOBALS['TL_HOOKS']['compileFormField'] )) {
            foreach ($GLOBALS['TL_HOOKS']['compileFormField'] as $arrCallback) {
                $this->import( $arrCallback[0] );
                $arrFieldAttributes = $this->{$arrCallback[0]}->{$arrCallback[1]}($arrFieldAttributes, $this) ;
            }
        }

        return $arrFieldAttributes;
    }

    public function shouldValidate() {

        return $this->blnValidate;
    }

    protected function isReactive( $arrField ) {

        if ( in_array( $arrField['type'], [ 'select', 'radio', 'checkbox', 'nouislider' ] ) ) {
            return true;
        }

        return $arrField['isReactive'] ? true : false;
    }

    public function save($blnValidateOnly = false) {

        $this->blnValidate = true;
        $arrForm = $this->getForm();
        $objPermission = new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions();
        if (!$objPermission->hasCredentials($this->strTable)) {
            $this->blnSuccess = false;
        }

        if (isset($GLOBALS['TL_HOOKS']['formResolverBeforeSave']) && is_array($GLOBALS['TL_HOOKS']['formResolverBeforeSave'])) {
            foreach ($GLOBALS['TL_HOOKS']['formResolverBeforeSave'] as $arrCallback) {
                $this->import($arrCallback[0]);
                $this->blnSuccess = $this->{$arrCallback[0]}->{$arrCallback[1]}($this->blnSuccess, $arrForm, $blnValidateOnly, $this);
            }
        }

        if ( $this->blnSuccess && !$blnValidateOnly ) {
            $this->saveRecord($arrForm);
        }

        return [
            'form' => $arrForm,
            'id' => \Input::post('id'),
            'saved' => !$blnValidateOnly,
            'success' => $this->blnSuccess,
            'redirect' => $this->strRedirect,
            'message' => $this->getErrorMessage()
        ];
    }

    public function validate() {

        return $this->save(true);
    }
}