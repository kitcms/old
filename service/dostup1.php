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

$document = DiDom\Document::create('http://dostup1.ru/tags/новости+копейска', true); //https://dostup1.ru/tags/?cur_cc=1006&tag=новости+копейска&curPos=24
if ($rows = array_merge($document->find('.inner-top-news a.link'), $document->find('.inner-top-news a.middle-news'), $document->find('.inner-top-news a.small-news'))) {
    foreach ($rows as $row) {
        $news = array(
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
            'source' => 'Агентство новостей «Доступ»',
            'sourceLink' => 'http://dostup1.ru'. $row->first('a[href]::attr(href)')
        );

        if ((false == ORM::forTable('News', 'service')->where('sourceLink', $news['sourceLink'])->findOne()) &&
            (false == ORM::forTable('News')->where('sourceLink', $news['sourceLink'])->findOne())) {

            // Загружаем текст новости
            $source = DiDom\Document::create($news['sourceLink'], true);

            // Удаляем лишнее
            $source->first('.main-news-tags')->remove();

            $news['title'] = $source->first('.main-news-container h1')->text();
            $news['category'] = $source->first('.main-news-container a.link-group span')->text();
            $date = $source->first('.main-news-container .date span')->text();

            $pattern = ['/года/', '/,/', '/января/', '/февраля/', '/марта/', '/апреля/', '/мая/', '/июня/', '/июля/', '/августа/', '/сентября/', '/ноября/', '/декабря/'];
            $replacement = ['', '', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

            $date = preg_replace($pattern, $replacement, $date);
            if ($date = DateTime::createFromFormat('d m Y  H:i', $date)) {
                $news['date'] = $date->format('Y-m-d H:i:s');
            }

            if ($image = $source->first('.main-news-container .main-news-img-container img::attr(src)')) {
                if (false == parse_url($image, PHP_URL_HOST)) {
                    $image = 'http://dostup1.ru'. $image;
                }
                $news['images'] = $image;
            }

            $text = [];
            foreach ($source->find('.main-news-content > *') as $key => $node) {
                if (false == $key) {
                    $news['description'] = $node->text();
                } else {
                    $text[] = $node->html();
                }
            }
            $news['text'] = join($text);
            $news['text'] = str_replace('src="/', 'src="http://dostup1.ru/', $news['text']);
            $news['text'] = str_replace('href="/', 'href="http://dostup1.ru/', $news['text']);

            ORM::forTable('News', 'service')->create($news)->save();
        }
    }
}
