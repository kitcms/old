<?php
/*
 * Конфигурация автозагрузчика для библиотеки elFinder
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

$elFinderDir = $dir['vendor'] . DS .'studio-42'. DS .'elfinder'. DS;

require_once $elFinderDir . 'autoload.php';

// Регистрация библиотеки для загрузки через SPL
/*$classLoader->addSymlinks(array(
    'elFinderVolumeLocalFileSystem' => $elFinderDir .'elFinderVolumeLocalFileSystem.class.php',
    'elFinderVolumeDriver' => $elFinderDir .'elFinderVolumeDriver.class.php',
    'elFinderConnector' => $elFinderDir .'elFinderConnector.class.php',
    'elFinder' => $elFinderDir .'elFinder.class.php'
));*/
