<?php

$addon = rex_addon::get('be_logger');

echo rex_view::title($addon->i18n('be_logger_title'));

rex_be_controller::includeCurrentPageSubPath();
