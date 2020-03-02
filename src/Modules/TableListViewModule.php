<?php

namespace Alnv\ContaoFormManagerBundle\Modules;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;

class TableListViewModule extends \Module {

    protected $strTemplate = 'mod_table_list_view';

    public function generate() {

        if ( \System::getContainer()->get( 'request_stack' )->getCurrentRequest()->get('_scope') == 'backend' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . strtoupper( $GLOBALS['TL_LANG']['FMD']['table-list-view'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    protected function compile() {

        $this->Template->operations = Toolkit::parseJSObject((new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions())->hasOperations($this->cmTable,['edit','delete']));
        $this->Template->addUrl = $this->cmForm && (new \Alnv\ContaoFormManagerBundle\Library\MemberPermissions())->hasAddButton($this->cmTable) ? \Alnv\ContaoCatalogManagerBundle\Helper\Toolkit::parseDetailLink( $this->cmFormPage, '' ) : '';
    }
}