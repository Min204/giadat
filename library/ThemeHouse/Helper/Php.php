<?php
$rootDir = XenForo_Autoloader::getInstance()->getRootDir();

$version = 0;
if ($handle = opendir($rootDir . '/ThemeHouse/Helper/Php')) {
    while (false !== ($entry = readdir($handle))) {
        if (intval($entry) > $version) {
            $version = intval($entry);
        }
    }
}

require_once $rootDir . '/ThemeHouse/Helper/Php/' . $version . '.php';