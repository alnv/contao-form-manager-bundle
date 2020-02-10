<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;

class Upload {

    protected $arrMessages = '';
    protected $blnSuccess = true;

    public function __construct( $arrOptions ) {

        switch ( $arrOptions['source'] ) {

            case 'form':

                //

                break;

            case 'dc':

                \Controller::loadDataContainer( $arrOptions['table'] );
                \System::loadLanguageFile( $arrOptions['table'], $arrOptions['language'] );

                $arrField = $GLOBALS['TL_DCA'][ $arrOptions['table'] ]['fields'][ $arrOptions['identifier'] ];
                $strClass = Toolkit::convertBackendFieldToFrontendField( $arrField['inputType'] );

                if ( !class_exists( $strClass ) ) {

                    continue;
                }

                $arrAttribute = $strClass::getAttributesFromDca( $arrField, $arrOptions['identifier'], null, $arrOptions['identifier'], $arrOptions['table'] );
                $objField = new $strClass( $arrAttribute );
                $objField->validate();

                if ( $objField->hasErrors() ) {

                    $this->blnSuccess = false;
                    $this->arrMessages = $objField->getErrors();
                }

                break;
        }
    }

    public function send() {

        return [

            'error' => implode(',', $this->arrMessages ),
            'success' => $this->blnSuccess
        ];
    }
}