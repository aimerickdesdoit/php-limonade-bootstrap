<?php

if (!getenv('APPLICATION_ENV')) {
  throw new Exception('APPLICATION_ENV is undefined');
}
define('APPLICATION_ENV', getenv('APPLICATION_ENV'));

ini_set('error_reporting',            E_ALL | E_STRICT);
ini_set('display_startup_errors',     0);
ini_set('short_open_tag',             0);
ini_set('default_charset',            'UTF-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('iconv.input_encoding',       'UTF-8');
ini_set('iconv.internal_encoding',    'UTF-8');
ini_set('iconv.output_encoding',      'UTF-8');
ini_set('magic_quotes_sybase',        0);
ini_set('magic_quotes_runtime',       0);
ini_set('auto_detect_line_endings',   0);

define('ROOT_DIR', dirname(dirname(__FILE__)));
define('PUBLIC_DIR',  ROOT_DIR . '/public');

require_once ROOT_DIR . '/vendor/autoload.php';

ini_set('display_errors', APPLICATION_ENV == 'development' ? 1 : 0);

function configure() {  
  option('base_uri',      BASE_URI);
  option('views_dir',     ROOT_DIR . '/app/views');

  layout('layouts/front.phtml');
}

require_once dirname(__FILE__) . '/settings.inc.php';

$initializers = glob(ROOT_DIR . '/config/initializers/*.php');
foreach ($initializers as $initializer) {
  require_once $initializer;
}