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


    public function getForm() {

        return [

            'palettes' => $this->getPalette(),
            'subPalettes' => $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'] ?: []
        ];
    }


    protected function getPalette() {

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

        \System::loadLanguageFile( $this->strTable );
        \Controller::loadDataContainer( $this->strTable );

        if ( !isset( $GLOBALS['TL_DCA'][ $this->strTable ] ) ) {

            return null;
        }

        $arrSubPalettes = [];
        $strType = $arrOptions['type'] ?: 'default';

        if ( is_array( $arrOptions['subPalettes'] ) && !empty( $arrOptions['subPalettes'] ) ) {

            foreach ( $arrOptions['subPalettes'] as $arrSubPalettesNames ) {

                foreach ( $arrSubPalettesNames as $strSubPalette ) {

                    if ( isset( $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strSubPalette ] ) )  {

                        $arrSubPalettes[ $strSubPalette ] = $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strSubPalette ];
                    }
                }
            }
        }

        $this->arrPalette = Toolkit::extractPaletteToArray( $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $strType ], $arrSubPalettes );
    }
}