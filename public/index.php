<?php

require_once dirname(dirname(__FILE__)) . '/config/application.php';

function configure() {
  option('env',             APPLICATION_ENV == 'development' ? ENV_DEVELOPMENT : ENV_PRODUCTION);

  option('base_uri',        BASE_URI);

  option('lib_dir',         ROOT_DIR . '/lib');
  option('controllers_dir', ROOT_DIR . '/app/controllers');
  option('views_dir',       ROOT_DIR . '/app/views');

  layout('layouts/front.phtml');
}

function autoload_controller($callback) {
  require_once_dir(option('controllers_dir'), '*/*.php');
}

function initialize() {
  require_once ROOT_DIR . '/config/routes.php';

  require_once_dir(ROOT_DIR . '/app/helpers');
  require_once_dir(ROOT_DIR . '/app/models');
}

run();