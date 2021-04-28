<?php

$addon = rex_addon::get('be_logger');

if (rex::isBackend() && rex::getUser() && 'be_logger' == rex_be_controller::getCurrentPagePart(1)) {
    rex_view::addCssFile($addon->getAssetsUrl('css/be_logger.css'));
}

try {
    if (rex::isBackend() && rex::getUser()) {
        $logger = new be_logger();
        $logger->writeLogfile();
    }
} catch (Exception $e) {
    //echo 'be_logger - Exception abgefangen: ',  $e->getMessage(), "\n";
}
