<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Database;

use Classes\TransferProtocol\HyperText\Request;
use PDO;

class Schema extends ORM
{
    protected $_instance_id_column = 'name';
    protected $_class_name = __NAMESPACE__ .'\Model';
    protected $_field_name;

    protected $aspects = array(
        'identifier' => 'bigint(20) unsigned',
        'integer' => 'int(11)',
        'string' => 'varchar(255)',
        'text' => 'longtext',
        'boolean' => 'tinyint(1)',
        'file' => 'longblob',
        'join' => 'char(20)',
        'group' => 'char(255)',
    );

    public function __construct($table_name = '', $data = array(), $connection_name = self::DEFAULT_CONNECTION) {
        $this->_table_name = $table_name;
        $this->_data = $data;

        $this->_connection_name = $connection_name;
        self::_setup_db_config($connection_name);
    }

    public static function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION) {
        self::_setup_db($connection_name);
        return new self($table_name, array(), $connection_name);
    }

    public function field()
    {
        $instance = self::for_table($this->name ?: $this->_table_name);
        $instance->_where_conditions = array();
        $instance->useIdColumn('field');
        return $instance;
    }

    public function count($column = '*')
    {
        $rows = $this->findMany();
        return count($rows);
    }

    public function findArray()
    {
        $array = parent::findArray();
        if ('field' === $this->_getIdColumnName()) {
            foreach ($array as $key => $data) {
                $array[$key]['table'] = $this->_table_name;
                if (false === ($array[$key]['aspect'] = array_search($data['type'], $this->aspects))) {
                    $array[$key]['aspect'] = $data['type'];
                }
            }
        }
        return $array;
    }

    public function create($data=null) {
        parent::create($data);

        if ('field' === $this->_getIdColumnName()) {
            $fill = array_fill_keys(array('field','type','collation','null','key',
            'default','extra','privileges','after','first','model','apply','table'), null);
        } else {
            $fill = array_fill_keys(array('name','engine','version','row_format',
                'rows','avg_row_length','data_length','max_data_length','index_length',
                'data_free','auto_increment','create_time','update_time','check_time',
                'collation','checksum','create_options', 'model', 'apply'), null);
        }
        $data = array_diff_key($this->_data, $fill);
        foreach (array_keys($data) as $key) {
            unset($this->_dirty_fields[$key]);
        }
        $this->_dirty_fields['comment'] = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $this;
    }

    public function save()
    {
        global $dir;
        if ('field' === $this->_getIdColumnName()) {
            if (!preg_match("/^[[:alnum:]-_.]+$/iu", $this->field) || preg_match("/^[\d-_.]+$/", $this->field)) {
                return false;
            }
            // Проверка на дублирование полей
            if (($this->isNew() || ($this->_field_name !== $this->field)) && $this->findOne($this->field)) {
                return false;
            }

            if (!$this->isNew() && $this->_field_name !== $this->field) {
                // Нахождение зависимостей
                // Files
                $directory = mb_strtolower("{$dir['public']}/files/{$this->_table_name}/*/{$this->_field_name}");
                $directories = glob_recursive($directory);
                $dependences = array(
                    'rename' => array(),
                    'query' => array("UPDATE `{$this->_table_name}` ".
                               "SET `{$this->field}` = ".
                               "REPLACE(`{$this->field}`, ".
                               "'\\\\/". mb_strtolower($this->_field_name) ."\\\\/', ".
                               "'\\\\/". mb_strtolower($this->field) ."\\\\/') ".
                               "WHERE `{$this->field}` ".
                               "LIKE '%\\\\\\\\/". mb_strtolower($this->_field_name) ."\\\\\\\\/%'"
                    )
                );
                foreach ($directories as $directory) {
                    if (false !== $pos = strrpos(rtrim($directory, DS), '/')) {
                        array_push($dependences['rename'], array($directory, substr($directory, 0, $pos + 1) . $this->field));
                    }
                }
                // Join column
                $self = new self('');
                foreach ($self->findMany() as $table) {
                    foreach ($table->field()->whereLike('comment', '%"aspect":"join"%"join":"'. $this->_table_name .'","column":"'. $this->_field_name .'"%')->findMany() as $field) {
                        $dependences['column'][$table->name][] = $field;
                    }
                }
            }
        } else {
            if (!preg_match("/^[[:alnum:]-_.]+$/iu", $this->name) || preg_match("/^[\d-_.]+$/", $this->name)) {
                return false;
            }
            if (!$this->isNew() && $this->_table_name !== $this->name) {
                // Нахождение зависимостей
                // Infobox & files
                $dependences = array(
                    'section' => Model::factory('Section')->whereLike('infobox', '%"model":"'. $this->_table_name .'"%')->findMany(),
                    'rename' => array(
                        array(
                            mb_strtolower("{$dir['public']}/files/{$this->_table_name}"),
                            mb_strtolower("{$dir['public']}/files/{$this->name}")
                        )
                    ),
                    'query' => array()
                );
                // Join
                $self = new self('');
                foreach ($self->findMany() as $table) {
                    foreach ($table->field()->whereLike('comment', '%"aspect":"join"%"join":"'. $this->_table_name .'"%')->findMany() as $field) {
                        $dependences['join'][$table->name][] = $field;
                    }
                }
                // Query
                if ($fields = self::forTable($this->_table_name)->field()->where('type', 'longblob')->findArray()) {
                    foreach ($fields as $field) {
                        array_push(
                            $dependences['query'],
                            "UPDATE `{$this->name}` ".
                            "SET `{$field['field']}` = ".
                            "REPLACE(`{$field['field']}`, ".
                                "'files\\\\/". mb_strtolower($this->_table_name) ."\\\\/', ".
                                "'files\\\\/". mb_strtolower($this->name) ."\\\\/') ".
                            "WHERE `{$field['field']}` ".
                            "LIKE '%files\\\\\\\\/". mb_strtolower($this->_table_name) ."\\\\\\\\/%'"
                        );
                    }
                }
            }
        };
        if (parent::save()) {
            // Join
            if (isset($dependences['join'])) {
                foreach ((array) $dependences['join'] as $fields) {
                    foreach ((array) $fields as $field) {
                        $field->set('join', $this->name)->save();
                    }
                }
            }
            // Join column
            if (isset($dependences['column'])) {
                foreach ((array) $dependences['column'] as $fields) {
                    foreach ((array) $fields as $field) {
                        $field->set('column', $this->field)->save();
                    }
                }
            }
            // Infobox
            if (isset($dependences['section'])) {
                foreach ((array) $dependences['section'] as $section) {
                    $infobox = $section->get('infobox');
                    $infobox['model'] = $this->name;
                    $section->set('infobox', $infobox)->save();
                }
            }
            // Files
            if (isset($dependences['rename'])) {
                foreach ((array) $dependences['rename'] as $dependence) {
                    if (is_array($dependence)) {
                        // FIXME Добавить проверку на существование и наличие необходимых прав в дальнейшем
                        @rename(current($dependence), next($dependence));
                    }
                }
                foreach ((array) $dependences['query'] as $query) {
                    // Может использовать _execute ?
                    $this->getDb()->query($query);
                }
            }
            return true;
        }
        return false;
    }

    public function delete()
    {
        global $dir;
        $dependences = array();
        if ('field' === $this->_getIdColumnName()) {
            // Join column
            $self = new self('');
            foreach ($self->findMany() as $table) {
                foreach ($table->field()->whereLike('comment', '%"aspect":"join"%"join":"'. $this->_table_name .'","column":"'. $this->_field_name .'"%')->findMany() as $field) {
                    $dependences['column'][$table->name][] = $field;
                }
            }
            $query = array('ALTER TABLE', $this->_quoteIdentifier($this->_table_name), 'DROP', $this->_quoteIdentifier($this->field));
            $directory = mb_strtolower("{$dir['public']}/files/{$this->_table_name}/*/{$this->field}");
            $dependences['file'] = glob_recursive("{$directory}/*");
            $dependences['dir'] = glob_recursive("{$directory}");
        } else {
            $query = array('DROP TABLE', $this->_quoteIdentifier($this->name));
            // FIXME Проверка на наличие таблицы
            $dependences['section'] = @Model::factory('Section')->whereLike('infobox', '%"model":"'. $this->_table_name .'"%')->findMany();
            $directory = mb_strtolower("{$dir['public']}/files/{$this->name}");
            $dependences['file'] = glob_recursive("{$directory}/*");
            array_unshift($dependences['file'], $directory);
        }
        $data = is_array($this->id(true)) ? array_values($this->id(true)) : array($this->id(true));
        if (self::_execute(join(" ", $query), $data, $this->_connection_name)) {
            if (isset($dependences['section'])) {
                foreach ((array) $dependences['section'] as $section) {
                    $section->set('infobox', '')->save();
                }
            }
            // Join column
            if (isset($dependences['column'])) {
                foreach ((array) $dependences['column'] as $fields) {
                    foreach ((array) $fields as $field) {
                        $field->set('column', 'id')->save();
                    }
                }
            }
            if (isset($dependences['file'])) {
                foreach ($dependences['file'] as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    } else {
                        $dependences['dir'][] = $file;
                    }
                }
            }
            if (isset($dependences['dir'])) {
                $dependences['dir'] = array_reverse($dependences['dir']);
                foreach ($dependences['dir'] as $directory) {
                    if (is_dir($directory)) {
                        rmdir($directory);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function hydrate($data=array())
    {
        if ('field' === $this->_getIdColumnName()) {
            $this->_field_name = $data['field'];
            if (!isset($data['aspect'])) {
                $data['aspect'] = $data['type'];
                 if ($aspect = array_search($data['type'], $this->aspects)) {
                     $data['aspect'] = $aspect;
                 }
            }
            if (!isset($data['type'])) {
                $data['type'] = $data['aspect'];
                if (key_exists($data['aspect'], $this->aspects)) {
                    $data['type'] = $this->aspects[$data['aspect']];
                }
            }
        }
        $this->_data = $data;
        return $this;
    }

    public function getDirty($key) {
        if (is_array($key)) {
            $result = array();
            foreach($key as $column) {
                $result[$column] = isset($this->_dirty_fields[$column]) ? $this->_dirty_fields[$column] : null;
            }
            return $result;
        } else {
            return isset($this->_dirty_fields[$key]) ? $this->_dirty_fields[$key] : null;
        }
    }

    protected function _set_orm_property($key, $value = null, $expr = false) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $field => $value) {
            // Корректировка полня type на основании значения поля aspect
            if ('aspect' === $field) {
                $this->_data['type'] = $value;
                if (key_exists($value, $this->aspects)) {
                    $this->_data['type'] = $this->aspects[$value];
                }
            }
            $this->_data[$field] = $value;
            $this->_dirty_fields[$field] = $value;
            if (false === $expr and isset($this->_expr_fields[$field])) {
                unset($this->_expr_fields[$field]);
            } else if (true === $expr) {
                $this->_expr_fields[$field] = true;
            }
        }
        if ('field' === $this->_getIdColumnName()) {
            $fill = array_fill_keys(array('field','type','collation','null','key',
                'default','extra','privileges','after','first','model','apply', 'table'), null);
        } else {
            $fill = array_fill_keys(array('name','engine','version','row_format',
                'rows','avg_row_length','data_length','max_data_length','index_length',
                'data_free','auto_increment','create_time','update_time','check_time',
                'collation','checksum','create_options', 'model', 'apply'), null);
        }
        $data = array_diff_key($this->_data, $fill);
        foreach (array_keys($data) as $key) {
            unset($this->_dirty_fields[$key]);
        }
        $this->_dirty_fields['comment'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
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
                'COMMENT \''. $this->getDirty('comment') .'\'',
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

    protected function _build_update()
    {
        $fragments = array(
            'ALTER TABLE '. $this->_quoteIdentifier($this->_table_name)
        );
        $afters = array();
        if ('name' === $this->_getIdColumnName()) {
            foreach ($this->_dirty_fields as $key => $value) {
                switch (strtoupper($key)) {
                    case 'NAME':
                        $afters = array(
                            '; RENAME TABLE '. $this->_quoteIdentifier($this->_table_name),
                            'TO '. $this->_quoteIdentifier($this->name) .';'
                        );
                        break;
                    case 'COLLATION':
                        array_push($fragments, 'DEFAULT CHARACTER SET'. $this->_quoteIdentifier(current(explode('_', $value))));
                        array_push($fragments, 'COLLATE'. $this->_quoteIdentifier($value));
                        break;
                    case 'COMMENT':
                        array_push($fragments, 'COMMENT \''. $value .'\'');
                        break;
                    case 'ENGINE':
                        array_push($fragments, $key .'=', $value);
                    default:
                        // FIXME
                }
            }
        }
        if ('field' === $this->_getIdColumnName()) {
            array_push($fragments, 'CHANGE');
            array_push($fragments, $this->_quoteIdentifier($this->_field_name));
            array_push($fragments, $this->_buildUpdateField());
        }
        return $this->_joinIfNotEmpty(" ", $fragments) . $this->_joinIfNotEmpty(" ", $afters);
    }

    protected function _build_field()
    {
        $fragments = array(
            $this->_quoteIdentifier($this->get('field')),
            $this->get('type'), // strtoupper($this->get('type'))
            (filter_var($this->get('null'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? null : 'NOT NULL'),
            (strlen($this->get('default')) ? 'default '. self::getDb()->quote($this->get('default')) : null),
            strtoupper($this->get('extra')),
            'COMMENT '. self::getDb()->quote($this->getDirty('comment')), // FIXME
            ($this->get('after') ? 'after '. $this->_quoteIdentifier($this->get('after')) : null),
            ($this->get('first') ? 'first' : null)
        );
        if ($key = strtoupper($this->get('key'))) {
            $name = $this->_quoteIdentifier($this->get('field'));
            if (in_array($key, array('PRI', 'PRIMARY'))) {
                $key = 'PRIMARY KEY ('. $name .')';
            } elseif (in_array($key, array('UNI', 'UNIQUE'))) {
                $key = 'UNIQUE KEY '. strtoupper($name) .' ('. $name .')';
            } else {
                $key = 'KEY '. strtoupper($name) .' ('. $name .')';
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
            'COMMENT '. self::getDb()->quote($this->getDirty('comment')), // FIXME
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
                $row['comment'] = '';
                $row = array_merge($row, $parts);
            }
            if ('field' === $this->_getIdColumnName()) {
                $row['table'] = $this->_table_name;
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

    protected function _create_instance_from_row($row)
    {
        $instance = self::for_table(isset($row['name']) ? $row['name'] : $this->_table_name, $this->_connection_name);
        $instance->use_id_column($this->_instance_id_column);
        $instance->hydrate($row);
        return $instance;
    }

    public function uploadConfig()
    {
        $request = Request::fromGlobals();

        $dir = "/tmp/{$this->table}/{$this->field}/";
        if (isset($_SESSION['user']['id']) && $userId = $_SESSION['user']['id']) {
            $dir = "/tmp/{$this->table}/{$userId}/{$this->field}/";
        }
        $dir = mb_strtolower($dir);

        $versions = array(
            '' => array(
                'min_width' => $this->increase ? ($this->min_width ?: ($this->max_width ?: null)) : null,
                'min_height' => $this->increase ? ($this->min_height ?: ($this->max_height ?: null)) : null,
                'max_width' => $this->reduction ? ($this->max_width ?: ($this->min_width ?: null)) : null,
                'max_height' => $this->reduction ? ($this->max_height ?: ($this->min_height ?: null)) : null,
                'compression' => true,
                'auto_orient' => true,
                'color_thief' => true,
                'crop' => false,
                'no_cache' => false,
                'jpeg_quality' => 75,
                'filter' => 3,
                'strip' => true
            )
        );

        $options = array(
            'image_library' => 1,
            'image_versions' => $versions,

            'upload_dir' => $GLOBALS['dir']['public'] . $dir,
            'web_import_temp_dir' => $GLOBALS['dir']['public'] . $dir,
            'upload_url' => $request->getBasePath() . $dir,

            'accept_file_types' => $this->types ? '/\.'. $this->types .'$/i' : '/.+$/i',
            'max_number_of_files' => $this->number ? intval($this->number) : null,
            'min_file_size' => 0,
            'min_width' => $this->increase ? null : $this->min_width,
            'min_height' => $this->increase ? null : $this->min_height,
            'max_width' => $this->reduction ? null : $this->max_width,
            'max_height' => $this->reduction ? null : $this->max_height
        );

        return $options;
    }
}
