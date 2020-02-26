<?php

namespace Alnv\ContaoFormManagerBundle\Modules;

class ListView {

    protected $strTable = null;
    protected $objModule = null;
    protected $objListing = null;
    protected $blnSuccess = true;

    public function __construct($strModule) {

        if (!$strModule) {
            return null;
        }

        $this->objModule = \ModuleModel::findByPk($strModule);
        if ($this->objModule === null) {
            return null;
        }
        $arrOptions = [
            'formPage' => $this->objModule->cmFormPage ?: '',
            'masterPage' => $this->objModule->cmMasterPage ?: ''
        ];
        $this->strTable = $this->objModule->cmTable;
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
        $arrFields = \StringUtil::deserialize($this->objModule->cmFields, true);
        \System::loadLanguageFile('default', 'de');
        \System::loadLanguageFile($this->strTable, 'de');
        foreach ($this->objListing->parse() as $arrEntity) {
            $arrRow = [
                'id' => $arrEntity['id']
            ];
            foreach ($arrFields as $strField) {
                $arrRow[$strField] = $arrEntity[$strField];
            }
            $arrRow['operations'] = [];
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
            'fields' => $arrFields,
            'labels' => $arLabels
        ];
    }
}