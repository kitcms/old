<p align="center">
  <img src="https://raw.githubusercontent.com/kitcms/docs/master/img/logo-small.png" width="181" height="auto" alt="logo">
</p>

<h1 align="center">Kit.cms</h1>
<h3 align="center"></h3>

<div align="center">
  <!-- Code name -->
  <a href="https://github.com/kitcms/docs"><img src="https://img.shields.io/badge/code%20name-black%20whale-202020.svg?style=flat-square" alt="Code name"/></a>
  <!-- Version -->
  <a href="https://github.com/kitcms/cms/releases"><img src="https://img.shields.io/badge/version-0.2.2-green.svg?style=flat-square" alt="Version"/></a>
  <!-- License -->
  <a href="https://raw.githubusercontent.com/kitcms/cms/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License" /></a>
</div>

<br><br><br><br>

## Установка

> :bulb: Так как, на данный момент, отсутствует полноценный инсталятор системы, вам придется в ручную редактировать
конфигурационные файлы.

1. [Скачайте](https://github.com/kitcms/cms/archive/0.1.0.zip) и распакуйте архив.
2. Содержимое папки ``public_html`` разместите в корневой(публичной) директории вашего сайта.
3. Папку ``application`` рекомендуется разместить рядом. Если у вас нет необходимых для этого полномочий, вы можете переместить данную папку в корневую директорию.
4. Отредактируйте содержимое файла [mysql.config.php](https://github.com/kitcms/cms/blob/0.1.0/application/Configs/mysql.config.php).  Укажите [хост](https://github.com/kitcms/cms/blob/0.1.0/application/Configs/mysql.config.php#L15), [порт](https://github.com/kitcms/cms/blob/0.1.0/application/Configs/mysql.config.php#L15) MySQL сервера и [название](https://github.com/kitcms/cms/blob/0.1.0/application/Configs/mysql.config.php#L15) базы данных, так же [логин](https://github.com/kitcms/cms/blob/0.1.0/application/Configs/mysql.config.php#L16) и [пароль](https://github.com/kitcms/cms/blob/0.1.0/application/Configs/mysql.config.php#L17) пользователя.
5. Наберите в адресной строке браузера адрес своего сайта. Если данные для сединения с сервером БД указаны корректно, то при первом открытии сайта будут созданы все необходимые системные таблицы и откроется страница авторизации в панели управления.
6. Введите логин ``user`` и пароль ``user``. Данную комбинацию вы всегда можете изменить в разделе редактирования пользователей.
