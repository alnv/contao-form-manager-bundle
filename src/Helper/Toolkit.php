<?php

namespace Alnv\ContaoFormManagerBundle\Helper;


class Toolkit {


    public static function convertTypeToComponent( $strType, $strRgxp = null ) {

        if ( !isset( $GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] ) && !is_array( $GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'] ) ) {

            return null;
        }

        $arrTypes = $GLOBALS['FORM_MANAGER_FIELD_COMPONENTS'][ $strType ];

        if ( !is_array( $arrTypes ) || empty( $arrTypes ) ) {

            return null;
        }

        if ( $strRgxp ) {

            return isset( $arrTypes[ $strRgxp ] ) ? $arrTypes[ $strRgxp ] : null;
        }

        return isset( $arrTypes[ 'default' ] ) ? $arrTypes[ 'default' ] : null;
    }


    public static function convertValue( $strValue, $arrField ) {

        if ( $arrField['multiple'] ) {

            if ( is_array( $strValue ) ) {

                return $strValue;
            }

            return $strValue ? [ $strValue ] : [];
        }

        return $strValue;
    }


    public static function convertMultiple( $varMultiple, $arrField ) {

        $varMultiple = ( $varMultiple || $arrField['type'] == 'checkbox' ) ? true : false;

        if ( $arrField['type'] == 'checkbox' && count( $arrField['options'] ) < 2 ) {

            $varMultiple = false;
        }

        return $varMultiple;
    }


    public static function extractPaletteToArray( $strPalette, $arrSubPalettes = [] ) {

        $arrPalette = [];

        if ( !$strPalette ) {

            return $arrPalette;
        }

        $intLegendCount = 0;
        $arrGroups = \StringUtil::trimsplit( ';', $strPalette );

        foreach ( $arrGroups as $strGroup ) {

            if ( !$strGroup ) {

                continue;
            }

            $blnHide = false;
            $arrFields = \StringUtil::trimsplit( ',', $strGroup );

            if ( preg_match('#\{(.+?)(:hide)?\}#', $arrFields[0], $arrMatches ) ) {

                //@todo add label
                $strLegend = $arrMatches[1];
                $blnHide = count( $arrMatches ) > 2 && ':hide' === $arrMatches[2];
                array_shift( $arrFields );
                $arrFields = self::pluckSubPalettes( $arrFields, $arrSubPalettes );
            }

            else {

                $strLegend = $intLegendCount++;
            }

            $arrPalette[ $strLegend ] = compact( 'arrFields', 'blnHide' );
        }

        return $arrPalette;
    }


    protected static function pluckSubPalettes( $arrFields, $arrSubPalettes ) {

        $arrReturn = [];

        foreach ( $arrFields as $strFieldname ) {

            $arrSubFields = self::findSubPaletteMatch( $strFieldname, $arrSubPalettes );
            $arrReturn[] = $strFieldname;

            if ( !empty( $arrSubFields ) ) {

                $arrReturn = array_filter( $arrReturn );
                $arrReturn = array_merge( $arrReturn, self::pluckSubPalettes( $arrSubFields, $arrSubPalettes ) );
            }
        }

        return $arrReturn;
    }


    protected static function findSubPaletteMatch( $strFieldname, &$arrSubPalettes ) {

        if ( $arrMatches = preg_grep( '/'. $strFieldname .'/', array_keys( $arrSubPalettes ) ) ) {

            if ( isset( $arrSubPalettes[ $arrMatches[0] ] ) ) {

                return \StringUtil::trimsplit( ',', $arrSubPalettes[ $arrMatches[0] ] );
            }

            if ( isset( $arrSubPalettes[ $strFieldname ] ) ) {

                return \StringUtil::trimsplit( ',', $arrSubPalettes[ $strFieldname ] );
            }
        }

        return [];
    }


    protected static function getSelectedOptions( $varValue, $arrOptions ) {

        $arrReturn = [];

        foreach ( $arrOptions as $arrValue ) {

            if ( is_array( $varValue ) && in_array( $arrValue['value'], $varValue ) ) {

                $arrReturn[] = self::parseLabelValue( $arrValue['label'] );

                continue;
            }

            if ( $varValue == $arrValue['value'] ) {

                $arrReturn[] = self::parseLabelValue( $arrValue['label'] );
            }
        }

        return $arrReturn;
    }


    public static function getLabelValue( $varValue, $arrField ) {

        if ( is_array( $arrField['options'] ) && !empty( $arrField['options'] ) ) {

            return static::getSelectedOptions( $varValue, $arrField['options'] );
        }

        return $varValue;
    }


    protected static function parseLabelValue( $strValue ) {

        $strValue = \Controller::replaceInsertTags( $strValue );

        // @todo translate

        return $strValue;
    }


    public static function pluckConditions( $strConditions ) {

        return array_filter( explode(';', \Controller::replaceInsertTags( $strConditions ) ) );
    }
}