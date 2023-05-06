<?php

namespace Alnv\ContaoFormManagerBundle\Modules;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;
use Alnv\ContaoFormManagerBundle\Library\MemberPermissions;
use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;

class TableListViewModule extends Module
{

    protected $strTemplate = 'mod_table_list_view';

    public function generate()
    {

        if (System::getContainer()->get('request_stack')->getCurrentRequest()->get('_scope') == 'backend') {

            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['FMD']['table-list-view'][0]) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {

        $this->Template->operations = Toolkit::parseJSObject((new MemberPermissions())->hasOperations($this->cmTable, ['edit', 'delete']));
        $this->Template->addUrl = ($this->cmForm && (new MemberPermissions())->hasAddButton($this->cmTable)) ? Toolkit::parseDetailLink($this->cmFormPage, '') : '';
    }
}