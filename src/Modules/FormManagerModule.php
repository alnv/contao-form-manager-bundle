<?php

namespace Alnv\ContaoFormManagerBundle\Modules;

use Alnv\ContaoCatalogManagerBundle\Library\RoleResolver;
use Alnv\ContaoCatalogManagerBundle\Views\Listing;
use Alnv\ContaoCatalogManagerBundle\Views\Master;
use Alnv\ContaoFormManagerBundle\Library\MemberPermissions;
use Alnv\ContaoTranslationManagerBundle\Library\Translation;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Exception\InsufficientAuthenticationException;
use Contao\Environment;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Contao\System;

class FormManagerModule extends Module
{

    protected $arrActiveRecord = [];
    protected $strTemplate = 'mod_form_manager';

    public function generate()
    {

        if (System::getContainer()->get('request_stack')->getCurrentRequest()->get('_scope') == 'backend') {

            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['FMD']['form-manager'][0]) . ' ###';

            return $objTemplate->parse();
        }

        if ($objFile = FilesModel::findByPath(Input::get('file'))) {
            Controller::sendFileToBrowser($objFile->path, true);
        }

        $this->arrActiveRecord = $this->getActiveRecord();
        $objPermission = new MemberPermissions();
        if (!empty($this->arrActiveRecord) && !$objPermission->hasPermission($this->cmIdentifier, $this->arrActiveRecord)) {
            throw new InsufficientAuthenticationException('Page access denied:  ' . Environment::get('uri'));
        }

        return parent::generate();
    }

    protected function compile()
    {

        $this->Template->source = $this->cmSource;
        $this->Template->formHint = $this->cmFormHint;
        $this->Template->identifier = $this->cmIdentifier;
        $this->Template->language = $GLOBALS['TL_LANGUAGE'] ?: '';
        $this->Template->successRedirect = $this->getFrontendUrl($this->cmSuccessRedirect);
        $this->Template->model = $this->getJsModelObject();
        $this->Template->submitLabel = Translation::getInstance()->translate('form.' . $this->cmIdentifier . '.submit', 'Speichern');
    }

    protected function getJsModelObject()
    {

        if (empty($this->arrActiveRecord)) {
            return null;
        }
        $arrModel = [];
        $arrFields = $GLOBALS['TL_DCA'][$this->cmIdentifier]['fields'];
        foreach ($arrFields as $strField => $arrDcField) {
            $varValue = $this->arrActiveRecord[$strField];
            if (!$arrDcField['eval']['multiple'] && $arrDcField['inputType'] == 'select' && is_array($varValue)) {
                if (isset($varValue[0])) {
                    if (isset($varValue[0]['value'])) {
                        $varValue = $varValue[0]['value'];
                    } else {
                        $varValue = $varValue[0];
                    }
                }
            }
            $arrModel[$strField] = $varValue;
        }
        return htmlspecialchars(json_encode($arrModel), ENT_QUOTES, 'UTF-8');
    }

    protected function getFrontendUrl($strPageId)
    {
        if (!$strPageId) {
            return '';
        }

        $objPage = PageModel::findByPk($strPageId);
        if ($objPage === null) {
            return '';
        }

        return $objPage->getFrontendUrl();
    }

    protected function getActiveRecord()
    {

        if ($this->cmSource != 'dc') {
            return [];
        }

        if (!isset($_GET['auto_item'])) {
            if (!$this->cmStandalone) {
                return [];
            }
            $objUser = FrontendUser::getInstance();
            if (!$objUser->id) {
                return [];
            }
            $objRoleResolver = RoleResolver::getInstance($this->cmIdentifier);
            $strUserField = $objRoleResolver->getFieldByRole('member');
            if (!$strUserField) {
                return [];
            }
            $arrListings = (new Listing($this->cmIdentifier, [
                'column' => ['' . $strUserField . '=?'],
                'value' => [$objUser->id],
                'ignoreVisibility' => true,
                'limit' => 1,
                'fastMode' => true,
                'isForm' => true,
                'id' => $this->id
            ]))->parse();
            if (empty($arrListings)) {
                return [];
            } else {
                return $arrListings[0];
            }
        } else {
            return (new Master($this->cmIdentifier, [
                'alias' => Input::get('auto_item'),
                'ignoreVisibility' => true,
                'fastMode' => true,
                'isForm' => true,
                'id' => $this->id
            ]))->parse()[0];
        }
    }
}