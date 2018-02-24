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
use Classes\Database\Schema;

class Section extends Model
{
    protected $_table = 'Section';

    protected $fields = array(
        array(
            'title' => 'Идентификатор сайта',
            'field' => 'site',
            'type' => 'bigint(20) unsigned',
            'key' => 'mul',
            'default' => '0'
        ),
        array(
            'title' => 'Идентификатор родительского объекта',
            'field' => 'parent',
            'type' => 'bigint(20) unsigned',
            'key' => 'mul',
            'default' => '0'
        ),
        array(
            'title' => 'Идентификатор макета дизайна',
            'field' => 'template',
            'type' => 'bigint(20) unsigned',
            'null' => 'yes',
            'key' => 'mul',
            'group' => 'template'
        ),
        array(
            'title' => 'Материализованный путь',
            'field' => 'path',
            'type' => 'varchar(255)'
        ),
        array(
            'title' => 'Тип раздела',
            'field' => 'type',
            'type' => 'tinyint(1)',
            'null' => 'yes'
        ),
        array(
            'title' => 'Инфобокс',
            'field' => 'infobox',
            'type' => 'varchar(255)',
            'null' => 'yes',
            'group' => 'infobox'
        ),
        array(
            'title' => 'Название раздела',
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
        ),
        array(
            'title' => 'Метаинформация',
            'field' => 'meta',
            'type' => 'longtext',
            'null' => 'yes',
            'group' => 'meta'
        )
    );

    public function site()
    {
        return $this->hasOne(__NAMESPACE__ . NS .'Site', 'id', 'site');
    }

    public function template()
    {
        return $this->hasOne(__NAMESPACE__ . NS .'Template', 'id', 'template');
    }

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
        return $this->hasMany(__CLASS__, 'site', 'site')->whereIn('path', $where);
    }

    public function children()
    {
        return $this->hasMany(__CLASS__, 'parent', 'id');
    }

    public function childrens()
    {
        return $this->hasMany(__CLASS__, 'site', 'site')->whereNotIn('id', (array) $this->id)->whereLike('path', $this->path .'%');
    }

    public function infobox()
    {
        if ($infobox = $this->get('infobox')) {
            return $this->factory($infobox['model'])->where('section', $this->id);
        }
        return false;
    }

    public function save()
    {
        // Если новая запись, определяем следующий идентификатор
        if (!$this->id) {
            $schema = $this->schema()->findOne();
            $this->id = $schema->auto_increment;
        }
        if ($this->isDirty('keyword')) {
            if (!$keyword = $this->get('keyword')) {
                $keyword = $this->get('title');
            }
            // Очистка от лишних символов
            $keyword = preg_replace('~[^\\pL0-9_.]+~u', '-', $keyword);
            $keyword = mb_strtolower($keyword);
            $keyword = trim($keyword, "-");

            $site = $this->site()->findOne();

            if ('transliteration' === $site->config['url']) {

                // Транслитерация по правилам яндекса
                $cyr = array(
                    'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
                    'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
                    'ch','sh'
                );
                $lat = array(
                    'a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p',
                    'r','s','t','u','f','h','c','c+h','s+h','s+hc+h','','y','','eh','yu','ya',
                    'c+h','s+h'
                );
                $keyword = str_replace($cyr, $lat, $keyword);
                $pattern = '/(c|s|e|h)h/i';
                $replacement = '$1kh';
                while (preg_match($pattern, $keyword)) {
                    $keyword = preg_replace($pattern, $replacement, $keyword);
                }
                $pattern = '/(c|s)\+h/i';
                $replacement = '$1h';
                while (preg_match($pattern, $keyword)) {
                    $keyword = preg_replace($pattern, $replacement, $keyword);
                }

            }
            $this->set('keyword', $keyword);

            // Проверка на корректность
            //if (!preg_match("/^[[:alnum:]-_.]+$/iu", $keyword)) {
            //    return false;
            //}
        }
        $this->set('path', $this->get('keyword'));
        if ($parent = $this->parent()->findOne()) {
            // Если раздел имеет расширение, то нельзя добавлять подразделы
            if ($parent->extension) {
                return false;
            }
            if ($parent->type) {
                // Если родителький элемент
                $parent->path = $parent->keyword;
                if ($parent->parent && ($parental = $parent->parent()->findOne())) {
                    $parent->path = $parental->path .'/'. $parent->path;
                }
            }
            $this->set(array('path' => $parent->path .'/'. $this->keyword, 'site' => $parent->site));
        }
        // Проверка на уникальность
        if ($this->isDirty('path')) {
            $instance = $this->hasOne(__CLASS__, 'site', 'site')->where('path', $this->get('path'));
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
        	$path = realpath('Views/Section/'). '/'. $this->id .'.tpl';
            if (false === @file_put_contents($path, $this->get('source'))) {
                return false;
            }
        }
        // Массивы зависимых объектов
        $dependences = array();
        if ($this->isDirty('site') || $this->isDirty('parent') || $this->isDirty('path')) {
            // Для раздела, имеющего подразделы, нельзя задавать расширение
            if (($childrens = $this->children()->findMany()) && strrpos($this->keyword, '.')) {
                return false;
            }
            array_push($dependences, $childrens);
        }

        if (parent::save()) {
            if (($infobox = $this->get('infobox')) && ($model = $infobox['model'])) {
                $schema = new Schema($model);
                // Добалвение поля для привязки объектов к заданному разделу
                if (false === $schema->field()->findOne('section')) {
                    $schema->field()->create(array(
                        'field' => 'section',
                        'after' => 'priority',
                        'type' => 'int(10) unsigned',
                        'key' => 'mul',
                        'default' => '0',
                        'comment' => 'Идентификатор раздела'
                    ))->save();
                }
            }
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
        array_push($dependences, $this->children()->findMany());
        if (parent::delete()) {
            // Удаление зависимых объектов
            foreach ((array) $dependences as $dependence) {
                foreach ((array) $dependence as $depend) {
                    $depend->delete();
                }
            }
            // Удаление файлов и директорий
            if ($path = realpath('Views/Section/'. $this->id .'.tpl')) {
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
            $extension = false;
            if (false !== $pos = strrpos($keyword, '.')) {
                $extension = substr($keyword, $pos + 1);
                //$keyword = substr($keyword, 0, $pos);
            }
            $data = array_merge($data, array('keyword' => $keyword, 'extension' => $extension));
        }
        // Если главная страница удаляем информацию о материализованном пути
        if ($this->get('type')) {
            unset($data['path']);
        }
        if (false === $this->isDirty('source')) {
            if ($path = realpath("Views/Section/{$this->id}.tpl")) {
                $data['source'] = file_get_contents($path);
            }
        }
        $this->orm->hydrate($data);
    }
}
