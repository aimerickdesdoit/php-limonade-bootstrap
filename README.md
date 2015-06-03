# Limonade bootstrap

## Installation

	git clone git://github.com/aimerickdesdoit/php-limonade-bootstrap.git
	cd php-limonade-bootstrap
	curl -sS https://getcomposer.org/installer | php
	php composer.phar install

## Configuration

config/settings.inc.php

    <?php
    
    define('BASE_URI', '/php-limonade-bootstrap/public/');
    
    define('DB_HOST',     '127.0.0.1:8889');
    define('DB_NAME',     'php-limonade-bootstrap');
    define('DB_USER',     'root');
    define('DB_PASSWORD', 'root');