<?php
/*
 * Функция автоматической загрузки системных классов
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

if (!function_exists('autoload')) {
    function autoload($className)
    {
        $className = trim($className, NS);
        $relativePath = '';
        if (false !== $pos = strrpos($className, NS)) {
            $namespace = substr($className, 0, $pos);
            $className = substr($className, $pos + 1);
            $relativePath = str_replace(NS, DS, $namespace) . DS;
        }
        $relativePath .= $className . '.{class,trait}.php';
        $files = glob($relativePath, GLOB_BRACE);
        foreach ($files as $file) {
            if (is_file($file)) {
                require_once $file;
            }
        }
    }
}
