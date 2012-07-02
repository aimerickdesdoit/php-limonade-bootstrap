<?php

if (!getenv('APPLICATION_ENV')) {
  throw new Exception('APPLICATION_ENV is undefined');
}

ini_set('error_reporting',            E_ALL | E_STRICT);
ini_set('display_startup_errors',     0);
ini_set('display_errors',             0);
ini_set('short_open_tag',             0);
ini_set('default_charset',            'UTF-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('iconv.input_encoding',       'UTF-8');
ini_set('iconv.internal_encoding',    'UTF-8');
ini_set('iconv.output_encoding',      'UTF-8');
ini_set('magic_quotes_sybase',        0);
ini_set('magic_quotes_runtime',       0);
ini_set('auto_detect_line_endings',   0);

define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
define('PRIVATE_DIR',     dirname(dirname(__FILE__)));

require_once PRIVATE_DIR . '/lib/limonade.php';
require_once PRIVATE_DIR . '/lib/WideImage/WideImage.php';

function configure() {  
  option('base_uri',      '/php-limonade-bootstrap/public/');
  option('views_dir',     PRIVATE_DIR . '/views');
  
  try {
      $db = new PDO('sqlite:' . PRIVATE_DIR . '/db/' . APPLICATION_ENV. '.sqlite');
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  catch(PDOException $e) {
      throw new Exception('Connexion failed : ' . $e->getMessage());
  }
  option('db_conn', $db);
  
  if (APPLICATION_ENV == 'development') {
    ini_set('display_errors', 1);
  }
}

function isAjax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}