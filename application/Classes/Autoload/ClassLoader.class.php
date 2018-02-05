<?php
/*
 * Реализации автоматической загрузки классов сторонних библиотек
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Autoload;

class ClassLoader
{
    private $extensions = array();
    private $fallbacks = array();
    private $symlinks = array();

    public function addExtensions($extensions)
    {
        foreach ((array) $extensions as $extension) {
            if (!in_array($extension, $this->extensions, true)) {
                array_push($this->extensions, $extension);
            }
        }
    }

    public function addFallbacks($dirs)
    {
        foreach ((array) $dirs as $dir) {
            if (!in_array($dir, $this->fallbacks, true)) {
                array_push($this->fallbacks, $dir);
            }
        }
    }

    public function addSymlinks($symlinks, $file = false)
    {
        if (is_array($symlinks) && false !== $file) {
            foreach ($symlinks as $symlink) {
                $this->symlinks[$symlink] = $file;
            }
        } elseif (is_array($symlinks)) {
            foreach ($symlinks as $symlink => $file) {
                $this->symlinks[$symlink] = $file;
            }
        } else {
            $this->symlinks[$symlinks] = $file;
        }
    }

    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            require $file;
        }
    }

    public function findFile($class)
    {
        $class = trim($class, NS);
        $namespace = false;

        if (false !== $pos = strrpos($class, NS)) {
            $namespace = substr($class, 0, $pos);
            $class = substr($class, $pos + 1);
        }

        $relativePath = str_replace(NS, DS, $namespace) . DS . $class;
        $patternExtension = implode(',', $this->extensions);

        foreach ($this->fallbacks as $dir) {
            $files = glob($dir. DS . $relativePath .'{'. $patternExtension .'}', GLOB_BRACE);
            foreach ($files as $file) {
                if (is_file($file)) {
                    return $file;
                }
            }
        }

        // Перебор ссылок классов на конкретные файлы
        foreach ($this->symlinks as $symlink => $file) {
            if (0 !== strpos($class, $symlink)) {
                continue;
            }
            if (is_file($file)) {
                unset($this->symlinks[$symlink]);
                return $file;
            }
        }
    }
}
