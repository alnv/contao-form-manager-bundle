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
                return $this->getTables();
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
}