<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;


class FormResolver {


    protected $objForm = null;
    protected $objFormFields = null;


    public function __construct( $strId, $arrOptions = [] ) {

        $this->objFormFields = \FormFieldModel::findPublishedByPid( $strId );
        $this->objForm = $this->objFormFields->getRelated('pid');
        $this->objFormFields->reset();
    }


    public function getForm() {

        $arrForm = [

            'palettes' => []
        ];

        if ( $this->objFormFields == null ) {

            return $arrForm;
        }

        $objPalette = new \stdClass();
        $objPalette->label = '';
        $objPalette->fields = [];
        $objPalette->hide = false;
        $objPalette->name = 'default';

        while ( $this->objFormFields->next() ) {

            $arrAttributes = $this->parseAttributes( $this->objFormFields->row() );

            if ( !$arrAttributes ) {

                continue;
            }

            $objPalette->fields[] = $arrAttributes;
        }

        return [ $objPalette ];
    }


    protected function parseAttributes( $arrField ) {

        $arrReturn = [];
        $strClass = $GLOBALS['TL_FFL'][ $arrField['type'] ];

        if ( !class_exists( $strClass ) ) {

            return null;
        }

        $objField = new $strClass( $arrField );

        foreach ( $arrField as $strFieldname => $strValue ) {

            $arrReturn[ $strFieldname ] = $objField->{$strFieldname};
        }

        $arrReturn['component'] = Toolkit::convertTypeToComponent( $arrReturn['type'], $arrReturn['rgxp'] );

        return $arrReturn;
    }
}