<?php
/*
 * Определение MIME type
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\TransferProtocol\HyperText;

class MimeType
{
    protected $types = array();

    public function __construct()
    {
        if (is_readable('Storages/mime.types') && ($json = file_get_contents('Storages/mime.types'))) {
            $this->types = json_decode($json, true);
        } elseif (is_readable('/etc/mime.types')) {
            $file = fopen('/etc/mime.types', 'r');
            while(($line = fgets($file)) !== false) {
                $line = trim(preg_replace('/#.*/', '', $line));
                if(!$line) {
                    continue;
                }
                $parts = preg_split('/\s+/', $line);
                if(count($parts) == 1) {
                    continue;
                }
                $type = array_shift($parts);
                foreach($parts as $part) {
                    $this->types[$part] = $type;
                }
            }
            fclose($file);
            file_put_contents('Storages/mime.types', json_encode($this->types));
        }
    }

    public function getType($file) {
        if (false == ($extension = pathinfo($file, PATHINFO_EXTENSION))) {
            $extension = $file;
        }
        $extension = strtolower($extension);
        return isset($this->types[$extension]) ? $this->types[$extension] : null;
    }
}
