<?php
/*
 * Функция для рекурсивного нахождения файлов
 * Флаг GLOB_BRACE не поддерживается
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

if (!function_exists('glob_recursive')) {
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) .'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir .'/'. basename($pattern), $flags));
        }
        return $files;
    }
}
