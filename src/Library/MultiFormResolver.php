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

        if ( !is_array($arrForms) || empty($arrForms) ) {
            $this->blnSuccess = false;
            return $this->getState();
        }

        foreach ($arrForms as $arrForm) {
            $this->setPost($arrForm['model']);
            switch ($arrForm['_source']) {
                case 'form':
                    $objFormResolver = new \Alnv\ContaoFormManagerBundle\Library\ResolveForm($arrForm['_formId'], []);
                    $arrState = $objFormResolver->validate();
                    if ( !$arrState['success'] ) {
                        $this->setErrorMessages($arrState['form']);
                        $this->blnSuccess = false;
                    }

                    break;

                case 'dc':
                    $objFormResolver = new \Alnv\ContaoFormManagerBundle\Library\ResolveDca($arrForm['_formId'], []);
                    $arrState = $objFormResolver->validate();
                    if ( !$arrState['success'] ) {
                        $this->setErrorMessages($arrState['form']);
                        $this->blnSuccess = false;
                    }
                    break;
            }
        }

        $arrState = $this->getState();
        if (!$arrState['success']) {
            return $arrState;
        }

        $arrSubmitted = [];
        foreach ($arrForms as $arrForm) {
            $arrPalettes = $arrForm['palettes'];
            foreach ($arrPalettes as $arrPalette ) {
                foreach ($arrPalette['fields'] as $arrField) {
                    $arrSubmitted[$arrField['name']] = $arrField['value']; // @todo parse values
                }
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['prepareMultiFormDataBeforeSave']) && is_array($GLOBALS['TL_HOOKS']['prepareMultiFormDataBeforeSave'])) {
            foreach ($GLOBALS['TL_HOOKS']['prepareMultiFormDataBeforeSave'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($arrSubmitted, $arrForms, $this);
                }
                elseif (\is_callable($arrCallback)) {
                    $arrCallback($arrSubmitted, $arrForms, $this);
                }
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['executeMultiFormOnSave']) && is_array($GLOBALS['TL_HOOKS']['executeMultiFormOnSave'])) {
            foreach ($GLOBALS['TL_HOOKS']['executeMultiFormOnSave'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    return $this->{$arrCallback[0]}->{$arrCallback[1]}($this->getState(), $arrSubmitted, $arrForms, $this);
                }
                elseif (\is_callable($arrCallback)) {
                    return $arrCallback($this->getState(), $arrSubmitted, $arrForms, $this);
                }
            }
            return null;
        }

        $this->sendNotifications(null, $arrSubmitted); // @todo

        return $arrState;
    }

    public function sendNotifications($strNotificationId, $arrSubmitted) {

        if (!$strNotificationId) {
            return null;
        }

        if ( !in_array( 'notification_center', array_keys(\System::getContainer()->getParameter('kernel.bundles'))) ) {
            return null;
        }

        $objNotification = \NotificationCenter\Model\Notification::findByPk($strNotificationId);
        if ($objNotification === null) {
            return null;
        }

        $arrTokens = [];
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        foreach ($arrSubmitted as $strField => $strValue) {
            $arrTokens['form_' . $strField] = $strValue;
        }
        $objNotification->send($arrTokens);
    }

    protected function getState() {
        return [
            'messages' => $this->arrErrorMessages,
            'success' => $this->blnSuccess,
            'redirect' => ''
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