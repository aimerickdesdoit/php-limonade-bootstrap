<?php

namespace CustomErrorPage;

class EventHandler extends \Airbrake\EventHandler {
  
  public static function start($apiKey, $notifyOnWarning=false, array $options=array()){
    if ( !isset(self::$instance)) {
      ob_start();
      
      $config = new \Airbrake\Configuration($apiKey, $options);

      $client = new \Airbrake\Client($config);
      self::$instance = new self($client, $notifyOnWarning);
      
      if (null !== $config->errorReportingLevel){
        self::$instance->addErrorFilter(new \Airbrake\EventFilter\Error\ErrorReporting($config));
      }

      self::$instance->addExceptionFilter(new \Airbrake\EventFilter\Exception\AirbrakeExceptionFilter());

      set_error_handler(array(self::$instance, 'onError'));
      set_exception_handler(array(self::$instance, 'onException'));
      register_shutdown_function(array(self::$instance, 'onShutdown'));
    }

    return self::$instance;
  }
  
  // sur le onError, s'il s'agit d'une erreur "fatalErrors", une exception est lancée
  
  public function onShutdown() {
    $error = error_get_last();
    if ($error && isset($this->fatalErrors[$error['type']])) {
      $this->_displayErrorPage();
    }
    return parent::onShutdown();
  }
  
  public function onException(Exception $exception) {
    if (APPLICATION_ENV == 'development') {
      echo '<pre>' . print_r($exception, true) . '</pre>';
    }
    $this->_displayErrorPage();
    return parent::onException($exception);
  }
  
  protected function _displayErrorPage() {
    if (APPLICATION_ENV != 'development') {
      while(ob_get_level()) {
        ob_end_clean();
      }
    }
    if (APPLICATION_ENV == 'development' && !headers_sent()) {
      // ne fonctionne pas chez Oxalide
      header($_SERVER['SERVER_PROTOCOL'] . ' Internal Server Error', true, 500);
    }
    echo file_get_contents(ROOT_DIR.'/public/500.html');
  }
  
  protected function _header_status($status_code) {
    if (self::$_status_codes[$status_code] !== null) {
      $status_string = $status_code . ' ' . self::$_status_codes[$status_code];
      header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $status_code);
    }
  }
  
}