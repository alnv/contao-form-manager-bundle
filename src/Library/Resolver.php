<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;

abstract class Resolver extends \System {

    protected $blnValidate = false;
    protected $strRedirect = null;
    protected $blnSuccess = true;

    abstract public function getForm();
    abstract protected function saveRecord( $arrForm );

    public function parseAttributes( $arrFieldAttributes ) {

        $arrFieldAttributes['messages'] = [];
        $arrFieldAttributes['validate'] = true;
        $strClass = Toolkit::convertBackendFieldToFrontendField( $arrFieldAttributes['type'] );

        if ( !class_exists( $strClass ) ) {

            return null;
        }

        if ( isset( $GLOBALS['TL_HOOKS']['preCompileFormField'] ) && is_array( $GLOBALS['TL_HOOKS']['preCompileFormField'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['preCompileFormField'] as $arrCallback ) {

                $this->import( $arrCallback[0] );
                $arrFieldAttributes = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrFieldAttributes, $this );
            }
        }

        $objField = new $strClass( $arrFieldAttributes );

        if ( $this->blnValidate ) {

            $objField->validate();

            if ( $objField->hasErrors() ) {

                $this->blnSuccess = false;
                $arrFieldAttributes['validate'] = false;
                $arrFieldAttributes['messages'] = $objField->getErrors();
            }
        }

        foreach ( $arrFieldAttributes as $strFieldname => $strValue ) {

            $arrFieldAttributes[ $strFieldname ] = $objField->{$strFieldname};
        }

        $arrFieldAttributes['isReactive'] = $this->isReactive( $arrFieldAttributes );
        $arrFieldAttributes['postValue'] = \Input::post( $arrFieldAttributes['name'] );
        $arrFieldAttributes['label'] = \Controller::replaceInsertTags( $arrFieldAttributes['label'] );
        $arrFieldAttributes['component'] = Toolkit::convertTypeToComponent( $arrFieldAttributes['type'], $arrFieldAttributes['rgxp'] );
        $arrFieldAttributes['multiple'] = Toolkit::convertMultiple( $arrFieldAttributes['multiple'], $arrFieldAttributes );
        $arrFieldAttributes['value'] = Toolkit::convertValue( $arrFieldAttributes['value'], $arrFieldAttributes );
        $arrFieldAttributes['labelValue'] = Toolkit::getLabelValue( $arrFieldAttributes['value'], $arrFieldAttributes );

        if ( in_array( $arrFieldAttributes['type'], ['checkbox'] ) && $arrFieldAttributes['multiple'] === false ) {

            $arrFieldAttributes['options'][0]['label'] = $arrFieldAttributes['label'];
        }

        if ( in_array( $arrFieldAttributes['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

            $arrFieldAttributes['dateFormat'] = \Date::getFormatFromRgxp( $arrFieldAttributes['rgxp'] );
        }

        if ( isset( $GLOBALS['TL_HOOKS']['compileFormField'] ) && is_array( $GLOBALS['TL_HOOKS']['compileFormField'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['compileFormField'] as $arrCallback ) {

                $this->import( $arrCallback[0] );
                $arrFieldAttributes = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrFieldAttributes, $this );
            }
        }

        return $arrFieldAttributes;
    }

    protected function isReactive( $arrField ) {

        if ( in_array( $arrField['type'], [ 'select', 'radio', 'checkbox', 'nouislider' ] ) ) {

            return true;
        }

        return $arrField['isReactive'] ? true : false;
    }

    public function save( $blnValidateOnly = false ) {

        $this->blnValidate = true;
        $arrForm = $this->getForm();
        $objPermission = new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions();
        if (!$objPermission->hasCredentials($this->strTable)) {
            $this->blnSuccess = false;
        }

        if ( $this->blnSuccess && !$blnValidateOnly ) {
            $this->saveRecord( $arrForm );
        }

        return [

            'form' => $arrForm,
            'saved' => !$blnValidateOnly,
            'success' => $this->blnSuccess,
            'redirect' => $this->strRedirect
        ];
    }

    public function validate() {

        return $this->save(true);
    }
}