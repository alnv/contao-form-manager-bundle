<?php

namespace Alnv\ContaoFormManagerBundle\Modules;

class ListView {

    protected $strTable = null;
    protected $blnSuccess = true;
    protected $objListing = null;

    public function __construct($strModule) {

        if (!$strModule) {
            return null;
        }

        $objModule = \ModuleModel::findByPk($strModule);
        if ($objModule === null) {
            return null;
        }
        $arrOptions = [
            'formPage' => $objModule->cmFormPage ?: '',
            'masterPage' => $objModule->cmMasterPage ?: ''
        ];
        $this->strTable = $objModule->cmTable;

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($this->strTable,[]);
        if ( $strMemberField = $objRoleResolver->getFieldByRole('member') ) {
            $objMember = \FrontendUser::getInstance();
            if ( !$objMember->id ) {
                $this->blnSuccess = false;
            } else {
                $arrOptions['column'] = [$GLOBALS['TL_DCA'][$this->strTable]['config']['_table'] . '.' . $strMemberField .'=?'];
                $arrOptions['value'] = [$objMember->id];
            }
        }

        if ( $this->blnSuccess ) {
            $this->objListing = new \Alnv\ContaoCatalogManagerBundle\Views\Listing($this->strTable, $arrOptions);
        }
    }

    public function delete($strId) {

        if (!$this->blnSuccess || !$this->objListing) {
            return [
                'success' => false
            ];
        }

        foreach ($this->objListing->parse() as $arrEntity) {
            if ( $arrEntity['id'] == $strId ) {
                \Database::getInstance()->prepare('DELETE FROM ' . $this->strTable . ' WHERE id=?')->execute($strId);
            }
        }

        return [
            'success' => $this->blnSuccess
        ];
    }

    public function parse() {

        if (!$this->blnSuccess || !$this->objListing) {
            return [
                'success' => false,
                'list' => []
            ];
        }

        $arrListView = [];
        $arrFields = ['title', 'operations'];
        \System::loadLanguageFile('default', 'de');
        \System::loadLanguageFile($this->strTable, 'de');
        foreach ($this->objListing->parse() as $arrEntity) {
            $arrRow = [
                'id' => $arrEntity['id']
            ];
            foreach ($arrFields as $strField) {
                switch ($strField) {
                    case 'operations':
                        $arrRow['operations'] = [];
                        break;
                    default:
                        $arrRow[$strField] = $arrEntity[$strField];
                }
            }
            $arrRow['operations']['master'] = [
                'label' => \Alnv\ContaoTranslationManagerBundle\Library\Translation::getInstance()->translate($this->strTable . '.operator.master', $GLOBALS['TL_LANG']['MSC']['operator']['master'][0]),
                'icon' => '',
                'href' => $arrEntity['masterUrl']
            ];
            $arrRow['operations']['edit'] = [
                'label' => \Alnv\ContaoTranslationManagerBundle\Library\Translation::getInstance()->translate($this->strTable . '.operator.edit', $GLOBALS['TL_LANG']['MSC']['operator']['edit'][0]),
                'icon' => '',
                'href' => $arrEntity['editUrl']()
            ];
            $arrRow['operations']['delete'] = [
                'label' => \Alnv\ContaoTranslationManagerBundle\Library\Translation::getInstance()->translate($this->strTable . '.operator.delete', $GLOBALS['TL_LANG']['MSC']['operator']['delete'][0]),
                'icon' => '',
                'href' => $arrEntity['deleteUrl']()
            ];
            $arrListView[] = $arrRow;
        }

        $arLabels = [];
        foreach ($arrFields as $strField) {
            $arLabels[$strField] = \Alnv\ContaoTranslationManagerBundle\Library\Translation::getInstance()
                ->translate($this->strTable . '.field.title.' . $strField, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strField]['name']);
        }

        return [
            'success' => $this->blnSuccess,
            'list' => $arrListView,
            'labels' => $arLabels
        ];
    }
}