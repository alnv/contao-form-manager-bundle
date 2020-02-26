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

        $arrField = [];
        $arrField['messages'] = [];
        $arrField['validate'] = true;
        $strClass = Toolkit::convertBackendFieldToFrontendField( $arrFieldAttributes['type'] );

        if ( !class_exists( $strClass ) ) {

            return null;
        }

        $objField = new $strClass( $arrFieldAttributes );

        if ( $this->blnValidate ) {

            $objField->validate();

            if ( $objField->hasErrors() ) {

                $this->blnSuccess = false;
                $arrField['validate'] = false;
                $arrField['messages'] = $objField->getErrors();
            }
        }

        foreach ( $arrFieldAttributes as $strFieldname => $strValue ) {

            $arrField[ $strFieldname ] = $objField->{$strFieldname};
        }

        $arrField['isReactive'] = $this->isReactive( $arrField );
        $arrField['postValue'] = \Input::post( $arrField['name'] );
        $arrField['label'] = \Controller::replaceInsertTags( $arrField['label'] );
        $arrField['component'] = Toolkit::convertTypeToComponent( $arrField['type'], $arrField['rgxp'] );
        $arrField['multiple'] = Toolkit::convertMultiple( $arrField['multiple'], $arrField );
        $arrField['value'] = Toolkit::convertValue( $arrField['value'], $arrField );
        $arrField['labelValue'] = Toolkit::getLabelValue( $arrField['value'], $arrField );
        $arrField['default'] = $arrFieldAttributes['default'];

        if ( in_array( $arrField['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

            $arrField['dateFormat'] = \Date::getFormatFromRgxp( $arrField['rgxp'] );
        }

        if ( isset( $GLOBALS['TL_HOOKS']['compileFormField'] ) && is_array( $GLOBALS['TL_HOOKS']['compileFormField'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['compileFormField'] as $arrCallback ) {

                $this->import( $arrCallback[0] );
                $arrField = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrField, $this );
            }
        }

        return $arrField;
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

        /*
        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($this->strTable,[]);
        if ( $strMemberField = $objRoleResolver->getFieldByRole('member') ) {
            $objMember = \FrontendUser::getInstance();
            if (!$objMember->id) {
                $this->blnSuccess = false;
            }
        }
        */

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