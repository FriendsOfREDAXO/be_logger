<?php

$logger = new be_logger();
$logger->deleteOldEntries();

$func = rex_request('func', 'string', '');
$csrf = rex_csrf_token::factory('be_logger_truncate');

$addon = rex_addon::get('be_logger');

$form = rex_config_form::factory('be_logger');

$field = $form->addFieldset($addon->i18n('be_logger_config_legend1'));

$field = $form->addRawField('<dl class="rex-form-group form-group"><dt></dt><dd><p>'.$addon->i18n('be_logger_ignoreinfo').'</p></dd></dl>');

$field = $form->addTextAreaField('ignorepages', null, ['class' => 'form-control']);
$field->setLabel($addon->i18n('be_logger_config_label_ignorepages'));

$field = $form->addTextAreaField('ignoreuser', null, ['class' => 'form-control']);
$field->setLabel($addon->i18n('be_logger_config_label_ignoreuser'));

$field = $form->addFieldset($addon->i18n('be_logger_config_legend2'));

$field = $form->addInputField('text', 'deletedays', null, ['class' => 'form-control']);
$field->setLabel($addon->i18n('be_logger_config_label_deletedays'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('be_logger_title_config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

if ('' != $func) {
    if (!$csrf->isValid()) {
        echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        if ('truncate' == $func) {
            $sql = rex_sql::factory();
            $sql->setDebug(false);
            $_query = 'TRUNCATE TABLE `' . rex::getTable('be_logger') . '` ';
            $sql->setQuery($_query);
            echo rex_view::success($this->i18n('be_logger_truncate_successful'));
        }
    }
}

$content = '<h3>'.$this->i18n('be_logger_title_truncate').'</h3>';
$content .= '<p>' . rex_i18n::rawMsg('be_logger_truncate_info') . '</p>';
$content .= '<p><a class="btn btn-primary" href="'.rex_url::currentBackendPage(['func' => 'truncate'] + $csrf->getUrlParams()).'" data-confirm="'.$this->i18n('be_logger_truncate_confirm').'">'.$this->i18n('be_logger_button_truncate').'</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('be_logger_title_truncate'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
