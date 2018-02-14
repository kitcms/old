<?php
/*
 * Загрузчик компонента
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

namespace Classes;

use Fenom;
use elFinder;
use elFinderConnector;

if (isset($views) && $views instanceof Fenom) {
    // Отключение кеширования компилированных шаблонов и другие настройки
    $views->setOptions(array('disable_cache' => true, 'force_include' => true, 'auto_reload' => true, 'strip' => true));

    // Добавление внутреннего источника шаблонов
    $provider = new Template\Provider(__DIR__ .'/Views');
    $views->addProvider('component', $provider);

    $views->addAccessorSmart("component", "component", Template\Engine::ACCESSOR_PROPERTY);
    $views->component = $request->getBasePath() .'/admin';
    if ($views->site) {
        $views->component = $request->getBasePath(). '/' . $views->site->dashboard;
    }

    // Настройка файлового менеджера
    function access($attr, $path, $data, $volume) {
        return (strpos(basename($path), '.') === 0 || pathinfo($path, PATHINFO_EXTENSION) === 'php') ? !($attr == 'read' || $attr == 'write') : null;
    }
    $views->addFunction('elfinder', function ($options) {
        $elFinder = new elFinderConnector(new elFinder($options));
        return $elFinder->run();
    });

    // Сопоставление шаблона с маршрутом
    $path = substr(trim($request->getPath(), '/'), strlen(substr($views->component, strlen($request->getBasePath()))));
    $template = $path .'/'. $request->getBaseName();
    if ($provider->templateExists($template)) {
        if ($mimeType = $request->getMimeType()) {
            header('Content-Type: '. $mimeType);
        }
        if (preg_match('/^assets\\//', $template)) {
            echo $provider->getSource($template, $time);
        } else {
            $views->display(array('component:'. $template, 'component:index.html'));
        }
    }
}
