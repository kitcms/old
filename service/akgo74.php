#!/usr/bin/php7.0
<?php
/*
 * ...
 *
 * @package   This file is part of the Kit.cms
 * @author    Anton Popov <a.popov@kit.team>
 * @copyright Kit.team <http://www.kit.team>
 * @link      Kit.cms <http://www.kitcms.ru>
 */

// Определение системных загрузчиков
$files = glob('{.,..}/*/bootstrap*.php', GLOB_BRACE);
foreach ($files as $file) {
    // Не допускается использование загрузчиков в одной директории с фронт-контроллером системы
    $file = realpath(__DIR__ .'/'. $file);
    if (__DIR__ !== pathinfo($file, PATHINFO_DIRNAME) && is_file($file)) {
        require_once $file;
    }
}

$document = DiDom\Document::create('http://akgo74.ru/novosti', true); //http://akgo74.ru/novosti?p=1
$keys = ['title', 'date', 'sourceLink', 'description'];
if ($rows = $document->find('#content li')) {
    foreach ($rows as $row) {
        $news = ['created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s')];
        foreach ($row->find('div') as $key => $el) {
            if (isset($keys[$key])) {
                $news[$keys[$key]] = trim($el->text());
            }
        }
        $news['source'] = 'Администрация КГО';
        $news['sourceLink'] = 'https://akgo74.ru'. $row->first('a[href]::attr(href)');
        $date = array_chunk(date_parse_from_format('j.n.Y / H:i', $news['date']), 3);
        $news['date'] = join('-', $date[0]) .' '. join(':', $date[1]);
        if ((false == ORM::forTable('News', 'service')->where('sourceLink', $news['sourceLink'])->findOne()) &&
            (false == ORM::forTable('News')->where('sourceLink', $news['sourceLink'])->findOne())) {
            // Загружаем текст новости
            $source = DiDom\Document::create($news['sourceLink'], true);

            // Удаляем лишнее
            $unnecessary = $source->first('script[src=//yandex.st/share/share.js]');
            foreach ($unnecessary->nextSiblings() as $node) {
                $node->remove();
            }
            $unnecessary->remove();

            if ($image = $source->first('.news-anons-pic img::attr(src)')) {
                if (false == parse_url($image, PHP_URL_HOST)) {
                    $image = 'http://akgo74.ru'. $image;
                }
                $news['images'] = $image;
            }
            $text= [];
            foreach ($source->first('.anons-content')->children() as $node) {
                $text[] = $node->html();
            }
            foreach ($source->first('#news-content')->children() as $node) {
                $text[] = $node->html();
            }
            $news['text'] = join($text);

            $news['text'] = str_replace('src="/', 'src="http://akgo74.ru/', $news['text']);

            ORM::forTable('News', 'service')->create($news)->save();
        }
    }
}
