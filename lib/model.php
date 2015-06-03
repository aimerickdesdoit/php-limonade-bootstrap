<?php

class Model {

  protected $_attributes = array();
  protected $_errors = array();
  protected static $_validators = array();

  public function __construct($attributes = array()) {
    $this->_attributes = $attributes;
  }

  public function __get($var) {
    return isset($this->_attributes[$var]) ? $this->_attributes[$var] : null;
  }

  public function __set($var, $value) {
    $this->_attributes[$var] = $value;
  }

  public function getError($attribute) {
    return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : null;
  }

  public function new_record() {
    return is_null($this->id);
  }

  public function validate() {
    foreach (static::$_validators as $field => $validators) {
      $validators = (array) $validators;
      foreach ($validators as $validator) {
        $method = '_validate' . $validator;
        $this->$method($field);
      }
    }
    return count($this->_errors) == 0;
  }

  protected function _validatePresence($field) {
    if (!$this->$field) {
      $this->_errors[$field] = 'Ce champ est obligatoire';
    }
  }

  public function save($attributes = array()) {
    $this->_attributes = array_merge($this->_attributes, $attributes);

    if (!$this->validate()) {
      return false;
    }

    if ($this->id) {
      $this->updated_at = date('Y-m-d H:i:s');

      $sets = array();

      // array('label = :label', 'description = :description')
      foreach ($this->_attributes as $key => $value) {
        if ($key == 'id') {
          continue;
        }
        array_push($sets, "$key = :$key");
      }

      // "label = :label AND description = :description"
      $sql = 'UPDATE '.static::$_table_name.' SET '.implode($sets, ', ').' WHERE id = :id';

      $stmt = self::pdo()->prepare($sql);
      return $stmt->execute($this->_attributes);
    }
    else {
      $this->created_at = date('Y-m-d H:i:s');
      $this->updated_at = date('Y-m-d H:i:s');

      $sets = array();

      // array(':label', ':description')
      foreach ($this->_attributes as $key => $value) {
        array_push($sets, ":$key");
      }

      $keys = array_keys($this->_attributes);
      $sql = 'INSERT INTO '.static::$_table_name.'('.implode($keys, ', ').') VALUES('. implode($sets, ', ') .')';
      $stmt = self::pdo()->prepare($sql);
      $execute = $stmt->execute($this->_attributes);

      $this->id = self::pdo()->lastInsertId();

      return $execute;
    }
  }

  public function destroy() {
    $sql = 'DELETE FROM '.static::$_table_name.' WHERE id = :id';
    $stmt = self::pdo()->prepare($sql);
    return $stmt->execute(array('id' => $this->id));
  }

  public static function findAll() {
    $stmt = self::pdo()->prepare('SELECT * FROM '.static::$_table_name);
    $stmt->execute();
    // [
    //   ['id' => '', 'label' => ''],
    //   ['id' => '', 'label' => ''],
    //   ['id' => '', 'label' => '']
    // ]
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $records = array();
    foreach ($results as $result) {
      // $records[] = new Model($result);
      array_push($records, new static($result));
    }

    return $records;
  }

  public static function find($id) {
    $stmt = self::pdo()->prepare('SELECT * FROM '.static::$_table_name.' WHERE id = :id LIMIT 1;');
    $stmt->execute(array('id' => $id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
      halt(NOT_FOUND, 'Record not found');
    }

    $record = new static($result);
    return $record;
  }

  public static function findAllBy($attribute, $value) {
    $stmt = self::pdo()->prepare('SELECT * FROM '.static::$_table_name.' WHERE '.self::_quoteIdentifier($attribute).' = :value LIMIT 1;');
    $stmt->execute(array('value' => $value));
    // [
    //   ['id' => '', 'label' => ''],
    //   ['id' => '', 'label' => ''],
    //   ['id' => '', 'label' => '']
    // ]
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $records = array();
    foreach ($results as $result) {
      // $records[] = new Model($result);
      array_push($records, new static($result));
    }

    return $records;
  }

  public static function findBy($attribute, $value, $halt = false) {
    $stmt = self::pdo()->prepare('SELECT * FROM '.static::$_table_name.' WHERE '.self::_quoteIdentifier($attribute).' = :value LIMIT 1;');
    $stmt->execute(array('value' => $value));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($halt && !$result) {
      halt(NOT_FOUND, 'Record not found');
    }

    if (!$result) {
      return null;
    }

    $record = new static($result);
    return $record;
  }

  protected static function _quoteIdentifier($value){
    return '`'.$value.'`';
  }

  public static function pdo() {
    global $pdo;

    if (!isset($pdo)) {
      $pdo = new Pdo_Mysql(array(DB_HOST, DB_NAME), DB_USER, DB_PASSWORD);
    }

    return $pdo;
  }

}