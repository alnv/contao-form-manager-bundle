<?php

namespace Alnv\ContaoFormManagerBundle\Library;


class ResolveForm extends Resolver {


    protected $strId = null;
    protected $objForm = null;
    protected $arrFields = [];


    public function __construct( $strId, $arrOptions = [] ) {

        $this->strId = $strId;
        \System::loadLanguageFile('default');
        \Controller::loadDataContainer('tl_form_field');

        parent::__construct();
    }


    public function getForm() {

        $arrForm = [ 'palettes' => [] ];
        $objFields = \FormFieldModel::findPublishedByPid( $this->strId );

        if ( $objFields === null ) {

            return $arrForm;
        }

        $this->objForm = $objFields->getRelated('pid');
        $objFields->reset();

        if ( $objFields === null ) {

            return $arrForm;
        }

        $objPalette = new \stdClass();
        $objPalette->label = '';
        $objPalette->fields = [];
        $objPalette->hide = false;
        $objPalette->name = 'default';

        while ( $objFields->next() ) {

            $arrField = $objFields->row();

            if ( $arrField['name'] != '' && isset( $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$arrField['type']] ) && preg_match('/[,;]name[,;]/', $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$arrField['type']] ) ) {

                $this->arrFields[ $arrField['name'] ] = $arrField;
            }
            else {

                $this->arrFields[] = $arrField;
            }

            $arrAttributes = $this->parseAttributes( $arrField );

            if ( $arrAttributes === null ) {

                continue;
            }

            $objPalette->fields[] = $arrAttributes;
        }

        return [ $objPalette ];
    }


    public function saveRecord( $arrForm ) {

        $arrSubmittedAndLabels = $this->getSubmittedAndLabelsFromForm( $arrForm );
        $this->processFormData( $arrSubmittedAndLabels['submitted'], $arrSubmittedAndLabels['labels'] );
    }


    protected function processFormData( $arrSubmitted, $arrLabels ) {

        if ( isset( $GLOBALS['TL_HOOKS']['prepareFormData']) && is_array($GLOBALS['TL_HOOKS']['prepareFormData'] ) ) {

            foreach ($GLOBALS['TL_HOOKS']['prepareFormData'] as $arrCallback) {

                $this->import( $arrCallback[0] );
                $this->{ $arrCallback[0] }->{ $arrCallback[1] }( $arrSubmitted, $arrLabels, $this->arrFields, null );
            }
        }

        if ( $this->objForm->sendViaEmail ) {

            $arrKeys = [];
            $arrValues = [];
            $arrFields = [];
            $strMessage = '';

            foreach ( $arrSubmitted as $strKey => $strValue ) {

                if ( $arrKeys == 'cc' ) {

                    continue;
                }

                $strValue = \StringUtil::deserialize( $strValue );

                if ( $this->objForm->skipEmpty && !is_array( $strValue ) && !strlen( $strValue ) ) {

                    continue;
                }

                $strMessage .= ( $arrLabels[ $strKey ] ?? ucfirst( $strKey )) . ': ' . ( is_array( $strValue ) ? implode( ', ', $strValue ) : $strValue ) . "\n";

                if ( $this->objForm->format == 'xml' ) {

                    $arrFields[] = [

                        'name' => $strKey,
                        'values' => ( is_array( $strValue ) ? $strValue : [ $strValue ] )
                    ];
                }

                if ( $this->objForm->format == 'csv' ) {

                    $arrKeys[] = $strKey;
                    $arrValues[] = ( \is_array( $strValue ) ? implode( ',', $strValue ) : $strValue );
                }
            }

            $arrRecipients = \StringUtil::splitCsv( $this->objForm->recipient );

            foreach ( $arrRecipients as $strKey => $strValue ) {

                $arrRecipients[ $strKey ] = str_replace(['[', ']', '"'], ['<', '>', ''], $strValue );
            }

            $objEmail = new \Email();

            if ( $this->objForm->format == 'email' ) {

                $strMessage = $arrSubmitted['message'];
                $objEmail->subject = $arrSubmitted['subject'];
            }

            $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
            $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];

            if ( !empty( \Input::post( 'email', true ) ) ) {

                $strReplyTo = \Input::post( 'email', true );

                if ( !empty( \Input::post('name') ) ) {

                    $strReplyTo = '"' . \Input::post('name') . '" <' . $strReplyTo . '>';
                }

                elseif ( !empty( \Input::post('firstname') ) && !empty( \Input::post('lastname') ) ) {

                    $strReplyTo = '"' . \Input::post('firstname') . ' ' . \Input::post('lastname') . '" <' . $strReplyTo . '>';
                }

                $objEmail->replyTo( $strReplyTo );
            }

            if ( !$objEmail->subject ) {

                $objEmail->subject = \Controller::replaceInsertTags( $this->objForm->subject, false );
            }

            if ( !empty( $arrSubmitted['cc'] ) ) {

                $objEmail->sendCc( \Input::post( 'email', true ) );

                unset( $_SESSION['FORM_DATA']['cc'] );
            }

            if ( $this->objForm->format == 'xml' ) {

                $objTemplate = new \FrontendTemplate( 'form_xml' );
                $objTemplate->fields = $arrFields;
                $objTemplate->charset = \Config::get('characterSet');
                $objEmail->attachFileFromString( $objTemplate->parse(), 'form.xml', 'application/xml' );
            }

            if ( $this->objForm->format == 'csv' ) {

                $objEmail->attachFileFromString( \StringUtil::decodeEntities( '"' . implode( '";"', $arrKeys ) . '"' . "\n" . '"' . implode('";"', $arrValues ) . '"'), 'form.csv', 'text/comma-separated-values' );
            }

            $objEmail->text = \StringUtil::decodeEntities( trim( $strMessage ) ) . "\n\n";

            try {

                $objEmail->sendTo( $arrRecipients );
            }

            catch ( \Swift_SwiftException $objError ) {

                \System::log( 'Form "' . $this->objForm->title . '" could not be sent: ' . $objError->getMessage(), __METHOD__, TL_ERROR );
            }
        }

        // @todo store values
        foreach ( array_keys( $_POST ) as $strKey ) {

            $_SESSION['FORM_DATA'][ $strKey ] = $this->objForm->allowTags ? \Input::postHtml( $strKey, true ) : \Input::post( $strKey, true );
        }

        $arrFiles = []; //
        $_SESSION['FORM_DATA']['SUBMITTED_AT'] = time();

        if ( isset( $GLOBALS['TL_HOOKS']['processFormData'] ) && is_array($GLOBALS['TL_HOOKS']['processFormData'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['processFormData'] as $arrCallback ) {

                $this->import( $arrCallback[0] );
                $this->{ $arrCallback[0] }->{ $arrCallback[1] }( $arrSubmitted, $this->objForm->row(), $arrFiles, $arrLabels, $this );
            }
        }

        if ( FE_USER_LOGGED_IN ) {

            $this->import( \FrontendUser::class, 'User' );

            \System::log( 'Form "' . $this->objForm->title . '" has been submitted by "' . $this->User->username . '".', __METHOD__, TL_FORMS );
        }

        else {

            $this->log( 'Form "' . $this->objForm->title . '" has been submitted by a guest.', __METHOD__, TL_FORMS );
        }

        // @todo generate redirect link
    }


    protected function getSubmittedAndLabelsFromForm( &$arrForm ) {

        $arrSubmittedAndLabels = [];

        foreach ( $arrForm as $objPalette ) {

            foreach ( $objPalette->fields as $arrField ) {

                $arrSubmittedAndLabels['submitted'][ $arrField['name'] ] = $arrField['value'];
                $arrSubmittedAndLabels['labels'][ $arrField['name'] ] = $arrField['label'];
            }
        }

        return $arrSubmittedAndLabels;
    }
}