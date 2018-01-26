<?php
/*
 * Контейнер для хранилища значений
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Storage;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

class Container extends Parameter implements ArrayAccess, IteratorAggregate, Countable
{
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    public function count()
    {
        return count($this->keys());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->all());
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __unset($key)
    {
        $this->remove($key);
    }
}
