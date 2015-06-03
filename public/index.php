<?php

require_once dirname(dirname(__FILE__)) . '/config/application.php';

require_once ROOT_DIR . '/app/controllers/front/pages_controller.php';

dispatch('/', 'pages_home');

run();