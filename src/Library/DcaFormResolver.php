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
        \Controller::loadDataContainer( $strTable );
        \System::loadLanguageFile( $strTable, $arrOptions['language'] );
    }


    public function getForm() {

        $arrForm = [

            'palettes' => [],
            'subPalettes' => []
        ];

        if ( !$GLOBALS['TL_DCA'][ $this->strTable ] ) {

            return $arrForm;
        }

        if ( !$this->arrOptions['subPalettes'] ) {

            $this->arrOptions['subPalettes'] = [];
        }

        $arrSubSelectors = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'];

        if ( !empty( $arrSubSelectors ) && is_array( $arrSubSelectors ) ) {

            foreach ( $arrSubSelectors as $strFieldname ) {

                if ( $strFieldname == 'type' ) {

                    // set default only on initialize
                    if ( !isset( $this->arrOptions['type'] ) ) {

                        $this->arrOptions['type'] = $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strFieldname ]['default'];
                    }

                    continue;
                }

                if ( !in_array( $strFieldname, $this->arrOptions['subPalettes'] ) && $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strFieldname ]['default'] ) {

                    $this->arrOptions['subPalettes'][] = [ $strFieldname, $strFieldname . '_' . $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strFieldname ]['default'] ];
                }
            }
        }

        $this->extractPalette();
        $arrForm['palettes'] = $this->getPalette();
        $arrForm['subPalettes'] = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'];

        return $arrForm;
    }


    protected function getPalette() {

        $arrPalettes = [];

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

                $objPalette->fields[ $strFieldname ] = $arrAttributes;
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

        $arrAttributes = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], $strFieldname, $this->strTable );
        $arrAttributes['component'] = Toolkit::convertTypeToComponent( $arrAttributes['type'], $arrAttributes['rgxp'] );

        return $arrAttributes;
    }


    protected function extractPalette() {

        $arrSubPalettes = [];
        $strType = $this->arrOptions['type'] ?: 'default';

        if ( is_array( $this->arrOptions['subPalettes'] ) && !empty( $this->arrOptions['subPalettes'] ) ) {

            foreach ( $this->arrOptions['subPalettes'] as $arrSubPalettesNames ) {

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