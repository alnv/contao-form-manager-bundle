<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Email;
use Contao\FormFieldModel;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LogLevel;

class ResolveForm extends Resolver
{

    protected $strId = null;

    protected $objForm = null;

    protected $arrFields = [];

    public function __construct($strId, $arrOptions = [])
    {

        $this->strId = $strId;
        System::loadLanguageFile('default');
        Controller::loadDataContainer('tl_form_field');
    }

    public function getForm()
    {

        $arrForm = ['palettes' => []];
        $objFields = FormFieldModel::findPublishedByPid($this->strId);

        if ($objFields === null) {

            return $arrForm;
        }

        $this->objForm = $objFields->getRelated('pid');
        $objFields->reset();

        $objPalette = new \stdClass();
        $objPalette->label = '';
        $objPalette->fields = [];
        $objPalette->hide = false;
        $objPalette->name = 'default';

        while ($objFields->next()) {

            $arrField = $objFields->row();

            if ($arrField['name'] != '' && isset($GLOBALS['TL_DCA']['tl_form_field']['palettes'][$arrField['type']]) && preg_match('/[,;]name[,;]/', $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$arrField['type']])) {
                $this->arrFields[$arrField['name']] = $arrField;
            } else {
                $this->arrFields[] = $arrField;
            }

            $arrAttributes = $this->parseAttributes($arrField);
            $arrAttributes['_source'] = 'form';
            $arrAttributes['_table'] = 'tl_form_field';
            $arrAttributes['_identifier'] = $arrField['name'];

            if ($arrAttributes === null) {
                continue;
            }

            $objPalette->fields[] = $arrAttributes;
        }

        return [$objPalette];
    }

    public function saveRecord($arrForm)
    {

        $arrSubmittedAndLabels = $this->getSubmittedAndLabelsFromForm($arrForm);
        $this->processFormData($arrSubmittedAndLabels['submitted'], $arrSubmittedAndLabels['labels']);
    }

    protected function processFormData($arrSubmitted, $arrLabels)
    {

        if (isset($GLOBALS['TL_HOOKS']['prepareFormData']) && is_array($GLOBALS['TL_HOOKS']['prepareFormData'])) {
            foreach ($GLOBALS['TL_HOOKS']['prepareFormData'] as $arrCallback) {
                System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrSubmitted, $arrLabels, $this->arrFields, null);
            }
        }

        if ($this->objForm->sendViaEmail) {

            $arrKeys = [];
            $arrValues = [];
            $arrFields = [];
            $strMessage = '';

            foreach ($arrSubmitted as $strKey => $strValue) {

                if ($arrKeys == 'cc') {
                    continue;
                }

                $strValue = StringUtil::deserialize($strValue, true);
                if ($this->objForm->skipEmpty && !is_array($strValue) && !strlen($strValue)) {
                    continue;
                }

                $strMessage .= ($arrLabels[$strKey] ?? ucfirst($strKey)) . ': ' . (is_array($strValue) ? implode(', ', $strValue) : $strValue) . "\n";

                if ($this->objForm->format == 'xml') {
                    $arrFields[] = [
                        'name' => $strKey,
                        'values' => (is_array($strValue) ? $strValue : [$strValue])
                    ];
                }

                if ($this->objForm->format == 'csv') {
                    $arrKeys[] = $strKey;
                    $arrValues[] = (\is_array($strValue) ? implode(',', $strValue) : $strValue);
                }
            }

            $arrRecipients = StringUtil::splitCsv($this->objForm->recipient);
            foreach ($arrRecipients as $strKey => $strValue) {
                $arrRecipients[$strKey] = str_replace(['[', ']', '"'], ['<', '>', ''], $strValue);
            }

            $objEmail = new Email();
            if ($this->objForm->format == 'email') {
                $strMessage = $arrSubmitted['message'];
                $objEmail->subject = $arrSubmitted['subject'];
            }

            $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
            $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];

            if (!empty(Input::post('email', true))) {
                $strReplyTo = Input::post('email', true);
                if (!empty(Input::post('name'))) {
                    $strReplyTo = '"' . Input::post('name') . '" <' . $strReplyTo . '>';
                } elseif (!empty(Input::post('firstname')) && !empty(Input::post('lastname'))) {
                    $strReplyTo = '"' . Input::post('firstname') . ' ' . Input::post('lastname') . '" <' . $strReplyTo . '>';
                }

                $objEmail->replyTo($strReplyTo);
            }

            if (!$objEmail->subject) {
                $objEmail->subject = Toolkit::replaceInsertTags($this->objForm->subject, false);
            }

            if (!empty($arrSubmitted['cc'])) {
                $objEmail->sendCc(Input::post('email', true));
                unset($_SESSION['FORM_DATA']['cc']);
            }

            if ($this->objForm->format == 'xml') {
                $objTemplate = new FrontendTemplate('form_xml');
                $objTemplate->fields = $arrFields;
                $objTemplate->charset = Config::get('characterSet');
                $objEmail->attachFileFromString($objTemplate->parse(), 'form.xml', 'application/xml');
            }

            if ($this->objForm->format == 'csv') {
                $objEmail->attachFileFromString(StringUtil::decodeEntities('"' . implode('";"', $arrKeys) . '"' . "\n" . '"' . implode('";"', $arrValues) . '"'), 'form.csv', 'text/comma-separated-values');
            }

            $objEmail->text = StringUtil::decodeEntities(trim($strMessage)) . "\n\n";

            try {
                $objEmail->sendTo($arrRecipients);
            } catch (\Exception $objError) {
                System::getContainer()
                    ->get('monolog.logger.contao')
                    ->log(LogLevel::INFO, 'Form "' . $this->objForm->title . '" could not be sent: ' . $objError->getMessage(), ['contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__)]);
            }
        }

        // @todo store values
        foreach (array_keys($_POST) as $strKey) {

            $_SESSION['FORM_DATA'][$strKey] = $this->objForm->allowTags ? Input::postHtml($strKey, true) : Input::post($strKey, true);
        }

        $_SESSION['FORM_DATA']['SUBMITTED_AT'] = time();

        if (FrontendUser::getInstance()->id) {
            System::getContainer()
                ->get('monolog.logger.contao')
                ->log(LogLevel::INFO, 'Form "' . $this->objForm->title . '" has been submitted by "' . FrontendUser::getInstance()->username . '".', ['contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__)]);
        } else {
            System::getContainer()
                ->get('monolog.logger.contao')
                ->log(LogLevel::INFO, 'Form "' . $this->objForm->title . '" has been submitted by a guest.', ['contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__)]);
        }
    }

    protected function getSubmittedAndLabelsFromForm(&$arrForm): array
    {

        $arrSubmittedAndLabels = [];

        foreach ($arrForm as $objPalette) {

            foreach ($objPalette->fields as $arrField) {

                $arrSubmittedAndLabels['submitted'][$arrField['name']] = $arrField['value'];
                $arrSubmittedAndLabels['labels'][$arrField['name']] = $arrField['label'];
            }
        }

        return $arrSubmittedAndLabels;
    }
}