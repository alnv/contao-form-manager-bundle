<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class DcaFormResolver {


    protected $arrOptions = [];
    protected $strTable = null;
    protected $arrPalette = [];


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

        \Controller::loadDataContainer( $strTable );
        \System::loadLanguageFile( $strTable, $arrOptions['language'] );
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
            $objPalette->label = '';
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

        $strClass = $GLOBALS['TL_FFL'][ $arrField['inputType'] ];

        if ( !class_exists( $strClass ) ) {

            return null;
        }

        if ( $strFieldname == 'type' ) {

            $arrField['default'] = $this->arrOptions['type'];
        }

        $arrAttributes = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
        $arrAttributes['component'] = Toolkit::convertTypeToComponent( $arrAttributes['type'], $arrAttributes['rgxp'] );
        $arrAttributes['default'] = $arrField['default'];

        return $arrAttributes;
    }
}