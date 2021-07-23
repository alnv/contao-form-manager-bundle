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
            'ignoreVisibility' => $this->objModule->cmIgnoreVisibility ? true : false,
            'formPage' => $this->objModule->cmFormPage ?: '',
            'masterPage' => $this->objModule->cmMasterPage ?: ''
        ];

        $this->strTable = $this->objModule->cmTable;

        $arrOptions['column'] = [];
        $arrOptions['value'] = [];

        if ($this->hasPermissions()) {
            $this->blnSuccess = $this->getPermissionQuery($arrOptions);
        }

        $arrOrder = \Input::post('order') ?: [];
        if (!empty($arrOrder)) {
            $arrOrders = [];
            foreach ($arrOrder as $strField => $strOrder) {
                $arrOrders[] = [
                    'field' => $this->strTable.'.'.$strField,
                    'order' => $strOrder
                ];
            }
            $arrOptions['order'] = \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::getOrderByStatementFromArray($arrOrders);
        }

        if ($this->blnSuccess) {
            $this->objListing = new \Alnv\ContaoCatalogManagerBundle\Views\Listing($this->strTable, $arrOptions);
        }
    }

    protected function hasPermissions() {

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($this->strTable, []);

        return $objRoleResolver->getFieldByRole('member') || $objRoleResolver->getFieldByRole('group');
    }

    protected function getPermissionQuery(&$arrOptions) {

        $arrPermissionQueries = [];
        $objMember = \FrontendUser::getInstance();

        if (!$objMember->id) {
            return false;
        }

        $objRoleResolver = \Alnv\ContaoCatalogManagerBundle\Library\RoleResolver::getInstance($this->strTable, []);

        if ($strMemberField = $objRoleResolver->getFieldByRole('member')) {
            $arrOptions['value'][] = $objMember->id;
            $arrPermissionQueries[] = 'FIND_IN_SET('.$GLOBALS['TL_DCA'][$this->strTable]['config']['_table'].'.'.$strMemberField.', ?)';
        }

        if ($strGroupField = $objRoleResolver->getFieldByRole('group')) {
            if (is_array($objMember->groups) && !empty($objMember->groups)) {
                foreach ($objMember->groups as $strGroupId) {
                    $arrOptions['value'][] = $strGroupId;
                    $arrPermissionQueries[] = 'FIND_IN_SET('.$GLOBALS['TL_DCA'][$this->strTable]['config']['_table'].'.'.$strGroupField.', ?)';
                }
            }
        }

        if (empty($arrPermissionQueries)) {
            return false;
        }

        $arrOptions['column'][] = '(' . implode(' OR ', $arrPermissionQueries) . ')';

        return true;
    }

    public function delete($strId) {

        if (!$this->blnSuccess || !$this->objListing) {
            return [
                'success' => false
            ];
        }

        foreach ($this->objListing->parse() as $arrEntity) {
            if ( $arrEntity['id'] == $strId ) {
                if ( in_array( 'notification_center', array_keys(\System::getContainer()->getParameter('kernel.bundles'))) ) {
                    $arrNotifications = \StringUtil::deserialize($this->objModule->cmNotifications, true);
                    foreach ($arrNotifications as $strNotificationId) {
                        $objNotification = \NotificationCenter\Model\Notification::findByPk($strNotificationId);
                        if ($objNotification->type == 'onDelete') {
                            $objNotification->send((new \Alnv\ContaoFormManagerBundle\Helper\NotificationTokens($this->strTable, $strId))->getTokens($objNotification->flatten_delimiter));
                        }
                    }
                }
                \Database::getInstance()->prepare('DELETE FROM ' . $this->strTable . ' WHERE id=?')->execute($strId);
            }
        }

        return [
            'success' => true
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
        $arLabels['operations'] = \Alnv\ContaoTranslationManagerBundle\Library\Translation::getInstance()
            ->translate($this->strTable . '.field.title.operations', $GLOBALS['TL_DCA'][$this->strTable]['fields']['operations']['name']);
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