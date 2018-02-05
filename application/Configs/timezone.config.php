<?php
/*
 * Настройка временной зоны
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

if (!ini_get('date.timezone')) {
    date_default_timezone_set(@date_default_timezone_get()); // GTM
}

date_default_timezone_set('Asia/Yekaterinburg');
