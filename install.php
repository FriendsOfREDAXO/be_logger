<?php

$addon = rex_addon::get('be_logger');

// Standard-Werte setzen
if (!$addon->hasConfig()) {
    $addon->setConfig('ignorepages', 'be_logger, credits');
    $addon->setConfig('ignoreuser', '');
    $addon->setConfig('deletedays', '30');
}

// Logging-Tabelle
rex_sql_table::get(rex::getTable('be_logger'))
    ->ensureColumn(new rex_sql_column('id', 'int(11) unsigned', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('createdate', 'double(14,4)'))
    ->ensureColumn(new rex_sql_column('userid', 'int(11)', false, '0'))
    ->ensureColumn(new rex_sql_column('login', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('method', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('page', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('params', 'text'))
    ->ensureColumn(new rex_sql_column('session_id', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('browser', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('ip', 'varchar(255)', false, ''))
    ->ensureColumn(new rex_sql_column('useragent', 'varchar(255)', false, ''))
    ->setPrimaryKey('id')
    ->ensure();
