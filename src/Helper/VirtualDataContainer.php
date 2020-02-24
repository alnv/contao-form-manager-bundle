<?php

namespace Alnv\ContaoFormManagerBundle\Helper;

class VirtualDataContainer extends \DataContainer {

    public function __construct( $strTable ) {

        parent::__construct();
        $this->table = $strTable;
    }

    public function __set($strKey, $varValue) {

        switch ( $strKey ) {
            case 'activeRecord':
                $objEntity = null;
                if ( \Input::post('id') ) {
                    $objEntity = \Database::getInstance()->prepare(sprintf( 'SELECT * FROM %s WHERE id=?', $this->table ))->limit(1)->execute(\Input::post('id'));
                }
                $this->objActiveRecord = $objEntity;
                break;

            default:
                parent::__set( $strKey, $varValue );
                break;
        }
    }

    public function getPalette(){}
    public function save( $varValue ){}
}
