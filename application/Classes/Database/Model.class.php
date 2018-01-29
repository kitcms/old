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

    public static function factory($table_name, $connection_name = null)
    {
        // FIXME
        $class_name = 'Classes'. NS .'Model'. NS . $table_name;
        if (false !== strrpos($table_name, NS)) {
            $class_name = $table_name;
        }
        if (class_exists($class_name)) {
            $table_name = self::_get_table_name($class_name);
        } else {
            $class_name = __CLASS__;
        }
        if ($connection_name == null) {
            $connection_name = self::_get_static_property(
                $class_name,
                '_connection_name',
                ORMWrapper::DEFAULT_CONNECTION
            );
        }
        $wrapper = ORMWrapper::for_table($table_name, $connection_name);
        $wrapper->set_class_name($class_name);
        $wrapper->use_id_column(self::_get_id_column_name($class_name));
        return $wrapper;
    }

    public static function fields()
    {
        $class = get_called_class();
        $fields = self::_get_static_property($class, 'fields');
        if (__CLASS__ !== $class) {
            $fields = array_merge(self::_get_static_property(__CLASS__, 'fields'), $fields);
        }
        return $fields;
    }

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
