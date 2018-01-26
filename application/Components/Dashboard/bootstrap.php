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

if (isset($views) && $views instanceof Fenom) {
    // Добавление внутреннего источника шаблонов
    $provider = new Template\Provider(__DIR__ .'/Views');
    $views->addProvider('component', $provider);

    $views->addAccessorSmart("root", "'". $request->getBasePath() ."'");
    $views->addAccessorSmart("component", "component", Template\Engine::ACCESSOR_PROPERTY);

    $views->component = '/admin';

    // Сопоставление шаблона с маршрутом
    $path = substr(trim($request->getPath(), '/'), strlen($views->component));
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
