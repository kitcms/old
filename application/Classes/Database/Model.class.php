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

use ArrayAccess;
use IteratorAggregate;
use Countable;

class Model extends ActiveRecord implements ArrayAccess, IteratorAggregate, Countable
{
    protected $_table_use_short_name = true;

    protected $fields = array(
        array(
            'field' => 'id',
            'type' => 'bigint(20) unsigned',
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
            'type' => 'bigint(20) unsigned',
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
            'type' => 'bigint(20) unsigned',
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

    public function schema()
    {
        $class = get_called_class();
        if (null === ($table = $this->_get_static_property($class, '_table')) && null !== $this->orm) {
            $table = $this->orm->get_table_name();
        }
        $instance = Schema::for_table($table);
        $instance->where('name', $table);
        $instance->set_class_name($class);
        return $instance;
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

    public function save()
    {
        global $dir;
        $schema = $this->schema()->findOne();
        $table = $schema->get('name');
        $fields = $schema->field()->findArray();

        // Исключение не используемых данных
        foreach ($this->asArray() as $field => $value) {
            if (false === ($key = array_search($field, array_column($fields, 'field')))) {
                unset($this->$field);
            } else {
                // Модификация значений
                switch ($fields[$key]['aspect']) {
                    case 'double':
                        $value = str_replace(array(',',' '), array('.',''), $this->get($field));
                        $this->set($field, $value);
                        break;
                    case 'datetime':
                        $value = $this->get($field);
                        $this->set($field, date('Y-m-d H:i:s', strtotime($value)));
                }
            }
        }
        // FIXME
        if (null === $id = $this->id()) {
            $id = $schema->auto_increment;
            $this->set('user', isset($_SESSION['user']) ? $_SESSION['user'] : false);
        }
        $this->set('created', $this->get('created') ?: date("Y-m-d H:i:s"));
        $this->set('updated', date("Y-m-d H:i:s"));

        // Проверка на заполнение всех необходимых полей
        foreach ($fields as $field) {
            // Если поле не имеет первичного ключа
            if (false === stristr($field['key'], 'pri')) {
                // и не может быть пустым
                if (false === filter_var($field['null'], FILTER_VALIDATE_BOOLEAN)) {
                    // так же не имеет значения по умолчанию
                    if ((null == $this->get($field['field'])) && (null === $field['default'])) {
                        return false;
                    }
                }
            }
            if ('' === $this->get($field['field'])) {
                if (true === filter_var($field['null'], FILTER_VALIDATE_BOOLEAN) && null === $field['default']) {
                    // Установка значения null
                    $this->set($field['field'], null);
                } else {
                    // Установка значения по умолчанию
                    $this->set($field['field'], $field['default']);
                }
            }
            if ('file' === $field['aspect']) {
                $path = mb_strtolower("files/{$table}/{$id}/{$field['field']}");
                $directory = $dir['public'] . DS . $path;
                $currentFiles = array();
                if (false === is_dir($directory)) {
                    mkdir($directory, 0755, true);
                } else {
                    $currentFiles = array_diff(scandir($directory), array('..', '.'));
                }
                $files = $this->get($field['field']);
                $temporaries = array();
                foreach ((array) $files as $key => $file) {
                    $url = urldecode($file['url']);
                    $filename = $dir['public'] . DS . trim($url, DS);
                    $basename = basename($url);
                    if (false === is_file($filename)) {
                        unset($files[$key]);
                    } else {
                        // Перемещение файлов в предопределенную директорию
                        $rename = $directory . DS . $basename;
                        if (($filename !== $rename) && rename($filename, $rename)) {
                            $temporaries[] = pathinfo($filename, PATHINFO_DIRNAME);
                            $files[$key]['url'] = $path . DS . $basename;
                        }
                    }
                    if (false !== ($key = array_search($basename, $currentFiles))) {
                        unset($currentFiles[$key]);
                    }
                }
                $this->set($field['field'], $files);
                foreach ($currentFiles as $file) {
                    $filename = $directory . DS . $file;
                    if (is_file($filename)) {
                        unlink($filename);
                    }
                }
                // Удаление временных файлов
                foreach ($temporaries as $directory) {
                    $files = array_diff(scandir($directory), array('..', '.'));
                    foreach ($files as $file) {
                        $filename = $directory . DS . $file;
                        if (is_file($filename)) {
                            unlink($filename);
                        }
                    }
                }
            }
        }
        return parent::save();
    }

    public function delete()
    {
        global $dir;
        $table = $this->orm->getTableName();
        $directory = mb_strtolower("{$dir['public']}/files/{$table}/{$this->id}");
        $files = glob_recursive($directory .'/*');
        array_push($files, $directory);
        if (parent::delete()) {
            // Удаление зависимых файлов и директорий
            foreach ($files as $key => $file) {
                if ((is_file($file) && unlink($file)) || false === is_dir($file)) {
                    unset($files[$key]);
                }
            }
            foreach ($files as $file) {
                rmdir($file);
            }
            return true;
        }
        return false;
    }

    protected function _has_one_or_many($associated_class_name, $foreign_key_name=null, $foreign_key_name_in_current_models_table=null, $connection_name=null) {
        $base_table_name = self::_get_table_name(get_class($this));
        $foreign_key_name = self::_build_foreign_key_name($foreign_key_name, $base_table_name);
        $where_value = '';
        if(is_null($foreign_key_name_in_current_models_table)) {
            $where_value = $this->id();
        } else {
            $where_value = $this->$foreign_key_name_in_current_models_table;
        }
        return self::factory($associated_class_name, $connection_name)->where($foreign_key_name, $where_value);
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

    public function __call($method, $arguments)
    {
        try {
            return parent::__call($method, $arguments);
        } catch (\ParisMethodMissingException $e) {
            if (function_exists('get_called_class')) {
                $model = self::factory(get_called_class());
                if (method_exists($model, $method)) {
                    return call_user_func_array(array($model, $method), $arguments);
                }
            }
        }
    }
}
