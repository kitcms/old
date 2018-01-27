<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @link      http://kitcms.ru
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Copyright (c) Kit.team
 */

namespace Classes\Database;

use Model as ActiveRecord;

use ORMWrapper;
use ArrayAccess;
use IteratorAggregate;
use Countable;

class Model extends ActiveRecord implements ArrayAccess, IteratorAggregate, Countable
{
    protected $_table_use_short_name = true;

    protected $fields = array(
        array(
            'field' => 'id',
            'type' => 'int(10) unsigned',
            'key' => 'pri',
            'extra' => 'auto_increment',
            'comment' => 'Идентификатор'
        ),
        array(
            'field' => 'created',
            'type' => 'datetime',
            'null' => 'yes',
            'comment' => 'Время создания'
        ),
        array(
            'field' => 'updated',
            'type' => 'datetime',
            'null' => 'yes',
            'comment' => 'Время обновления'
        ),
        array(
            'field' => 'user',
            'type' => 'int(10) unsigned',
            'key' => 'mul',
            'default' => '0',
            'comment' => 'Идентификатор пользователя'
        ),
        array(
            'field' => 'active',
            'type' => 'tinyint(1)',
            'key' => 'mul',
            'default' => '1',
            'comment' => 'Статус объекта'
        ),
        array(
            'field' => 'priority',
            'type' => 'int(10) unsigned',
            'default' => '0',
            'comment' => 'Приоритет объекта'
        )
    );

    public function __toString()
    {
        return (string) $this->id();
    }

    public function offsetExists($key)
    {
        return $this->isDirty($key);
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
        return count($this->asArray());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->asArray());
    }
}
