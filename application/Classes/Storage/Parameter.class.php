<?php
/*
 * Хранилище значений
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Storage;

class Parameter
{
    protected $parameters = array();

    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    public function add(array $parameters = array())
    {
        $this->parameters = array_replace_recursive($this->parameters, $parameters);
    }

    public function prepend($key, $value)
    {
        if (!isset($this->parameters[$key])) {
            $this->parameters[$key] = array();
        }
        if (!is_array($this->parameters[$key])) {
            $this->parameters[$key] = (array) $this->parameters[$key];
        }
        array_unshift($this->parameters[$key], $value);
    }

    public function append($key, $value)
    {
        if (!isset($this->parameters[$key])) {
            $this->parameters[$key] = array();
        }
        if (!is_array($this->parameters[$key])) {
            $this->parameters[$key] = (array) $this->parameters[$key];
        }
        array_push($this->parameters[$key], $value);
    }

    public function replace(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    public function set($key, $value)
    {
        if (false == $key) {
            $key = max($this->keys()) + 1;
        }
        $this->parameters[$key] = $value;
    }

    public function get($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    public function all()
    {
        return $this->parameters;
    }

    public function keys()
    {
        return array_keys($this->parameters);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    public function reset()
    {
        $this->parameters = array();
    }
}
