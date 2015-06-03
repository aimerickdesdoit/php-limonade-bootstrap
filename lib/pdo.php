<?php

class Pdo_Mysql extends PDO {

  public function __construct($dsn, $username, $password, $driver_options = array()) {
    if (is_array($dsn)) {
      $port = null;
      if (count($dsn) == 2) {
        list($host, $dbname) = $dsn;
      }
      else if (count($dsn) == 3) {
        list($host, $port, $dbname) = $dsn;
      }
      $dsn = self::_getMysqlDsn($host, $dbname, $port);
    }

    $driver_options = array_merge(array(
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_CASE => PDO::CASE_NATURAL,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\''
    ), $driver_options);

    parent::__construct($dsn, $username, $password, $driver_options);
  }

  private static function _getMysqlDsn($host, $dbname, $port = null){
    $driver = 'mysql';

    if(!in_array($driver, PDO::getAvailableDrivers())){
      throw new Exception('The ' . $driver . ' driver is not currently installed');
    }

    if (strpos($host, ':')) {
      list($host, $port) = explode(':', $host);
    }

    $dsn = $driver.':';
    $dsn .= 'host='.$host.';';
    if (!is_null($port)) {
      $dsn .= 'port='.$port.';';
    }
    $dsn .= 'dbname='.$dbname;

    return $dsn;
  }

}
