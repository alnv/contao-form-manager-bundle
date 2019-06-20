<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class DcaFormResolver {


    protected $strTable = null;
    protected $arrPalette = [];


    public function __construct( $strTable, $arrOptions = [] ) {

        $this->strTable = $strTable;
        $this->setOptions( $arrOptions );
    }


    public function getPalette() {

        $arrPalettes = [];

        foreach ( $this->arrPalette as $strPalette => $arrPalette ) {

            $objPalette = new \stdClass();
            $objPalette->label = '';
            $objPalette->fields = [];
            $objPalette->name = $strPalette ?: '';
            $objPalette->hide = $arrPalette['blnHide'];

            foreach ( $arrPalette['arrFields'] as $arrFieldname ) {

                $arrAttributes = $this->parseAttributes( $arrFieldname, $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $arrFieldname ] );

                if ( !$arrAttributes ) {

                    continue;
                }

                $arrAttributes['component'] = Toolkit::convertTypeToComponent( $arrAttributes['type'], $arrAttributes['rgxp'] );
                $objPalette->fields[ $arrFieldname ] = $arrAttributes;
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

        return $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
    }


    protected function setOptions( $arrOptions ) {

        \Controller::loadDataContainer( $this->strTable );

        if ( !isset( $GLOBALS['TL_DCA'][ $this->strTable ] ) ) {

            return null;
        }

        $this->arrPalette = Toolkit::extractPaletteToArray( $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['default'], $arrOptions['fields'] );
    }
}