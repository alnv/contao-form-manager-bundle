<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;

class Upload {

    public function upload( $arrOptions ) {

        $arrResponse = [
            'error' => '',
            'file' => null,
            'success' => true
        ];

        switch ( $arrOptions['source'] ) {

            case 'form':

                //

                break;

            case 'dc':

                \Controller::loadDataContainer( $arrOptions['table'] );
                \System::loadLanguageFile( $arrOptions['table'], $arrOptions['language'] );

                $arrField = $GLOBALS['TL_DCA'][ $arrOptions['table'] ]['fields'][ $arrOptions['identifier'] ];
                $strClass = Toolkit::convertBackendFieldToFrontendField( $arrField['inputType'] );

                if ( !class_exists( $strClass ) ) {

                    continue;
                }

                $arrAttribute = $strClass::getAttributesFromDca( $arrField, $arrOptions['identifier'], null, $arrOptions['identifier'], $arrOptions['table'] );
                $objField = new $strClass( $arrAttribute );
                $objField->validate();

                if ( $objField->hasErrors() ) {

                    $arrResponse['success'] = false;
                    $arrResponse['error'] = implode(',', $objField->getErrors());
                }

                $arrResponse['file'] = $_SESSION['FILES'][ $arrOptions['identifier'] ];

                break;
        }

        return $arrResponse;
    }


    public function delete( $arrOptions ) {

        $arrResponse = [];

        $objFile = \FilesModel::findByUuid( $arrOptions['file'] );

        if ( $objFile == null ) {

            return $arrResponse;
        }

        \Files::getInstance()->delete( $objFile->path );
        $objFile->delete();

        return $arrResponse;
    }


    public function getFiles( $arrOptions ) {

        $arrResponse = [
            'files' => []
        ];

        if ( empty( $arrOptions['files'] ) ) {

            return $arrResponse;
        }

        foreach ( $arrOptions['files'] as $strUuid ) {

            if ( !\Validator::isUuid( $strUuid ) ) {

                continue;
            }

            $objFile = \FilesModel::findByUuid( $strUuid );

            if ( $objFile == null ) {

                continue;
            }

            $arrResponse['files'][] = [
                'id' => $objFile->id,
                'name' => $objFile->name,
                'path' => $objFile->path,
                'type' => $objFile->type,
                'extension' => $objFile->extension,
                'uuid' => \StringUtil::binToUuid( $objFile->uuid ),
                'size' => \System::getReadableSize((new \File($objFile->path))->filesize)
            ];
        }

        return $arrResponse;
    }
}