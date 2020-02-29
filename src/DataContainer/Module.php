<?php

namespace Alnv\ContaoFormManagerBundle\DataContainer;

class Module {

    public function getFormIdentifier(\DataContainer $dc) {

        $arrReturn = [];
        if (!$dc->activeRecord->cmSource) {
            return $arrReturn;
        }

        switch ($dc->activeRecord->cmSource) {
            case 'dc':
                return (new \Alnv\ContaoCatalogManagerBundle\DataContainer\Catalog())->getTables();
            case 'form':
                $objForms = \FormModel::findAll();
                if ($objForms === null) {
                    while ($objForms->next()) {
                        $arrReturn[$objForms->id] = $objForms->title;
                    }
                }
                return $arrReturn;
        }
        return $arrReturn;
    }

    public function getNotifications() {

        $arrReturn = [];

        if ( empty($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['form-manager-bundle']) || !is_array($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['form-manager-bundle']) ) {
            return $arrReturn;
        }

        if ( !in_array( 'notification_center', array_keys(\System::getContainer()->getParameter('kernel.bundles'))) ) {
            return $arrReturn;
        }

        foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['form-manager-bundle'] as $strType => $arrTokens) {
            $objNotificationCollection = \NotificationCenter\Model\Notification::findByType($strType);
            if ($objNotificationCollection === null) {
                continue;
            }
            while ($objNotificationCollection->next()) {
                $arrReturn[$objNotificationCollection->id] = $objNotificationCollection->title;
            }
        }

        return $arrReturn;
    }
}