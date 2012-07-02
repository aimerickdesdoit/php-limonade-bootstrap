<?php

require_once dirname(dirname(__FILE__)) . '/private/config/application.php';

dispatch('/', 'index');

function index() {
  return html('index/index.phtml', 'layouts/default.phtml');
}

run();