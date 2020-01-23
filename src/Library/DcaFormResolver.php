<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class DcaFormResolver extends \System {


    protected $arrOptions = [];
    protected $strTable = null;
    protected $arrPalette = [];
    protected $strRedirect = '';
    protected $blnSuccess = true;
    protected $blnValidate = false;


    public function __construct( $strTable, $arrOptions = [] ) {

        $this->strTable = $strTable;
        $this->arrOptions = $arrOptions;

        /* debug
        $this->strTable = 'tl_catalog';
        $this->arrOptions = [
            'type' => '',
            'initialized' => false,
            'subpalettes' => [ 'mode::flex', 'showColumns::' ],
        ];
        */

        \System::loadLanguageFile('default');
        \Controller::loadDataContainer( $strTable );
        \System::loadLanguageFile( $strTable, $arrOptions['language'] );
    }


    public function getWizard() {

        $this->arrOptions['type'] = null;

        if ( !$GLOBALS['TL_DCA'][ $this->strTable ] ) {

            return null;
        }

        $objPalette = new \stdClass();
        $objPalette->label = '';
        $objPalette->fields = [];
        $objPalette->hide = false;
        $objPalette->name = $this->arrOptions['wizard'];

        $arrFields = $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $this->arrOptions['wizard'] ]['eval']['form'] ?: [];

        foreach ( $arrFields as $strFieldname => $arrField ) {

            $arrAttributes = $this->parseAttributes( $strFieldname, $arrField );

            if ( !$arrAttributes ) {

                continue;
            }

            // $arrAttributes['name'] = $objPalette->name . '.' . $strFieldname;
            $objPalette->fields[] = $arrAttributes;
        }

        return [ $objPalette ];
    }


    public function getForm() {

        if ( !$GLOBALS['TL_DCA'][ $this->strTable ] ) {

            return null;
        }

        if ( !$this->arrOptions['initialized'] ) {

            $this->arrOptions['type'] = $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['type']['default'];
        }

        if ( !$this->arrOptions['type'] ) {

            $this->arrOptions['type'] = 'default';
        }

        $strPalette = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $this->arrOptions['type'] ];
        $arrActiveSelectors = $this->getActiveSelector();
        $this->arrPalette = Toolkit::extractPaletteToArray( $strPalette, $arrActiveSelectors );

        return $this->setFields();
    }


    protected function getActiveSelector() {

        $arrSubpalettes = [];

        if ( is_array( $this->arrOptions['subpalettes'] ) && !empty( $this->arrOptions['subpalettes'] ) ) {

            foreach ( $this->arrOptions['subpalettes'] as $strSelector ) {

                list( $strFieldname, $strValue ) = explode( '::', $strSelector );

                if ( $strValue == '1' ) {

                    $strValue = '';
                }

                $strPalette = $strFieldname . ( $strValue ? '_' . $strValue : '' ); $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strFieldname . ( $strValue ? '_' . $strValue : '' ) ];

                if ( isset( $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strPalette ] ) ) {

                    $arrSubpalettes[ $strPalette ] = $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strPalette ];
                }
            }
        }

        return $arrSubpalettes;
    }


    protected function setFields() {

        $arrPalettes = [];
        $arrSelectors = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'];

        foreach ( $this->arrPalette as $strPalette => $arrPalette ) {

            $objPalette = new \stdClass();
            $objPalette->label = $GLOBALS['TL_LANG'][ $this->strTable ][$strPalette];
            $objPalette->fields = [];
            $objPalette->name = $strPalette ?: '';
            $objPalette->hide = $arrPalette['blnHide'];

            foreach ( $arrPalette['arrFields'] as $strFieldname ) {

                $arrAttributes = $this->parseAttributes( $strFieldname, $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strFieldname ] );

                if ( !$arrAttributes ) {

                    continue;
                }

                if ( is_array( $arrSelectors ) && in_array( $strFieldname, $arrSelectors ) && $strFieldname != 'type' ) {

                    $arrAttributes['isSelector'] = true;
                }

                $objPalette->fields[] = $arrAttributes;
            }

            $arrPalettes[] = $objPalette;
        }

        return $arrPalettes;
    }


    protected function parseAttributes( $strFieldname, $arrField ) {

        $arrReturn = [];
        $strClass = $GLOBALS['TL_FFL'][ $arrField['inputType'] ];

        if ( !class_exists( $strClass ) ) {

            return null;
        }

        if ( $strFieldname == 'type' ) {

            $arrField['default'] = $this->arrOptions['type'];
        }

        $arrAttributes = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
        $objField = new $strClass( $arrAttributes );

        if ( $this->blnValidate ) {

            $objField->validate();

            if ( $objField->hasErrors() ) {

                $this->blnSuccess = false;
                $arrReturn['validate'] = false;
                $arrReturn['messages'] = $objField->getErrors();
            }
        }

        foreach ( $arrAttributes as $strFieldname => $strValue ) {

            $arrReturn[ $strFieldname ] = $objField->{$strFieldname};
        }

        $arrReturn['label'] = \Controller::replaceInsertTags( $arrReturn['label'] );
        $arrReturn['component'] = Toolkit::convertTypeToComponent( $arrReturn['type'], $arrReturn['rgxp'] );
        $arrReturn['value'] = Toolkit::convertValue( $arrReturn['value'], $arrReturn );
        $arrReturn['labelValue'] = Toolkit::getLabelValue( $arrReturn['value'], $arrReturn );
        $arrReturn['default'] = $arrField['default'];

        return $arrReturn;
    }


    public function save( $blnValidateOnly = false ) {

        $this->blnValidate = true;
        $arrForm = $this->getForm();

        if ( $this->blnSuccess && !$blnValidateOnly ) {

            $this->saveEntry( $arrForm );
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


    protected function saveEntry( $arrForm ) {

        $arrSubmitted = [];

        foreach ( $arrForm as $objPalette ) {

            foreach ( $objPalette->fields as $arrField ) {

                $arrSubmitted[ $arrField['name'] ] = $arrField['value'];
            }
        }

        $arrSubmitted['tstamp'] = time();

        if ( isset( $GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] ) && is_array($GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['prepareDataBeforeSave'] as $arrCallback ) {

                $this->import( $arrCallback[0] );
                $this->{ $arrCallback[0] }->{ $arrCallback[1] }( $arrSubmitted, $arrForm, $this->arrOptions, $this );
            }
        }

        if ( isset( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['executeOnSave'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['executeOnSave'] ) ) {

            foreach ( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['executeOnSave'] as $arrCallback ) {

                $this->import( $arrCallback[0] );
                $this->strRedirect = $this->{ $arrCallback[0] }->{ $arrCallback[1] }( $arrSubmitted, false, $this->arrOptions, $this );
            }

            return null;
        }

        $objDatabase = \Database::getInstance();
        $objDatabase->prepare('INSERT INTO '. $this->strTable .' %s')->set( $arrSubmitted )->execute();
    }
}