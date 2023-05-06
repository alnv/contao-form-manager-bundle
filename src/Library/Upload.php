<?php

namespace Alnv\ContaoFormManagerBundle\Library;

use Alnv\ContaoFormManagerBundle\Helper\Toolkit;
use Contao\Config;
use Contao\Controller;
use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

class Upload
{

    public function upload($arrOptions)
    {

        $arrResponse = [
            'error' => '',
            'file' => null,
            'success' => true
        ];

        switch ($arrOptions['source']) {

            case 'form':
                //
                break;
            case 'dc':

                Controller::loadDataContainer($arrOptions['table']);
                System::loadLanguageFile($arrOptions['table'], $arrOptions['language']);

                $arrField = $GLOBALS['TL_DCA'][$arrOptions['table']]['fields'][$arrOptions['identifier']];
                $strClass = Toolkit::convertBackendFieldToFrontendField($arrField['inputType']);

                if (!class_exists($strClass)) {
                    break;
                }

                $arrAttribute = $strClass::getAttributesFromDca($arrField, $arrOptions['identifier'], null, $arrOptions['identifier'], $arrOptions['table']);
                if ($arrAttribute['imageWidth']) {
                    Config::set('imageWidth', $arrAttribute['imageWidth']);
                }
                if ($arrAttribute['imageHeight']) {
                    Config::set('imageHeight', $arrAttribute['imageHeight']);
                }
                $objField = new $strClass($arrAttribute);
                $objField->validate();

                if ($objField->hasErrors()) {

                    $arrResponse['success'] = false;
                    $arrResponse['error'] = implode(',', $objField->getErrors());
                }

                $arrResponse['file'] = $_SESSION['FILES'][$arrOptions['identifier']];
                break;
        }

        return $arrResponse;
    }


    public function delete($arrOptions)
    {

        $arrResponse = [];
        $objFile = FilesModel::findByUuid($arrOptions['file']);

        if ($objFile == null) {
            return $arrResponse;
        }

        Files::getInstance()->delete($objFile->path);
        $objFile->delete();

        return $arrResponse;
    }


    public function getFiles($arrOptions)
    {

        $arrResponse = [
            'files' => []
        ];

        if (empty($arrOptions['files'])) {
            return $arrResponse;
        }

        foreach ($arrOptions['files'] as $strUuid) {
            if (!Validator::isUuid($strUuid)) {
                continue;
            }

            $objFile = FilesModel::findByUuid($strUuid);
            if ($objFile == null) {
                continue;
            }

            $strHref = '';
            if ($objPage = PageModel::findByPk(Input::post('pageId'))) {
                $strHref = $objPage->getFrontendUrl() . '?file=' . $objFile->path;
            }

            $arrResponse['files'][] = [
                'id' => $objFile->id,
                'name' => $objFile->name,
                'path' => $objFile->path,
                'type' => $objFile->type,
                'extension' => $objFile->extension,
                'uuid' => StringUtil::binToUuid($objFile->uuid),
                'href' => $strHref,
                'size' => System::getReadableSize((new File($objFile->path))->filesize),
                'imagesize' => getimagesize(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objFile->path)
            ];
        }

        return $arrResponse;
    }
}