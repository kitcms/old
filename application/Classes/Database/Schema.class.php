<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 * @version   0.1.0
 */

namespace Classes\Database;

use PDO;

class Schema extends ORM
{
    protected $_instance_id_column = 'name';
    protected $_class_name = __NAMESPACE__ .'\Model';

    protected $aspects = array(
        'integer' => 'int(11)',
        'string' => 'varchar(255)',
        'text' => 'longtext',
        'group' => 'varchar(255)',
        'boolean' => 'tinyint(1)',
        'file' => 'longblob',
        'join' => 'varchar(255)'
    );

    public function __construct($table_name = '', $data = array(), $connection_name = self::DEFAULT_CONNECTION) {
        $this->_table_name = $table_name;
        $this->_data = $data;

        $this->_connection_name = $connection_name;
        self::_setup_db_config($connection_name);
    }

    public static function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION) {
        self::_setup_db($connection_name);
        $instance = new self($table_name, array(), $connection_name);
        $instance->where('name', $table_name);
        return $instance;
    }

    public function field()
    {
        $instance = self::for_table($this->name ?: $this->_table_name);
        $instance->_where_conditions = array();
        $instance->useIdColumn('field');
        return $instance;
    }

    protected function _build_insert()
    {
        if ('name' === $this->_getIdColumnName()) {
            $model = new $this->_class_name();
            foreach ($model->fields() as $field) {
                $fragments[] = $this->field()->create($field)->_build_field();
            }
            $fragments = array(
                'CREATE TABLE IF NOT EXISTS '. $this->_quoteIdentifier($this->name),
                '('. $this->_joinIfNotEmpty(", ", $fragments) .')',
                'ENGINE '. $this->_quoteIdentifier($this->engine),
                'DEFAULT CHARACTER SET '. $this->_quoteIdentifier(current(explode('_', $this->collation))),
                'COLLATE '. $this->_quoteIdentifier($this->collation),
                'COMMENT \''. $this->comment .'\'',
                'AUTO_INCREMENT 1'
            );
            return $this->_joinIfNotEmpty(" ", $fragments);
        }
        $fragments = array(
            'ALTER TABLE',
            $this->_quoteIdentifier($this->_table_name),
            'ADD',
            $this->_build_update_field()
        );
        return $this->_joinIfNotEmpty(" ", $fragments);
    }

    protected function _build_field()
    {
        $fragments = array(
            $this->_quoteIdentifier($this->get('field')),
            $this->get('type'), // strtoupper($this->get('type'))
            (filter_var($this->get('null'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? null : 'NOT NULL'),
            (strlen($this->get('default')) ? 'default '. self::getDb()->quote($this->get('default')) : null),
            strtoupper($this->get('extra')),
            'COMMENT '. self::getDb()->quote($this->get('comment')), // FIXME
            ($this->get('after') ? 'after '. $this->_quoteIdentifier($this->get('after')) : null),
            ($this->get('first') ? 'first' : null)
        );
        if ($key = strtoupper($this->get('key'))) {
            if (in_array($key, array('PRI', 'PRIMARY'))) {
                $key = 'PRIMARY KEY ('. $this->_quoteIdentifier($this->get('field')) .')';
            } elseif (in_array($key, array('UNI', 'UNIQUE'))) {
                $key = 'UNIQUE KEY ('. $this->_quoteIdentifier($this->get('field')) .')';
            } else {
                $key = 'KEY ('. $this->_quoteIdentifier($this->get('field')) .')';
            }
        }
        return $this->_joinIfNotEmpty(', ', array($this->_joinIfNotEmpty(' ', $fragments), $key));
    }

    protected function _build_update_field()
    {
        $fragments = array(
            $this->_quoteIdentifier($this->get('field')),
            $this->get('type'), // strtoupper($this->get('type'))
            (filter_var($this->get('null'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? null : 'NOT NULL'),
            (strlen($this->get('default')) ? 'default '. self::getDb()->quote($this->get('default')) : null),
            strtoupper($this->get('extra')),
            'COMMENT '. self::getDb()->quote($this->get('comment')), // FIXME
            ($this->get('after') ? 'after '. $this->_quoteIdentifier($this->get('after')) : null),
            ($this->get('first') ? 'first' : null)
        );
        /*if ($key = strtoupper($this->get('key'))) {
            if (in_array($key, array('PRI', 'PRIMARY'))) {
                $key = 'ADD PRIMARY KEY ('. $this->_quoteIdentifier($this->get('field')) .')';
            } elseif (in_array($key, array('UNI', 'UNIQUE'))) {
                $key = 'ADD UNIQUE KEY ('. $this->_quoteIdentifier($this->get('field')) .')';
            } else {
                $key = 'ADD KEY ('. $this->_quoteIdentifier($this->get('field')) .')';
            }
        }*/
        $key = array();
        return $this->_joinIfNotEmpty(', ', array($this->_joinIfNotEmpty(' ', $fragments), $key));
    }

    protected function _build_select()
    {
        if ('name' === $this->_getIdColumnName()) {
            $fragments = array('SHOW TABLE STATUS', $this->_buildWhere());
            return $this->_joinIfNotEmpty(" ", $fragments);
        }
        if ('field' === $this->_getIdColumnName()) {
            $fragments = array('SHOW FULL COLUMNS FROM', $this->_quoteIdentifier($this->_table_name), $this->_buildWhere());
            return $this->_joinIfNotEmpty(" ", $fragments);
        }
        return parent::_build_select();
    }

    protected function _run()
    {
        $query = $this->_build_select();
        $caching_enabled = self::$_config[$this->_connection_name]['caching'];
        if ($caching_enabled) {
            $cache_key = self::_createCacheKey($query, $this->_values, $this->_table_name, $this->_connection_name);
            $cached_result = self::_checkQueryCache($cache_key, $this->_table_name, $this->_connection_name);
            if ($cached_result !== false) {
                return $cached_result;
            }
        }
        self::_execute($query, $this->_values, $this->_connection_name);
        $statement = self::getLastStatement();
        $rows = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $row = array_change_key_case($row);
            // Переопределение значений при условии, что они заданы в комментарии
            if (null !== ($parts = json_decode($row['comment'], true))) {
                $row = array_merge($row, $parts);
            }
            $rows[] = $row;
        }
        if ($caching_enabled) {
            self::_cacheQueryResult($cache_key, $rows, $this->_table_name, $this->_connection_name);
        }
        // reset Orm after executing the query
        $this->_values = array();
        $this->_result_columns = array('*');
        $this->_using_default_result_columns = true;
        return $rows;
    }

    protected function _create_instance_from_row($row) {
        $instance = self::for_table(isset($row['name']) ? $row['name'] : $this->_table_name, $this->_connection_name);
        $instance->use_id_column($this->_instance_id_column);
        $instance->hydrate($row);
        return $instance;
    }

    public function findArray()
    {
        $array = parent::findArray();
        if ('field' === $this->_getIdColumnName()) {
            foreach ($array as $key => $data) {
                if (false === ($array[$key]['aspect'] = array_search($data['type'], $this->aspects))) {
                    $array[$key]['aspect'] = $data['type'];
                }
            }
        }
        return $array;
    }
}
