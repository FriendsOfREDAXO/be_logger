<?php

$addon = rex_addon::get('be_logger');

$logger = new be_logger();
$logger->deleteOldEntries();

// Parameter bereitstellen
$func = rex_request('func', 'string', ''); // Funktion: add/edit/delete
$filter = rex_request('filter', 'int', 0);

// Filter setzen
$where = '';

if (1 === $filter) {
    $_SESSION['be_logger']['filter']['date'] = trim(rex_request('date', 'string', ''));
    $_SESSION['be_logger']['filter']['login'] = trim(rex_request('login', 'string', ''));
    $_SESSION['be_logger']['filter']['name'] = trim(rex_request('name', 'string', ''));
    $_SESSION['be_logger']['filter']['method'] = trim(rex_request('method', 'string', ''));
    $_SESSION['be_logger']['filter']['logpage'] = trim(rex_request('logpage', 'string', ''));
    $_SESSION['be_logger']['filter']['params'] = trim(rex_request('params', 'string', ''));
}
if (!isset($_SESSION['be_logger']['filter']['date'])) {
    $_SESSION['be_logger']['filter']['date'] = '';
    $_SESSION['be_logger']['filter']['login'] = '';
    $_SESSION['be_logger']['filter']['name'] = '';
    $_SESSION['be_logger']['filter']['method'] = '';
    $_SESSION['be_logger']['filter']['logpage'] = '';
    $_SESSION['be_logger']['filter']['params'] = '';
}

if ($_SESSION['be_logger']['filter']['date']) {
    $date = $_SESSION['be_logger']['filter']['date'];
    $d = DateTime::createFromFormat('d.m.Y', $date);
    $rc = $d && $d->format('d.m.Y') == $date;
    if (!$rc) {
        echo rex_view::error($this->i18n('be_logger_msg_error_date'));
    } else {
        $filterdatemin = strtotime($_SESSION['be_logger']['filter']['date']. '00:00:00');
        $filterdatemax = strtotime($_SESSION['be_logger']['filter']['date']. '23:59:59');
        $where .= PHP_EOL . '    AND (`createdate` >= \'' . $filterdatemin . '\' AND `createdate` <= \'' . $filterdatemax . '\') ';
    }
}

if ($_SESSION['be_logger']['filter']['login']) {
    $where .= PHP_EOL . '    AND (`login` LIKE \'%' . $_SESSION['be_logger']['filter']['login'] . '%\') ';
}

if ($_SESSION['be_logger']['filter']['name']) {
    $where .= PHP_EOL . '    AND (`name` LIKE \'%' . $_SESSION['be_logger']['filter']['name'] . '%\') ';
}

if ($_SESSION['be_logger']['filter']['method']) {
    $where .= PHP_EOL . '    AND (`method` LIKE \'%' . $_SESSION['be_logger']['filter']['method'] . '%\') ';
}

if ($_SESSION['be_logger']['filter']['logpage']) {
    $where .= PHP_EOL . '    AND (`page` LIKE \'%' . $_SESSION['be_logger']['filter']['logpage'] . '%\') ';
}

if ($_SESSION['be_logger']['filter']['params']) {
    $where .= PHP_EOL . '    AND (`params` LIKE \'%' . $_SESSION['be_logger']['filter']['params'] . '%\') ';
}
?>

<div class="panel panel-default">
<div class="panel-body be_logger-filter">
    <form action="<?php echo rex_url::currentBackendPage(); ?>" method="post" class="form-inline">
    <input type="hidden" name="filter" value="1" />
    <div class="form-group">
        <label for="filter-date"><?php echo $this->i18n('be_logger_label_filter_date'); ?></label>
        <input id="filter-date" class="rex-form-text form-control filter-text" type="text" name="date" value="<?php echo $_SESSION['be_logger']['filter']['date']; ?>" />
    </div>
    <div class="form-group">
        <label for="filter-login"><?php echo $this->i18n('be_logger_label_filter_login'); ?></label>
        <input id="filter-login" class="rex-form-text form-control filter-text" type="text" name="login" value="<?php echo $_SESSION['be_logger']['filter']['login']; ?>" />
    </div>
    <div class="form-group">
        <label for="filter-name"><?php echo $this->i18n('be_logger_label_filter_name'); ?></label>
        <input id="filter-name" class="rex-form-text form-control filter-text" type="text" name="name" value="<?php echo $_SESSION['be_logger']['filter']['name']; ?>" />
    </div>
    <div class="form-group">
        <label for="filter-method"><?php echo $this->i18n('be_logger_label_filter_method'); ?></label>
        <input id="filter-method" class="rex-form-text form-control filter-text" type="text" name="method" value="<?php echo $_SESSION['be_logger']['filter']['method']; ?>" />
    </div>
    <div class="form-group">
        <label for="filter-logpage"><?php echo $this->i18n('be_logger_label_filter_logpage'); ?></label>
        <input id="filter-logpage" class="rex-form-text form-control filter-text" type="text" name="logpage" value="<?php echo $_SESSION['be_logger']['filter']['logpage']; ?>" />
    </div>
    <div class="form-group">
        <label for="filter-params"><?php echo $this->i18n('be_logger_label_filter_params'); ?></label>
        <input id="filter-params" class="rex-form-text form-control filter-text" type="text" name="params" value="<?php echo $_SESSION['be_logger']['filter']['params']; ?>" />
    </div>
    <button type="submit" class="btn btn-default" name="be_logger_filter"><i class="rex-icon fa-search"></i>&nbsp;<?php echo $this->i18n('be_logger_button_filter'); ?></button>
    </form>
</div>
</div>

<?php
// rex_list
$list = rex_list::factory(
    '
    SELECT `createdate`, `login`, `name`, `method`, `page`, `params`, `browser`, `ip`
    FROM `' . rex::getTable('be_logger') . '`
    WHERE 1 ' . $where . '
    ORDER by `createdate` DESC
    ',
    50, 'BE-Logging', false);

$list->addTableColumnGroup([100, 100, 100, 40, 120, '*', 100, 100]);

$list->setColumnSortable('createdate', 'desc');
$list->setColumnSortable('login', 'asc');
$list->setColumnSortable('name', 'asc');
$list->setColumnSortable('method', 'asc');
$list->setColumnSortable('page', 'asc');

$list->setColumnFormat('createdate', 'custom', static function ($params) {
    $list = $params['list']; // $list enthält ein SQL-Objekt mit allen Feldern aus dem DB-Select
    //$str = date('d.m.Y', strtotime($list->getValue('createdate')));
    $str = date('d.m.Y', $list->getValue('createdate'));
    $str .= '<br>' . date('H:i:s', $list->getValue('createdate'));
    return $str;
});

$list->setColumnFormat('params', 'custom', static function ($params) {
    $list = $params['list'];
    $str = nl2br($list->getValue('params'));
    return $str;
});

$list->setNoRowsMessage($addon->i18n('be_logger_list_no_rows'));

$list->addTableAttribute('class', 'table-striped table-hover');

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('be_logger_thead_title'));
$fragment->setVar('content', $list->get(), false);
echo $fragment->parse('core/page/section.php');
