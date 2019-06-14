<?php

$objAssetsManager = \Alnv\ContaoAssetsManagerBundle\Library\AssetsManager::getInstance();
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/library/vue.min.js' );
$objAssetsManager->addIfNotExist( 'bundles/alnvcontaoformmanager/js/vue/library/vue-resource.min.js' );