<?php

namespace Alnv\ContaoFormManagerBundle\Modules;

class FormManagerModule extends \Module {

    protected $arrActiveRecord = [];
    protected $strTemplate = 'mod_form_manager';

    public function generate() {

        if ( \System::getContainer()->get( 'request_stack' )->getCurrentRequest()->get('_scope') == 'backend' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . strtoupper( $GLOBALS['TL_LANG']['FMD']['form-manager'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        $this->arrActiveRecord = $this->getActiveRecord();
        $objPermission = new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions();
        if ( !empty($this->arrActiveRecord) && !$objPermission->hasPermission($this->cmIdentifier, $this->arrActiveRecord) ) {
            throw new \CoreBundle\Exception\InsufficientAuthenticationException('Page access denied:  ' . \Environment::get('uri'));
        }

        return parent::generate();
    }

    protected function compile() {

        $this->Template->source = $this->cmSource;
        $this->Template->formHint = $this->cmFormHint;
        $this->Template->identifier = $this->cmIdentifier;
        $this->Template->language = $GLOBALS['TL_LANGUAGE'] ?: '';
        $this->Template->successRedirect = $this->getFrontendUrl($this->cmSuccessRedirect);
        $this->Template->model = htmlspecialchars(json_encode($this->arrActiveRecord),ENT_QUOTES,'UTF-8');
        $this->Template->submitLabel = \Alnv\ContaoTranslationManagerBundle\Library\Translation::getInstance()->translate('form.' . $this->cmIdentifier . '.submit',  'Senden');
    }

    protected function getFrontendUrl($strPageId) {
        if (!$strPageId) {
            return '';
        }
        $objPage = \PageModel::findByPk($strPageId);
        if ( $objPage === null ) {
            return '';
        }
        return $objPage->getFrontendUrl();
    }

    protected function getActiveRecord() {
        if ($this->cmSource != 'dc') {
            return [];
        }
        if (!isset($_GET['auto_item'])) {
            return [];
        }
        return (new \Alnv\ContaoCatalogManagerBundle\Views\Master($this->cmIdentifier,[
            'alias' => \Input::get('auto_item'),
            'ignoreVisibility' => true,
            'fastMode' => true,
            'isForm' => true,
            'id' => $this->id
        ]))->parse()[0];
    }
}