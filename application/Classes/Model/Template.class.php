<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes\Model;

use Classes\Database\Model;

class Template extends Model
{
    protected $_table = 'Template';

    protected $fields = array(
        array(
            'title' => 'Идентификатор родительского объекта',
            'field' => 'parent',
            'type' => 'bigint(20) unsigned',
            'key' => 'mul',
            'default' => '0'
        ),
        array(
            'title' => 'Материализованный путь',
            'field' => 'path',
            'type' => 'varchar(255)'
        ),
        array(
            'title' => 'Название макета',
            'field' => 'title',
            'type' => 'varchar(255)',
            'group' => 'main'
        ),
        array(
            'title' => 'Описание',
            'field' => 'description',
            'type' => 'text',
            'null' => 'yes',
            'group' => 'main'
        )
    );

    public function parent()
    {
        return $this->hasOne(__CLASS__, 'id', 'parent');
    }

    public function parents()
    {
        $paths = explode('/', $this->path);
        array_pop($paths);
        $where = array(array_shift($paths));
        foreach ($paths as $path) {
            array_push($where, end($where) .'/'. $path);
        }
        return $this->factory(__CLASS__)->whereIn('path', $where);
    }

    public function children()
    {
        return $this->hasMany(__CLASS__, 'parent', 'id');
    }

    public function childrens()
    {
        return $this->factory(__CLASS__)->whereNotIn('id', (array) $this->id)->whereLike('path', $this->path .'%');
    }

    public function save()
    {
        // Новый объект, изменение ключевого слова или родительского элемента
        if ($this->isNew() || $this->isDirty('parent') || $this->isDirty('path')) {
            // Если новая запись, определяем следующий идентификатор
            if (!$this->id) {
                $schema = $this->schema()->findOne();
                $this->id = $schema->auto_increment;
            }
            $this->set('path', $this->id);
            if ($parent = $this->parent()->findOne()) {
                $this->set('path', $parent->path .'/'. $this->id);
            }
        }
        // Проверка на уникальность
        if ($this->isDirty('path')) {
            $instance = $this->factory(__CLASS__)->where('path', $this->get('path'));
            if (false === $this->isNew()) {
                $instance->whereNotIn('id', (array) $this->id);
            }
            if ($instance->findOne()) {
                return false;
            }
        }
        // Сохранение данных в файл
        if ($this->isNew() || $this->isDirty('source')) {
            // TODO Добавить проверку существования файла и прав на запись
        	$path = realpath('Views/Template/'). '/'. $this->id .'.tpl';
            if (false === @file_put_contents($path, $this->get('source'))) {
                return false;
            }
        }
        // Массивы зависимых объектов
        $dependences = array();
        if ($this->isDirty('parent') || $this->isDirty('path')) {
            array_push($dependences, $this->children()->findMany());
        }
        if (parent::save()) {
            foreach ((array) $dependences as $dependence) {
                foreach ((array) $dependence as $depend) {
                    $depend->set('path', null)->save();
                }
            }
            return true;
        }
        return false;
    }

    public function delete()
    {
        // Массивы зависимых объектов
        $dependences = array();
        $updates = array();
        array_push($dependences, $this->children()->findMany());
        array_push($updates, $this->factory('Site')->where('template', $this->get('id'))->findMany());
        array_push($updates, $this->factory('Section')->where('template', $this->get('id'))->findMany());
        $ids = array($this->get('id'));
        if (parent::delete()) {
            // Обновление зависимых объектов
            foreach ((array) $updates as $update) {
                foreach ((array) $update as $object) {
                    $object->set('template', null)->save();
                }
            }
            // Удаление зависимых объектов
            foreach ((array) $dependences as $dependence) {
                foreach ((array) $dependence as $depend) {
                    array_push($ids, $depend->get('id'));
                    $depend->delete();
                }
            }
            // Пересохранить все зависимости у сайтов и разделов
            // Удаление файлов и директорий
            if ($path = realpath('Views/Template/'. $this->keyword)) {
                unlink($path);
            }
            return true;
        }
        return false;
    }

    protected function _create_model_instance($orm) {
        $data = $this->asArray();
        if (false === $this->isDirty('keyword')) {
            $parts = explode('/', $this->get('path'));
            $keyword = array_pop($parts);
            $data['keyword'] = $keyword .'.tpl';
        }
        if (false === $this->isDirty('source')) {
            if ($path = realpath('Views/Template/'. $data['keyword'])) {
                $data['source'] = file_get_contents($path);
            }
        }
        $this->orm->hydrate($data);
    }
}
