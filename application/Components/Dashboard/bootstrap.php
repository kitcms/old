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

    $template = 'index.html';
    if ($provider->templateExists($template)) {
        $views->display(array('component:'. $template, 'component:index.html'));
    }
}
