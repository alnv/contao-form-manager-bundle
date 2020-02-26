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
        $this->Template->successRedirect = $this->getFrontendUrl($this->cmSuccessRedirect);
        $this->Template->model = htmlspecialchars(json_encode($this->arrActiveRecord),ENT_QUOTES,'UTF-8');
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
        $arrActiveRecord = [];
        if ($this->cmSource != 'dc') {
            return $arrActiveRecord;
        }
        if (!isset($_GET['auto_item'])) {
            return $arrActiveRecord;
        }
        $objMaster = new \Alnv\ContaoCatalogManagerBundle\Views\Master( $this->cmIdentifier, [
            'alias' => \Input::get('auto_item'),
            'id' => $this->id
        ]);
        $arrMaster = $objMaster->parse();
        if (empty($arrMaster)) {
            return $arrActiveRecord;
        }
        $arrFields = array_keys($GLOBALS['TL_DCA'][$this->cmIdentifier]['fields']);
        if (!is_array($arrFields) || empty($arrFields)) {
            return $arrActiveRecord;
        }
        foreach ($arrFields as $strField) {
            $arrActiveRecord[$strField] = $this->parseModelValue($arrMaster[0][$strField],$GLOBALS['TL_DCA'][$this->cmIdentifier]['fields'][$strField],$arrMaster[0],$strField);
        }
        return $arrActiveRecord;
    }

    protected function parseModelValue($varValue,$arrField,$arrMaster,$strField) {

        if ( $arrField['inputType'] == 'fileTree' ) {
            $arrFiles = \StringUtil::deserialize($arrMaster['origin'][$strField],true);
            foreach ($arrFiles as $index => $strUuid) {
                if ( \Validator::isBinaryUuid($strUuid) ) {
                    $arrFiles[$index] = \StringUtil::binToUuid($strUuid);
                }
            }
            return $arrFiles;
        }
        return $varValue;
    }
}