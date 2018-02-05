<?php
/*
 * Получение и разбор текущего HTTP-запроса
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\TransferProtocol\HyperText;

use Classes\Storage\Container;

class Request
{
    public $headers;
    public $request;
    public $query;
    public $files;
    public $server;
    public $cookies;

    public function __construct(array $query = array(), array $request = array(), array $cookies = array(), array $files = array(), array $server = array())
    {
        $this->query = new Container($query);
        $this->request = new Container($request);
        $this->cookies = new Container($cookies);
        $this->files = new Container($files);
        $this->server = new Server($server);
        $this->headers = new Container($this->server->getHeaders());
    }

    public static function fromGlobals()
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    public function getScriptName()
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }

    public function getHost()
    {
        if (!$host = $this->headers->get('HOST')) {
            if (!$host = $this->server->get('SERVER_NAME')) {
                $host = $this->server->get('SERVER_ADDR', '');
            }
        }
        return strtolower(preg_replace('/:\d+$/', '', trim($host)));
    }

    public function getBasePath()
    {
        $basePath = str_replace(DS, '/', pathinfo($this->getScriptName(), PATHINFO_DIRNAME));
        return rtrim($basePath, '/');
    }

    public function getPath()
    {
        $uri = rawurldecode($this->server->get('REQUEST_URI'));
        $path = substr(parse_url($uri, PHP_URL_PATH), strlen($this->getBasePath()));
        if (false !== strrpos($path, '.')) {
            $path = pathinfo($path, PATHINFO_DIRNAME);
            return str_replace(DS, '/', $path);
        }
        return rtrim($path, '/');
    }

    public function getBaseName()
    {
        $uri = rawurldecode($this->server->get('REQUEST_URI'));
        $path = parse_url($uri, PHP_URL_PATH);
        if (false !== strrpos($path, '.')) {
            return pathinfo($path, PATHINFO_BASENAME);
        }
        return false;
    }

    public function getFileName()
    {
        $uri = rawurldecode($this->server->get('REQUEST_URI'));
        $path = parse_url($uri, PHP_URL_PATH);
        if (false !== strrpos($path, '.')) {
            return pathinfo($path, PATHINFO_FILENAME);
        }
        return false;
    }

    public function getExtension()
    {
        $uri = rawurldecode($this->server->get('REQUEST_URI'));
        $path = parse_url($uri, PHP_URL_PATH);
        if (false !== strrpos($path, '.')) {
            return pathinfo($path, PATHINFO_EXTENSION);
        }
        return false;
    }

    public function getMimeType()
    {
        if ($extension = $this->getExtension()) {
            $mime = new MimeType();
            return $mime->getType($extension);
        }
        return false;
    }
}
