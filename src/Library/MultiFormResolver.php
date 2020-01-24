<?php

namespace Alnv\ContaoFormManagerBundle\Library;


class MultiFormResolver extends \System {


    protected $blnSuccess = true;
    protected $arrErrorMessages = [];


    public function __construct() {

        parent::__construct();
    }


    public function save() {

        $arrForms = \Input::post('forms');

        if ( !is_array( $arrForms ) || empty( $arrForms ) ) {

            $this->blnSuccess = false;

            return $this->getState();
        }

        $arrDebug = [];
        $arrDebug['state'] = [];

        foreach ( $arrForms as $arrForm ) {

            switch ( $arrForm['_source'] ) {

                case 'form':

                    $this->setPost( $arrForm['model'] );
                    $objFormResolver = new FormResolver( $arrForm['_formId'], [] );
                    $arrState = $objFormResolver->validate();

                    if ( !$arrState['success'] ) {

                        $this->setErrorMessages( $arrState['form'] );
                        $this->blnSuccess = false;
                    }

                    break;

                case 'dc':

                    $this->blnSuccess = false;

                    break;
            }
        }

        // @todo bind notification center
        // @todo hook
        // @todo generate redirect
        return $this->getState();
    }


    protected function getState() {

        return [

            'messages' => $this->arrErrorMessages,
            'success' => $this->blnSuccess
        ];
    }


    protected function setPost( $arrModels ) {

        if ( is_array( $arrModels ) && !empty( $arrModels ) ) {

            foreach ( $arrModels as $strFieldname => $varValue ) {

                \Input::setPost( $strFieldname, $varValue );
            }
        }
    }


    protected function setErrorMessages( $arrForms ) {

        if ( is_array( $arrForms ) && !empty( $arrForms ) ) {

            foreach ( $arrForms as $objPalette ) {

                foreach ( $objPalette->fields as $arrField ) {

                    if ( !$arrField['validate'] ) {

                        foreach ( $arrField['messages'] as $strMessage ) {

                            $this->arrErrorMessages[] = $strMessage;
                        }
                    }
                }
            }
        }
    }
}