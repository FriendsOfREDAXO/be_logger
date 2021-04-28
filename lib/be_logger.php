<?php
/**
 * BE_Logger - Ausgabe der Logging-Tabelle.
 * @author Friends Of REDAXO
 * @package be_logger
 */

class be_logger
{
    public function getUserIP()
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
                $addr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim($addr[0]);
            }
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    public function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = '';

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason

        if (preg_match('/Edg\//i', $u_agent)) { // this condition is for Edge
            $bname = 'Edge';
            $ub = 'Edg';
        } elseif (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = 'MSIE';
        } elseif (preg_match('/Trident/i', $u_agent)) { // this condition is for IE11
            $bname = 'Internet Explorer';
            $ub = 'rv';
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = 'Firefox';
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = 'Chrome';
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = 'Safari';
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = 'Opera';
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = 'Netscape';
        }

        // finally get the correct version number
        // Added "|:"
        $known = ['Version', $ub, 'other'];
        $pattern = '#(?<browser>' . implode('|', $known) .
            ')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if (1 != $i) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, 'Version') < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if (null == $version || '' == $version) {
            $version = '?';
        }

        return [
            'name' => $bname,
            'lname' => strtolower(str_replace(' ', '', $bname)),
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern,
            'userAgent' => $u_agent,
        ];
    }

    public function writeLogfile()
    {
        $page = rex_request('page', 'string', '-');
        $ignorepages = rex_addon::get('be_logger')->getConfig('ignorepages');
        if ($ignorepages) {
            $ign = explode(',', $ignorepages);
            if (in_array($page, $ign)) {
                return;
            }
            foreach ($ign as $ignpage) {
                $ignpage = trim($ignpage);
                if (substr($page, 0, strlen($ignpage)) == $ignpage) {
                    return;
                }
            }
        }

        $sql = rex_sql::factory();
        $sql->setDebug(false);

        $datum = microtime(true);
        $datum = str_replace(',', '.', $datum);

        $userid = 0;
        $login = '';
        $name = '';
        $session_id = session_id();

        if (rex::getUser()) {
            $userid = rex::getUser()->getValue('id');
            $login = rex::getUser()->getValue('login');
            $name = rex::getUser()->getValue('name');
            if (rex::getImpersonator()) {
                $name .= ' (BE-Switch)';
            }
        }

        $ignoreuser = rex_addon::get('be_logger')->getConfig('ignoreuser');
        if ($ignoreuser) {
            $ign = explode(',', $ignoreuser);
            if (in_array($login, $ign)) {
                return;
            }
            foreach ($ign as $ignuser) {
                $ignuser = trim($ignuser);
                if (substr($login, 0, strlen($ignuser)) == $ignuser) {
                    return;
                }
            }
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $page = rex_request('page', 'string', '-');

        $params = '';
        foreach ($_REQUEST as $key => $par) {
            if (is_string($par)) {
                $params .= $key . ' = ' . $par . PHP_EOL;
            }
        }

        $browserdata = self::getBrowser();
        $browser = $browserdata['name'];
        $useragent = $browserdata['userAgent'];

        $ip = self::getUserIP();

        $sql->setTable(rex::getTable('be_logger'));
        $sql->setValue('id', null);
        $sql->setValue('createdate', $datum);
        $sql->setValue('userid', $userid);
        $sql->setValue('login', $login);
        $sql->setValue('name', $name);
        $sql->setValue('method', $method);
        $sql->setValue('page', $page);
        $sql->setValue('params', $params);
        $sql->setValue('session_id', $session_id);
        $sql->setValue('browser', $browser);
        $sql->setValue('ip', $ip);
        $sql->setValue('useragent', $useragent);

        $sql->insert();
    }

    public function deleteOldEntries()
    {
        $deletedays = rex_addon::get('be_logger')->getConfig('deletedays');

        if ((int) $deletedays > 0) {
            $sql = rex_sql::factory();
            $sql->setDebug(false);

            $deletedate = time() - ($deletedays * 24 * 60 * 60);
            //$deletedate = date('Y-m-d H:i:s', $deletedate);

            $_query = 'DELETE FROM `' . rex::getTable('be_logger') . '` ';
            $_query .= ' WHERE `createdate` < \'' . $deletedate . '\' ';
            $sql->setQuery($_query);
        }
    }
}
