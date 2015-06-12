<?php

class Model {

  protected $_attributes = array();
  protected $_errors = array();
  protected static $_validators = array();

  public function __construct($attributes = array()) {
    $this->_attributes = $attributes;
  }

  /* ATTRIBUTES */

  public function __get($var) {
    return isset($this->_attributes[$var]) ? $this->_attributes[$var] : null;
  }

  public function __set($var, $value) {
    $this->_attributes[$var] = $value;
  }

  public function __isset($var) {
    return isset($this->_attributes[$var]);
  }

  public function __unset($var) {
    if (array_key_exists($var, $this->_attributes)) {
      unset($this->_attributes[$var]);
    }
  }

  /* INSTANCE METHODS */

  public function new_record() {
    return is_null($this->id);
  }

  public function required($attribute) {
    if (isset(static::$_validators[$attribute])) {
      $validators = (array) static::$_validators[$attribute];
      if (isset($validators['presence'])) {
        $options = (array) $validators['presence'];
        if (isset($options['if'])) {
          return $this->$options['if']();
        }
      }
      else {
        return in_array('presence', $validators);
      }
    }
    return false;
  }

  /* CALLBACKS */

  protected function _triggerCallback($when, $name) {
    $method = '_'.strtolower($when).ucfirst(strtolower($name));
    if (method_exists($this, $method)) {
      $this->$method();
    }
  }

  /* VALIDATORS */

  public function valid($throw = false) {
    $this->_triggerCallback('before', 'validation');

    foreach (static::$_validators as $field => $validators) {
      $validators = (array) $validators;
      foreach ($validators as $validator => $options) {
        if (is_int($validator)) {
          $validator = $options;
          $options = array();
        }
        $execute = true;
        if (is_array($options) && isset($options['if'])) {
          $execute = $this->$options['if']();
        }
        if ($execute) {
          $method = '_valid' . ucfirst(strtolower($validator));
          $this->$method($field, $options);
        }
      }
    }

    if ($throw && $this->_errors) {
      $messages = array();
      foreach ($this->_errors as $field => $message) {
        $messages[] = $field.' ('.$message.')';
      }
      throw new Exception('Validation error: ' . implode(', ', $messages));
    }

    $valid = count($this->_errors) == 0;

    if ($valid) {
      $this->_triggerCallback('after', 'validation');
    }

    return $valid;
  }

  protected function _validPresence($attribute) {
    if (!$this->$attribute) {
      $this->_errors[$attribute] = 'doit être rempli(e)';
    }
  }

  protected function _validFormat($attribute, $pattern = array()) {
    if (!preg_match($pattern, $this->$attribute)) {
      $this->_errors[$attribute] = 'n\'est pas valide';
    }
  }

  public function getErrors() {
    return $this->_errors;
  }

  public function getError($attribute) {
    return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : null;
  }

  /* DB ACCESS */

  public function save($attributes = array(), $throw = false) {
    if (count(func_get_args()) == 1 && is_bool($attributes)) {
      $throw = $attributes;
      $attributes = array();
    }

    $this->_attributes = array_merge($this->_attributes, $attributes);

    if (!$this->valid($throw)) {
      return false;
    }

    $this->_triggerCallback('before', 'save');

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
      $execute = $stmt->execute($this->_attributes);
    }
    else {
      $this->_triggerCallback('before', 'create');

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

      if ($execute) {
        $this->_triggerCallback('after', 'create');
      }
    }

    if ($execute) {
      $this->_triggerCallback('after', 'save');
    }

    if ($throw && !$execute) {
      throw new Exception('Unknown save error');
    }

    return $execute;
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