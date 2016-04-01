<?php
define("LOCAL_TZ", "EDT");
mb_internal_encoding("UTF-8");
function _void() {
  return null;
}
function _new($fn) {
  if (!($fn instanceof Func)) {
    throw new Ex(Error::create(_typeof($fn) . " is not a function"));
  }
  $args = array_slice(func_get_args(), 1);
  return call_user_func_array(array($fn, 'construct'), $args);
}
function _instanceof($obj, $fn) {
  if (!($obj instanceof Object)) {
    return false;
  }
  if (!($fn instanceof Func)) {
    throw new Ex(Error::create('Expecting a function in instanceof check'));
  }
  $proto = $obj->proto;
  $prototype = get($fn, 'prototype');
  while ($proto !== Object::$null) {
    if ($proto === $prototype) {
      return true;
    }
    $proto = $proto->proto;
  }
  return false;
}
function _plus() {
  $total = 0;
  $strings = array();
  $isString = false;
  foreach (func_get_args() as $arg) {
    if (is_string($arg)) {
      $isString = true;
    }
    $strings[] = to_string($arg);
    if (!$isString) {
      $total += to_number($arg);
    }
  }
  return $isString ? join('', $strings) : $total;
}
function _concat() {
  $strings = array();
  foreach (func_get_args() as $arg) {
    $strings[] = to_string($arg);
  }
  return join('', $strings);
}
function _negate($val) {
  return (float)(0 - $val);
}
function _and($a, $b) {
  return $a ? $b : $a;
}
function _or($a, $b) {
  return $a ? $a : $b;
}
function _delete($obj, $key = null) {
  if (func_num_args() === 1) {
    return false;
  }
  if ($obj === null || $obj === Object::$null) {
    throw new Ex(Error::create("Cannot convert undefined or null to object"));
  }
  $obj = objectify($obj);
  $obj->remove($key);
  return true;
}
function _in($key, $obj) {
  if (!($obj instanceof Object)) {
    throw new Ex(Error::create("Cannot use 'in' operator to search for '" . $key . "' in " . to_string($obj)));
  }
  return $obj->hasProperty($key);
}
function _typeof($value) {
  if ($value === null) {
    return 'undefined';
  }
  if ($value === Object::$null) {
    return 'object';
  }
  $type = gettype($value);
  if ($type === 'integer' || $type === 'double') {
    return 'number';
  }
  if ($type === 'string' || $type === 'boolean') {
    return $type;
  }
  if ($value instanceof Func) {
    return 'function';
  }
  if ($value instanceof Object) {
    return 'object';
  }
  return 'unknown';
}
function _seq() {
  $args = func_get_args();
  return array_pop($args);
}
function is($x) {
  return $x !== false && $x !== 0.0 && $x !== '' && $x !== null && $x !== Object::$null && $x === $x ;
}
function not($x) {
  return $x === false || $x === 0.0 || $x === '' || $x === null || $x === Object::$null || $x !== $x ;
}
function eq($a, $b) {
  $typeA = ($a === null || $a === Object::$null ? 'null' : ($a instanceof Object ? 'object' : gettype($a)));
  $typeB = ($b === null || $b === Object::$null ? 'null' : ($b instanceof Object ? 'object' : gettype($b)));
  if ($typeA === 'null' && $typeB === 'null') {
    return true;
  }
  if ($typeA === 'integer') {
    $a = (float)$a;
    $typeA = 'double';
  }
  if ($typeB === 'integer') {
    $b = (float)$b;
    $typeB = 'double';
  }
  if ($typeA === $typeB) {
    return $a === $b;
  }
  if ($typeA === 'double' && $typeB === 'string') {
    return $a === to_number($b);
  }
  if ($typeB === 'double' && $typeA === 'string') {
    return $b === to_number($a);
  }
  if ($typeA === 'boolean') {
    return eq((float)$a, $b);
  }
  if ($typeB === 'boolean') {
    return eq((float)$b, $a);
  }
  if (($typeA === 'string' || $typeA === 'double') && $typeB === 'object') {
    return eq($a, to_primitive($b));
  }
  if (($typeB === 'string' || $typeB === 'double') && $typeA === 'object') {
    return eq($b, to_primitive($a));
  }
  return false;
}
function keys($obj, &$arr = array()) {
  if (!($obj instanceof Object)) {
    return $arr;
  }
  return $obj->getKeys($arr);
}
function is_primitive($value) {
  return ($value === null || $value === Object::$null || is_scalar($value));
}
function is_int_or_float($value) {
  return (is_int($value) || is_float($value));
}
function to_string($value) {
  if ($value === null) {
    return 'undefined';
  }
  if ($value === Object::$null) {
    return 'null';
  }
  $type = gettype($value);
  if ($type === 'string') {
    return $value;
  }
  if ($type === 'boolean') {
    return $value ? 'true' : 'false';
  }
  if ($type === 'integer' || $type === 'double') {
    if ($value !== $value) return 'NaN';
    if ($value === INF) return 'Infinity';
    if ($value === -INF) return '-Infinity';
    return $value . '';
  }
  if ($value instanceof Object) {
    $fn = $value->get('toString');
    if ($fn instanceof Func) {
      return $fn->call($value);
    } else {
      throw new Ex(Error::create('Cannot convert object to primitive value'));
    }
  }
  throw new Ex(Error::create('Cannot cast PHP value to string: ' . _stringify($value)));
}
function to_number($value) {
  if ($value === null) {
    return NAN;
  }
  if ($value === Object::$null) {
    return 0.0;
  }
  if (is_float($value)) {
    return $value;
  }
  if (is_int($value)) {
    return (float)$value;
  }
  if (is_bool($value)) {
    return ($value ? 1.0 : 0.0);
  }
  if ($value instanceof Object) {
    return to_number(to_primitive($value));
  }
  $value = preg_replace('/^[\s\x0B\xA0]+|[\s\x0B\xA0]+$/u', '', $value);
  if ($value === '') {
    return 0.0;
  }
  if ($value === 'Infinity' || $value === '+Infinity') {
    return INF;
  }
  if ($value === '-Infinity') {
    return -INF;
  }
  if (preg_match('/^([+-]?)(\d+\.\d*|\.\d+|\d+)$/i', $value)) {
    return (float)$value;
  }
  if (preg_match('/^([+-]?)(\d+\.\d*|\.\d+|\d+)e([+-]?[0-9]+)$/i', $value, $m)) {
    return pow($m[1] . $m[2], $m[3]);
  }
  if (preg_match('/^0x[a-z0-9]+$/i', $value)) {
    return (float)hexdec(substr($value, 2));
  }
  return NAN;
}
function to_primitive($obj) {
  $value = $obj->callMethod('valueOf');
  if ($value instanceof Object) {
    $value = to_string($value);
  }
  return $value;
}
function objectify($value) {
  $type = gettype($value);
  if ($type === 'string') {
    return new Str($value);
  } elseif ($type === 'integer' || $type === 'double') {
    return new Number($value);
  } elseif ($type === 'boolean') {
    return new Bln($value);
  }
  return $value;
}
function get($obj, $name) {
  if ($obj === null || $obj === Object::$null) {
    throw new Ex(Error::create("Cannot read property '" . $name . "' of " . to_string($obj)));
  }
  $obj = objectify($obj);
  return $obj->get($name);
}
function set($obj, $name, $value, $op = '=', $returnOld = false) {
  if ($obj === null || $obj === Object::$null) {
    throw new Ex(Error::create("Cannot set property '" . $name . "' of " . to_string($obj)));
  }
  $obj = objectify($obj);
  if ($op === '=') {
    return $obj->set($name, $value);
  }
  $oldValue = $obj->get($name);
  switch ($op) {
    case '+=':
      $newValue = _plus($oldValue, $value);
      break;
    case '-=':
      $newValue = $oldValue - $value;
      break;
    case '*=':
      $newValue = $oldValue * $value;
      break;
    case '/=':
      $newValue = $oldValue / $value;
      break;
    case '%=':
      $newValue = $oldValue % $value;
      break;
  }
  $obj->set($name, $newValue);
  return $returnOld ? $oldValue : $newValue;
}
function call($fn) {
  if (!($fn instanceof Func)) {
    throw new Ex(Error::create(_typeof($fn) . " is not a function"));
  }
  $args = array_slice(func_get_args(), 1);
  return $fn->apply(Object::$global, $args);
}
function call_method($obj, $name) {
  if ($obj === null || $obj === Object::$null) {
    throw new Ex(Error::create("Cannot read property '" . $name . "' of " . to_string($obj)));
  }
  $obj = objectify($obj);
  $fn = $obj->get($name);
  if (!($fn instanceof Func)) {
    throw new Ex(Error::create(_typeof($fn) . " is not a function"));
  }
  $args = array_slice(func_get_args(), 2);
  return $fn->apply($obj, $args);
}
function write_all($stream, $data, $bytesTotal = null) {
  if ($bytesTotal === null) {
    $bytesTotal = strlen($data);
  }
  $bytesWritten = fwrite($stream, $data);
  while ($bytesWritten < $bytesTotal) {
    $bytesWritten += fwrite($stream, substr($data, $bytesWritten));
  }
}
class Object {
  public $data = null;
  public $proto = null;
  public $className = "Object";
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  static $null = null;
  static $global = null;
  function __construct() {
    $this->data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    $this->proto = self::$protoObject;
    $args = func_get_args();
    if (count($args) > 0) {
      $this->init($args);
    }
  }
  function init($arr) {
    $len = count($arr);
    for ($i = 0; $i < $len; $i += 2) {
      $this->set($arr[$i], $arr[$i + 1]);
    }
  }
  function get($key) {
    $key = (string)$key;
    if (method_exists($this, 'get_' . $key)) {
      return $this->{'get_' . $key}();
    }
    $obj = $this;
    while ($obj !== Object::$null) {
      $data = $obj->data;
      if (array_key_exists($key, $data)) {
        return $data->{$key}->value;
      }
      $obj = $obj->proto;
    }
    return null;
  }
  function set($key, $value) {
    $key = (string)$key;
    if (method_exists($this, 'set_' . $key)) {
      return $this->{'set_' . $key}($value);
    }
    $data = $this->data;
    if (array_key_exists($key, $data)) {
      $property = $data->{$key};
      if ($property->writable) {
        $property->value = $value;
      }
    } else {
      $data->{$key} = new Property($value);
    }
    return $value;
  }
  function remove($key) {
    $key = (string)$key;
    $data = $this->data;
    if (array_key_exists($key, $data)) {
      if (!$data->{$key}->configurable) {
        return false;
      }
      unset($data->{$key});
    }
    return true;
  }
  function hasOwnProperty($key) {
    $key = (string)$key;
    return array_key_exists($key, $this->data);
  }
  function hasProperty($key) {
    $key = (string)$key;
    if (array_key_exists($key, $this->data)) {
      return true;
    }
    $proto = $this->proto;
    if ($proto instanceof Object) {
      return $proto->hasProperty($key);
    }
    return false;
  }
  function getOwnKeys($onlyEnumerable) {
    $arr = array();
    foreach ($this->data as $key => $prop) {
      if ($onlyEnumerable) {
        if ($prop->enumerable) {
          $arr[] = $key;
        }
      } else {
        $arr[] = $key;
      }
    }
    return $arr;
  }
  function getKeys(&$arr = array()) {
    foreach ($this->data as $key => $prop) {
      if ($prop->enumerable) {
        $arr[] = $key;
      }
    }
    $proto = $this->proto;
    if ($proto instanceof Object) {
      $proto->getKeys($arr);
    }
    return $arr;
  }
  function setProperty($key, $value, $writable = null, $enumerable = null, $configurable = null) {
    $key = (string)$key;
    $data = $this->data;
    if (array_key_exists($key, $data)) {
      $prop = $data->{$key};
      $prop->value = $value;
      if ($writable !== null) $prop->writable = $writable;
      if ($enumerable !== null) $prop->enumerable = $enumerable;
      if ($configurable !== null) $prop->configurable = $configurable;
    } else {
      $data->{$key} = new Property($value, $writable, $enumerable, $configurable);
    }
    return $value;
  }
  function setProps($props, $writable = null, $enumerable = null, $configurable = null) {
    foreach ($props as $key => $value) {
      $this->setProperty($key, $value, $writable = null, $enumerable = null, $configurable = null);
    }
  }
  function setMethods($methods, $writable = null, $enumerable = null, $configurable = null) {
    foreach ($methods as $name => $fn) {
      $func = new Func($name, $fn);
      $func->strict = true;
      $this->setProperty($name, $func, $writable, $enumerable, $configurable);
    }
  }
  function toArray() {
    $keys = $this->getOwnKeys(true);
    $results = array();
    foreach ($keys as $key) {
      $results[$key] = $this->get($key);
    }
    return $results;
  }
  function callMethod($name) {
    $fn = $this->get($name);
    $args = array_slice(func_get_args(), 1);
    return $fn->apply($this, $args);
  }
  function __call($name, $args) {
    if (isset($this->{$name})) {
      return call_user_func_array($this->{$name}, $args);
    } else {
      throw new Ex(Error::create('Internal method `' . $name . '` not found on ' . gettype($this)));
    }
  }
  static function getGlobalConstructor() {
    $Object = new Func(function($value = null) {
      if ($value === null || $value === Object::$null) {
        return new Object();
      } else {
        return objectify($value);
      }
    });
    $Object->set('prototype', Object::$protoObject);
    $Object->setMethods(Object::$classMethods, true, false, true);
    return $Object;
  }
}
class Property {
  public $value = null;
  public $writable = true;
  public $enumerable = true;
  public $configurable = true;
  function __construct($value, $writable = true, $enumerable = true, $configurable = true) {
    $this->value = $value;
    $this->writable = $writable;
    $this->enumerable = $enumerable;
    $this->configurable = $configurable;
  }
  function getDescriptor() {
    $result = new Object();
    $result->set('value', $this->value);
    $result->set('writable', $this->writable);
    $result->set('enumerable', $this->enumerable);
    $result->set('configurable', $this->configurable);
    return $result;
  }
}
Object::$classMethods = array(
  'create' => function($proto) {
      if (!($proto instanceof Object) && $proto !== Object::$null) {
        throw new Ex(Error::create('Object prototype may only be an Object or null'));
      }
      $obj = new Object();
      $obj->proto = $proto;
      return $obj;
    },
  'keys' => function($obj) {
      if (!($obj instanceof Object)) {
        throw new Ex(Error::create('Object.keys called on non-object'));
      }
      $results = new Arr();
      $results->init($obj->getOwnKeys(true));
      return $results;
    },
  'getOwnPropertyNames' => function($obj) {
      if (!($obj instanceof Object)) {
        throw new Ex(Error::create('Object.getOwnPropertyNames called on non-object'));
      }
      $results = new Arr();
      $results->init($obj->getOwnKeys(false));
      return $results;
    },
  'getOwnPropertyDescriptor' => function($obj, $key) {
      if (!($obj instanceof Object)) {
        throw new Ex(Error::create('Object.getOwnPropertyDescriptor called on non-object'));
      }
      $result = $obj->get($key);
      return ($result) ? $result->getDescriptor() : null;
    },
  'defineProperty' => function($obj, $key, $desc) {
      if (!($obj instanceof Object)) {
        throw new Ex(Error::create('Object.defineProperty called on non-object'));
      }
      $value = $desc->get('value');
      $writable = $desc->get('writable');
      if ($writable === null) $writable = true;
      $enumerable = $desc->get('enumerable');
      if ($enumerable === null) $enumerable = true;
      $configurable = $desc->get('configurable');
      if ($configurable === null) $configurable = true;
      $obj->data->{$key} = new Property($value, $writable, $enumerable, $configurable);
    },
  'defineProperties' => function($obj, $items) {
      if (!($obj instanceof Object)) {
        throw new Ex(Error::create('Object.defineProperties called on non-object'));
      }
      $methods = Object::$classMethods;
      foreach ($items->data as $key => $prop) {
        if ($prop->enumerable) {
          $methods['defineProperty']($obj, $key, $prop->value);
        }
      }
    }
);
Object::$protoMethods = array(
  'hasOwnProperty' => function($key) {
      $self = Func::getContext();
      $key = (string)$key;
      return array_key_exists($key, $self->data);
    },
  'toString' => function() {
      $self = Func::getContext();
      if ($self === null) {
        $className = 'Undefined';
      } else if ($self === Object::$null) {
        $className = 'Null';
      } else {
        $obj = objectify($self);
        $className = $obj->className;
      }
      return '[object ' . $className . ']';
    },
  'valueOf' => function() {
      return Func::getContext();
    }
);
class Null {}
Object::$null = new Null();
Object::$protoObject = new Object();
Object::$protoObject->proto = Object::$null;
class Func extends Object {
  public $name = "";
  public $className = "Function";
  public $fn = null;
  public $meta = null;
  public $strict = false;
  public $callStackPosition = null;
  public $args = null;
  public $boundArgs = null;
  public $context = null;
  public $boundContext = null;
  public $arguments = null;
  public $instantiate = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  static $callStack = array();
  static $callStackLength = 0;
  function __construct() {
    parent::__construct();
    $this->proto = self::$protoObject;
    $args = func_get_args();
    if (gettype($args[0]) === 'string') {
      $this->name = array_shift($args);
    }
    $this->fn = array_shift($args);
    $this->meta = isset($args[0]) ? $args[0] : array();
    $this->strict = isset($this->meta['strict']);
    $prototype = new Object();
    $prototype->setProperty('constructor', $this, true, false, true);
    $this->setProperty('prototype', $prototype, true, false, true);
  }
  function construct() {
    if ($this->instantiate !== null) {
      $obj = call_user_func($this->instantiate);
    } else {
      $obj = new Object();
      $obj->proto = $this->get('prototype');
    }
    $result = $this->apply($obj, func_get_args());
    return is_primitive($result) ? $obj : $result;
  }
  function call($context = null) {
    return $this->apply($context, array_slice(func_get_args(), 1));
  }
  function apply($context, $args) {
    if ($this->boundContext !== null) {
      $context = $this->boundContext;
      if ($this->boundArgs) {
        $args = array_merge($this->boundArgs, $args);
      }
    }
    $this->args = $args;
    if (!$this->strict) {
      if ($context === null || $context === Object::$null) {
        $context = Object::$global;
      } else if (!($context instanceof Object)) {
        $context = objectify($context);
      }
    }
    $oldStackPosition = $this->callStackPosition;
    $oldArguments = $this->arguments;
    $oldContext = $this->context;
    $this->context = $context;
    $this->callStackPosition = self::$callStackLength;
    self::$callStack[self::$callStackLength++] = $this;
    $result = call_user_func_array($this->fn, $args);
    self::$callStack[--self::$callStackLength] = null;
    $this->callStackPosition = $oldStackPosition;
    $this->arguments = $oldArguments;
    $this->context = $oldContext;
    return $result;
  }
  function get_name() {
    return $this->name;
  }
  function set_name($value) {
    return $value;
  }
  function get_arguments() {
    $arguments = $this->arguments;
    if ($arguments === null && $this->callStackPosition !== null) {
      $arguments = $this->arguments = Args::create($this);
    }
    return $arguments;
  }
  function set_arguments($value) {
    return $value;
  }
  function get_caller() {
    $stackPosition = $this->callStackPosition;
    if ($stackPosition !== null && $stackPosition > 0) {
      return self::$callStack[$stackPosition - 1];
    } else {
      return null;
    }
  }
  function set_caller($value) {
    return $value;
  }
  function get_length() {
    $reflection = new ReflectionObject($this->fn);
    $method = $reflection->getMethod('__invoke');
    $arity = $method->getNumberOfParameters();
    if ($this->boundArgs) {
      $boundArgsLength = count($this->boundArgs);
      $arity = ($boundArgsLength >= $arity) ? 0 : $arity - $boundArgsLength;
    }
    return (float)$arity;
  }
  function set_length($value) {
    return $value;
  }
  function toJSON() {
    return null;
  }
  static function getCurrent() {
    return self::$callStack[self::$callStackLength - 1];
  }
  static function getContext() {
    $func = self::$callStack[self::$callStackLength - 1];
    return $func->context;
  }
  static function getArguments() {
    $func = self::$callStack[self::$callStackLength - 1];
    return $func->get_arguments();
  }
  static function getGlobalConstructor() {
    $Function = new Func(function($fn) {
      throw new Ex(Error::create('Cannot construct function at runtime.'));
    });
    $Function->set('prototype', Func::$protoObject);
    $Function->setMethods(Func::$classMethods, true, false, true);
    return $Function;
  }
}
class Args extends Object {
  public $args = null;
  public $length = 0;
  public $callee = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function toArray() {
    return array_slice($this->args, 0);
  }
  function get_callee() {
    return $this->callee;
  }
  function set_callee($value) {
    return $value;
  }
  function get_caller() {
    return $this->callee->get_caller();
  }
  function set_caller($value) {
    return $value;
  }
  function get_length() {
    return (float)$this->length;
  }
  function set_length($value) {
    return $value;
  }
  static function create($callee) {
    $self = new Args();
    foreach ($callee->args as $i => $arg) {
      $self->set($i, $arg);
      $self->length += 1;
    }
    $self->args = $callee->args;
    $self->callee = $callee;
    return $self;
  }
}
Func::$classMethods = array();
Func::$protoMethods = array(
  'bind' => function($context) {
      $self = Func::getContext();
      $fn = new Func($self->name, $self->fn, $self->meta);
      $fn->boundContext = $context;
      $args = array_slice(func_get_args(), 1);
      if (!empty($args)) {
        $fn->boundArgs = $args;
      }
      return $fn;
    },
  'call' => function() {
      $self = Func::getContext();
      $args = func_get_args();
      return $self->apply($args[0], array_slice($args, 1));
    },
  'apply' => function($context, $args = null) {
      $self = Func::getContext();
      if ($args === null) {
        $args = array();
      } else
      if ($args instanceof Args || $args instanceof Arr) {
        $args = $args->toArray();
      } else {
        throw new Ex(Error::create('Function.prototype.apply: Arguments list has wrong type'));
      }
      return $self->apply($context, $args);
    },
  'toString' => function() {
      $self = Func::getContext();
      $source = array_key_exists('source_', $GLOBALS) ? $GLOBALS['source_'] : null;
      if ($source) {
        $meta = $self->meta;
        if (isset($meta['id']) && isset($source[$meta['id']])) {
          $source = $source[$meta['id']];
          return substr($source, $meta['start'], $meta['end'] - $meta['start'] + 1);
        }
      }
      return 'function ' . $self->name . '() { [native code] }';
    }
);
Func::$protoObject = new Object();
Func::$protoObject->setMethods(Func::$protoMethods, true, false, true);
Object::$protoObject->setMethods(Object::$protoMethods, true, false, true);
class GlobalObject extends Object {
  public $className = "global";
  static $immutable = array('Array' => 1, 'Boolean' => 1, 'Buffer' => 1, 'Date' => 1, 'Error' => 1, 'RangeError' => 1, 'ReferenceError' => 1, 'SyntaxError' => 1, 'TypeError' => 1, 'Function' => 1, 'Infinity' => 1, 'JSON' => 1, 'Math' => 1, 'NaN' => 1, 'Number' => 1, 'Object' => 1, 'RegExp' => 1, 'String' => 1, 'console' => 1, 'decodeURI' => 1, 'decodeURIComponent' => 1, 'encodeURI' => 1, 'encodeURIComponent' => 1, 'escape' => 1, 'eval' => 1, 'isFinite' => 1, 'isNaN' => 1, 'parseFloat' => 1, 'parseInt' => 1, 'undefined' => 1, 'unescape' => 1);
  static $OLD_GLOBALS = null;
  static $SUPER_GLOBALS = array('GLOBALS' => 1, '_SERVER' => 1, '_GET' => 1, '_POST' => 1, '_FILES' => 1, '_COOKIE' => 1, '_SESSION' => 1, '_REQUEST' => 1, '_ENV' => 1);
  static $protoObject = null;
  static $classMethods = null;
  function set($key, $value) {
    if (array_key_exists($key, self::$immutable)) {
      return $value;
    }
    $key = self::encodeVar($key);
    return ($GLOBALS[$key] = $value);
  }
  function get($key) {
    $key = self::encodeVar($key);
    $value = array_key_exists($key, $GLOBALS) ? $GLOBALS[$key] : null;
    return $value;
  }
  function remove($key) {
    if (array_key_exists($key, self::$immutable)) {
      return false;
    }
    $key = self::encodeVar($key);
    if (array_key_exists($key, $GLOBALS)) {
      unset($GLOBALS[$key]);
    }
    return true;
  }
  function hasOwnProperty($key) {
    $key = self::encodeVar($key);
    return array_key_exists($key, $GLOBALS);
  }
  function hasProperty($key) {
    $key = self::encodeVar($key);
    if (array_key_exists($key, $GLOBALS)) {
      return true;
    }
    $proto = $this->proto;
    if ($proto instanceof Object) {
      return $proto->hasProperty($key);
    }
    return false;
  }
  function getOwnKeys($onlyEnumerable) {
    $arr = array();
    foreach ($GLOBALS as $key => $value) {
      if (!array_key_exists($key, self::$SUPER_GLOBALS)) {
        $arr[] = self::decodeVar($key);
      }
    }
    return $arr;
  }
  function getKeys(&$arr = array()) {
    foreach ($GLOBALS as $key => $value) {
      if (!array_key_exists($key, self::$SUPER_GLOBALS)) {
        $arr[] = self::decodeVar($key);
      }
    }
    $proto = $this->proto;
    if ($proto instanceof Object) {
      $proto->getKeys($arr);
    }
    return $arr;
  }
  static function encodeVar($str) {
    if (array_key_exists($str, self::$SUPER_GLOBALS)) {
      return $str . '_';
    }
    $str = preg_replace('/_$/', '__', $str);
    $str = preg_replace_callback('/[^a-zA-Z0-9_]/', 'self::encodeChar', $str);
    return $str;
  }
  static function encodeChar($matches) {
    return '«' . bin2hex($matches[0]) . '»';
  }
  static function decodeVar($str) {
    $len = strlen($str);
    if ($str[$len - 1] === '_') {
      $name = substr($str, 0, $len - 1);
      if (array_key_exists($name, self::$SUPER_GLOBALS)) {
        return $name;
      }
    }
    $str = preg_replace('/__$/', '_', $str);
    $str = preg_replace_callback('/«([a-z0-9]+)»/', 'self::decodeChar', $str);
    return $str;
  }
  static function decodeChar($matches) {
    return hex2bin($matches[1]);
  }
  static function unsetGlobals() {
    self::$OLD_GLOBALS = array();
    foreach ($GLOBALS as $key => $value) {
      if (!array_key_exists($key, self::$SUPER_GLOBALS)) {
        self::$OLD_GLOBALS[$key] = $value;
        unset($GLOBALS[$key]);
      }
    }
  }
}
GlobalObject::unsetGlobals();
Object::$global = new GlobalObject();
class Arr extends Object {
  public $className = "Array";
  public $length = 0;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  static $empty = null;
  function __construct() {
    parent::__construct();
    $this->proto = self::$protoObject;
    $args = func_get_args();
    if (count($args) > 0) {
      $this->init($args);
    } else {
      $this->length = 0;
    }
  }
  function init($arr) {
    $len = 0;
    foreach ($arr as $i => $item) {
      if ($item !== Arr::$empty) {
        $this->set($i, $item);
      }
      $len += 1;
    }
    $this->length = $len;
  }
  function push($value) {
    $i = $this->length;
    foreach (func_get_args() as $value) {
      $this->set($i, $value);
      $i += 1;
    }
    return ($this->length = $i);
  }
  function shift() {
    $el = $this->get(0);
    $data = $this->data;
    $len = $this->length;
    for ($pos = 1; $pos < $len; $pos ++) {
      $newPos = $pos - 1;
      if (array_key_exists($pos, $data)) {
        $data->{$newPos} = $data->{$pos};
      } else if (array_key_exists($newPos, $data)) {
        unset($data->{$newPos});
      }
    }
    unset($data->{$len - 1});
    $this->length = $len - 1;
    return $el;
  }
  function unshift($value) {
    $len = $this->length;
    $num = func_num_args();
    $data = $this->data;
    $pos = $len;
    while ($pos--) {
      $newPos = $pos + $num;
      if (array_key_exists($pos, $data)) {
        $data->{$newPos} = $data->{$pos};
        unset($data->{$pos});
      } else if (array_key_exists($newPos, $data)) {
        unset($data->{$newPos});
      }
    }
    $this->length = $len + $num;
    foreach (func_get_args() as $i => $value) {
      $this->set($i, $value);
    }
    return $this->length;
  }
  static function checkInt($s) {
    if (is_int($s) && $s >= 0) return (float)$s;
    $s = to_string($s);
    $match = preg_match('/^\d+$/', $s);
    return ($match !== false) ? (float)$s : null;
  }
  function set($key, $value) {
    $i = self::checkInt($key);
    if ($i !== null && $i >= $this->length) {
      $this->length = $i + 1;
    }
    return parent::set($key, $value);
  }
  function get_length() {
    return (float)$this->length;
  }
  function set_length($len) {
    $len = self::checkInt($len);
    if ($len === null) {
      throw new Ex(Error::create('Invalid array length'));
    }
    $oldLen = $this->length;
    if ($oldLen > $len) {
      for ($i = $len; $i < $oldLen; $i++) {
        $this->remove($i);
      }
    }
    $this->length = $len;
    return (float)$len;
  }
  function toArray() {
    $results = array();
    $len = $this->length;
    for ($i = 0; $i < $len; $i++) {
      $results[] = $this->get($i);
    }
    return $results;
  }
  static function fromArray($nativeArray) {
    $arr = new Arr();
    $arr->init($nativeArray);
    return $arr;
  }
  static function getGlobalConstructor() {
    $Array = new Func(function($value = null) {
      $arr = new Arr();
      $len = func_num_args();
      if ($len === 1 && is_int_or_float($value)) {
        $arr->length = (int)$value;
      } else if ($len > 0) {
        $arr->init(func_get_args());
      }
      return $arr;
    });
    $Array->set('prototype', Arr::$protoObject);
    $Array->setMethods(Arr::$classMethods, true, false, true);
    return $Array;
  }
}
Arr::$classMethods = array(
  'isArray' => function($arr) {
      return ($arr instanceof Arr);
    }
);
Arr::$protoMethods = array(
  'push' => function($value) {
      $self = Func::getContext();
      $length = call_user_func_array(array($self, 'push'), func_get_args());
      return (float)$length;
    },
  'pop' => function() {
      $self = Func::getContext();
      $i = $self->length - 1;
      $result = $self->get($i);
      $self->remove($i);
      $self->length = $i;
      return $result;
    },
  'unshift' => function($value) {
      $self = Func::getContext();
      $length = call_user_func_array(array($self, 'unshift'), func_get_args());
      return (float)$length;
    },
  'shift' => function() {
      $self = Func::getContext();
      return $self->shift();
    },
  'join' => function($str = ',') {
      $results = array();
      $self = Func::getContext();
      $len = $self->length;
      for ($i = 0; $i < $len; $i++) {
        $value = $self->get($i);
        $results[] = ($value === null || $value === Object::$null) ? '' : to_string($value);
      }
      return join(to_string($str), $results);
    },
  'indexOf' => function($value) {
      $self = Func::getContext();
      $len = $self->length;
      for ($i = 0; $i < $len; $i++) {
        if ($self->get($i) === $value) return (float)$i;
      }
      return -1.0;
    },
  'lastIndexOf' => function($value) {
      $self = Func::getContext();
      $i = $self->length;
      while ($i--) {
        if ($self->get($i) === $value) return (float)$i;
      }
      return -1.0;
    },
  'slice' => function($start = 0, $end = null) {
      $self = Func::getContext();
      $len = $self->length;
      if ($len === 0) {
        return new Arr();
      }
      $start = (int)$start;
      if ($start < 0) {
        $start = $len + $start;
        if ($start < 0) $start = 0;
      }
      if ($start >= $len) {
        return new Arr();
      }
      $end = ($end === null) ? $len : (int)$end;
      if ($end < 0) {
        $end = $len + $end;
      }
      if ($end < $start) {
        $end = $start;
      }
      if ($end > $len) {
        $end = $len;
      }
      $result = new Arr();
      for ($i = $start; $i < $end; $i++) {
        $value = $self->get($i);
        $result->set($i, $value);
      }
      return $result;
    },
  'forEach' => function($fn, $context = null) {
      $self = Func::getContext();
      $len = $self->length;
      for ($i = 0; $i < $len; $i++) {
        if ($self->hasOwnProperty($i)) {
          $fn->call($context, $self->get($i), (float)$i, $self);
        }
      }
    },
  'sort' => function($fn = null) {
      $self = Func::getContext();
      if ($fn instanceof Func) {
        $results = $self->toArray();
        $comparator = function($a, $b) use (&$fn) {
          return $fn->call(null, $a, $b);
        };
        uasort($results, $comparator);
      } else {
        $results = array();
        $len = $self->length;
        for ($i = 0; $i < $len; $i++) {
          $results[$i] = to_string($self->get($i));
        }
        asort($results, SORT_STRING);
      }
      $i = 0;
      $temp = new StdClass();
      foreach ($results as $index => $str) {
        $temp->{$i} = $self->data->{$index};
        $i += 1;
      }
      foreach ($temp as $i => $prop) {
        $self->data->{$i} = $prop;
      }
      return $self;
    },
  'concat' => function() {
      $self = Func::getContext();
      $items = $self->toArray();
      foreach (func_get_args() as $item) {
        if ($item instanceof Arr) {
          foreach ($item->toArray() as $subitem) {
            $items[] = $subitem;
          }
        } else {
          $items[] = $item;
        }
      }
      $arr = new Arr();
      $arr->init($items);
      return $arr;
    },
  'toString' => function() {
      $self = Func::getContext();
      return $self->callMethod('join');
    },
  'toLocaleString' => function() {
      $self = Func::getContext();
      return $self->callMethod('join');
    }
);
Arr::$protoObject = new Object();
Arr::$protoObject->setMethods(Arr::$protoMethods, true, false, true);
Arr::$empty = new StdClass();
class Date extends Object {
  public $className = "Date";
  public $date = null;
  static $LOCAL_TZ = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function __construct() {
    parent::__construct();
    $this->proto = self::$protoObject;
    $args = func_get_args();
    if (count($args) > 0) {
      $this->init($args);
    }
  }
  function init($arr) {
    $len = count($arr);
    if ($len === 1) {
      $value = $arr[0];
      if (is_int_or_float($value)) {
        $this->_initFromMiliseconds($value);
      } else {
        $this->_initFromString($value);
      }
    } else {
      $this->_initFromParts($arr);
    }
  }
  function _initFromMiliseconds($ms) {
    $this->value = (float)$ms;
    $this->date = self::fromValue($ms);
  }
  function _initFromString($str) {
    $tz = (substr($str, -1) === 'Z') ? 'UTC' : null;
    $arr = self::parse($str);
    $this->_initFromParts($arr, $tz);
  }
  function _initFromParts($arr, $tz = null) {
    $arr = array_pad($arr, 7, 0);
    $date = self::create($tz);
    $date->setDate($arr[0], $arr[1] + 1, $arr[2]);
    $date->setTime($arr[3], $arr[4], $arr[5]);
    $this->date = $date;
    $this->value = (float)($date->getTimestamp() * 1000 + $arr[6]);
  }
  function toJSON() {
    $date = self::fromValue($this->value, 'UTC');
    $str = $date->format('Y-m-d\TH:i:s');
    $ms = $this->value % 1000;
    if ($ms < 0) $ms = 1000 + $ms;
    if ($ms < 0) $ms = 0;
    return $str . '.' . substr('00' . $ms, -3) . 'Z';
  }
  static function create($tz = null) {
    if ($tz === null) {
      return new DateTime('now', new DateTimeZone(self::$LOCAL_TZ));
    } else {
      return new DateTime('now', new DateTimeZone($tz));
    }
  }
  static function now() {
    return floor(microtime(true) * 1000);
  }
  static function fromValue($ms, $tz = null) {
    $timestamp = floor($ms / 1000);
    $date = self::create($tz);
    $date->setTimestamp($timestamp);
    return $date;
  }
  static function parse($str) {
    $str = to_string($str);
    $d = date_parse($str);
    return array($d['year'], $d['month'] - 1, $d['day'], $d['hour'], $d['minute'], $d['second'], floor($d['fraction'] * 1000));
  }
  static function getGlobalConstructor() {
    $Date = new Func(function() {
      $date = new Date();
      $date->init(func_get_args());
      return $date;
    });
    $Date->set('prototype', Date::$protoObject);
    $Date->setMethods(Date::$classMethods, true, false, true);
    return $Date;
  }
}
Date::$classMethods = array(
  'now' => function() {
      return Date::now();
    },
  'parse' => function($str) {
      $date = new Date($str);
      return $date->value;
    },
  'UTC' => function() {
      $date = new Date();
      $date->_initFromParts(func_get_args(), 'UTC');
      return $date->value;
    }
);
Date::$protoMethods = array(
  'valueOf' => function() {
      $self = Func::getContext();
      return $self->value;
    },
  'toJSON' => function() {
      $self = Func::getContext();
      return $self->toJSON();
    },
  'toUTCString' => function() {
    },
  'toString' => function() {
      $self = Func::getContext();
      return str_replace('~', 'GMT', $self->date->format('D M d Y H:i:s ~O (T)'));
    }
);
Date::$protoObject = new Object();
Date::$protoObject->setMethods(Date::$protoMethods, true, false, true);
Date::$LOCAL_TZ = defined('LOCAL_TZ') ? constant('LOCAL_TZ') : getenv('LOCAL_TZ');
if (Date::$LOCAL_TZ === false) {
  Date::$LOCAL_TZ = 'UTC';
}
class RegExp extends Object {
  public $className = "RegExp";
  public $source = '';
  public $ignoreCaseFlag = false;
  public $globalFlag = false;
  public $multilineFlag = false;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function __construct() {
    parent::__construct();
    $this->proto = self::$protoObject;
    $args = func_get_args();
    if (count($args) > 0) {
      $this->init($args);
    }
  }
  function init($args) {
    $this->source = ($args[0] === null) ? '(?:)' : to_string($args[0]);
    $flags = array_key_exists('1', $args) ? to_string($args[1]) : '';
    $this->ignoreCaseFlag = (strpos($flags, 'i') !== false);
    $this->globalFlag = (strpos($flags, 'g') !== false);
    $this->multilineFlag = (strpos($flags, 'm') !== false);
  }
  function get_source() {
    return $this->source;
  }
  function set_source($value) {
    return $value;
  }
  function get_ignoreCase() {
    return $this->ignoreCaseFlag;
  }
  function set_ignoreCase($value) {
    return $value;
  }
  function get_global() {
    return $this->globalFlag;
  }
  function set_global($value) {
    return $value;
  }
  function get_multiline() {
    return $this->multilineFlag;
  }
  function set_multiline($value) {
    return $value;
  }
  function toString($pcre = true) {
    $source = $this->source;
    $flags = '';
    if ($this->ignoreCaseFlag) {
      $flags .= 'i';
    }
    if (!$pcre && $this->globalFlag) {
      $flags .= 'g';
    }
    if ($pcre) {
      $flags .= 'u';
    }
    if ($this->multilineFlag) {
      $flags .= 'm';
    }
    return '/' . str_replace('/', '\\/', $source) . '/' . $flags;
  }
  static function toReplacementString($str) {
    $str = str_replace('\\', '\\\\', $str);
    $str = str_replace('$&', '$0', $str);
    return $str;
  }
  static function getGlobalConstructor() {
    $RegExp = new Func(function() {
      $reg = new RegExp();
      $reg->init(func_get_args());
      return $reg;
    });
    $RegExp->set('prototype', RegExp::$protoObject);
    $RegExp->setMethods(RegExp::$classMethods, true, false, true);
    return $RegExp;
  }
}
RegExp::$classMethods = array();
RegExp::$protoMethods = array(
  'exec' => function($str) {
      $self = Func::getContext();
      $str = to_string($str);
      $result = preg_match($self->toString(true), $str, $matches);
      if ($result === false) {
        return Object::$null;
      }
      $self->set('lastIndex', (float)($result + strlen($matches[0])));
      $arr = new Arr();
      $arr->init($matches);
      $arr->set('index', (float)$result);
      $arr->set('input', $str);
      return $arr;
    },
  'test' => function($str) {
      $self = Func::getContext();
      $result = preg_match($self->toString(true), to_string($str));
      return ($result !== false);
    },
  'toString' => function() {
      $self = Func::getContext();
      return $self->toString(false);
    }
);
RegExp::$protoObject = new Object();
RegExp::$protoObject->setMethods(RegExp::$protoMethods, true, false, true);
class Str extends Object {
  public $className = "String";
  public $value = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function __construct($str = null) {
    parent::__construct();
    $this->proto = self::$protoObject;
    if (func_num_args() === 1) {
      $this->value = $str;
      $this->length = mb_strlen($str);
    }
  }
  function get_length() {
    return (float)$this->length;
  }
  function set_length($len) {
    return $len;
  }
  function get($key) {
    if (is_float($key)) {
      if ((float)(int)$key === $key && $key >= 0) {
        return $this->callMethod('charAt', $key);
      }
    }
    return parent::get($key);
  }
  static function getGlobalConstructor() {
    $String = new Func(function($value = '') {
      $self = Func::getContext();
      if ($self instanceof Str) {
        $self->value = to_string($value);
        return $self;
      } else {
        return to_string($value);
      }
    });
    $String->instantiate = function() {
      return new Str();
    };
    $String->set('prototype', Str::$protoObject);
    $String->setMethods(Str::$classMethods, true, false, true);
    return $String;
  }
}
Str::$classMethods = array(
  'fromCharCode' => function($code) {
      return chr($code);
    }
);
Str::$protoMethods = array(
  'charAt' => function($i) {
      $self = Func::getContext();
      $ch = mb_substr($self->value, $i, 1);
      return ($ch === false) ? '' : $ch;
    },
  'charCodeAt' => function($i) {
      $self = Func::getContext();
      $ch = mb_substr($self->value, $i, 1);
      if ($ch === false) return NAN;
      $len = strlen($ch);
      if ($len === 1) {
        $code = ord($ch[0]);
      } else {
        $ch = mb_convert_encoding($ch, 'UCS-2LE', 'UTF-8');
        $code = ord($ch[1]) * 256 + ord($ch[0]);
      }
      return (float)$code;
    },
  'indexOf' => function($search, $offset = 0) {
      $self = Func::getContext();
      $index = mb_strpos($self->value, $search, $offset);
      return ($index === false) ? -1.0 : (float)$index;
    },
  'lastIndexOf' => function($search, $offset = null) {
      $self = Func::getContext();
      $str = $self->value;
      if ($offset !== null) {
        $offset = to_number($offset);
        if ($offset > 0 && $offset < $self->length) {
          $str = mb_substr($str, 0, $offset + 1);
        }
      }
      $index = mb_strrpos($str, $search);
      return ($index === false) ? -1.0 : (float)$index;
    },
  'split' => function($delim) {
      $self = Func::getContext();
      $str = $self->value;
      if ($delim instanceof RegExp) {
        $arr = preg_split($delim->toString(true), $str);
      } else {
        $delim = to_string($delim);
        if ($delim === '') {
          $len = mb_strlen($str);
          $arr = array();
          for ($i = 0; $i < $len; $i++) {
            $arr[] = mb_substr($str, $i, 1);
          }
        } else {
          $arr = explode($delim, $str);
        }
      }
      $result = new Arr();
      $result->init($arr);
      return $result;
    },
  'substr' => function($start, $num = null) {
      $self = Func::getContext();
      $len = $self->length;
      if ($len === 0) {
        return '';
      }
      $start = (int)$start;
      if ($start < 0) {
        $start = $len + $start;
        if ($start < 0) $start = 0;
      }
      if ($start >= $len) {
        return '';
      }
      if ($num === null) {
        return mb_substr($self->value, $start);
      } else {
        return mb_substr($self->value, $start, $num);
      }
    },
  'substring' => function($start, $end = null) {
      $self = Func::getContext();
      $len = $self->length;
      if (func_num_args() === 1) {
        $end = $len;
      }
      $start = (int)$start;
      $end = (int)$end;
      if ($start < 0) $start = 0;
      if ($start > $len) $start = $len;
      if ($end < 0) $end = 0;
      if ($end > $len) $end = $len;
      if ($start === $end) {
        return '';
      }
      if ($end < $start) {
        list($start, $end) = array($end, $start);
      }
      return mb_substr($self->value, $start, $end - $start);
    },
  'slice' => function($start, $end = null) {
      $self = Func::getContext();
      $len = $self->length;
      if ($len === 0) {
        return '';
      }
      $start = (int)$start;
      if ($start < 0) {
        $start = $len + $start;
        if ($start < 0) $start = 0;
      }
      if ($start >= $len) {
        return '';
      }
      $end = ($end === null) ? $len : (int)$end;
      if ($end < 0) {
        $end = $len + $end;
      }
      if ($end < $start) {
        $end = $start;
      }
      if ($end > $len) {
        $end = $len;
      }
      return mb_substr($self->value, $start, $end - $start);
    },
  'trim' => function() {
      $self = Func::getContext();
      return preg_replace('/^[\s\x0B\xA0]+|[\s\x0B\​xA0]+$/u', '', $self->value);
    },
  'replace' => function($search, $replace) {
      $self = Func::getContext();
      $str = $self->value;
      $isRegEx = ($search instanceof RegExp);
      $limit = ($isRegEx && $search->globalFlag) ? -1 : 1;
      $search = $isRegEx ? $search->toString(true) : to_string($search);
      if ($replace instanceof Func) {
        if ($isRegEx) {
          $count = 0;
          $offset = 0;
          $result = array();
          $success = null;
          while (
              ($limit === -1 || $count < $limit) &&
              ($success = preg_match($search, $str, $matches, PREG_OFFSET_CAPTURE, $offset))
            ) {
            $matchIndex = $matches[0][1];
            $args = array();
            foreach ($matches as $match) {
              $args[] = $match[0];
            }
            $result[] = substr($str, $offset, $matchIndex - $offset);
            $mbIndex = mb_strlen(substr($str, 0, $matchIndex));
            array_push($args, $mbIndex);
            array_push($args, $str);
            $result[] = to_string($replace->apply(null, $args));
            $offset = $matchIndex + strlen($args[0]);
            $count += 1;
          }
          if ($success === false) {
            throw new Ex(Error::create('String.prototype.replace() failed'));
          }
          $result[] = substr($str, $offset);
          return join('', $result);
        } else {
          $matchIndex = strpos($str, $search);
          if ($matchIndex === false) {
            return $str;
          }
          $before = substr($str, 0, $matchIndex);
          $after = substr($str, $matchIndex + strlen($search));
          $args = array($search, mb_strlen($before), $str);
          return $before . to_string($replace->apply(null, $args)) . $after;
        }
      }
      $replace = to_string($replace);
      if ($isRegEx) {
        $replace = RegExp::toReplacementString($replace);
        return preg_replace($search, $replace, $str, $limit);
      } else {
        $parts = explode($search, $str);
        $first = array_shift($parts);
        return $first . $replace . implode($search, $parts);
      }
    },
  'toLowerCase' => function() {
      $self = Func::getContext();
      return mb_strtolower($self->value);
    },
  'toLocaleLowerCase' => function() {
      $self = Func::getContext();
      return mb_strtolower($self->value);
    },
  'toUpperCase' => function() {
      $self = Func::getContext();
      return mb_strtoupper($self->value);
    },
  'toLocaleUpperCase' => function() {
      $self = Func::getContext();
      return mb_strtoupper($self->value);
    },
  'localeCompare' => function($compareTo) {
      $self = Func::getContext();
      return (float)strcmp($self->value, to_string($compareTo));
    },
  'valueOf' => function() {
      $self = Func::getContext();
      return $self->value;
    },
  'toString' => function() {
      $self = Func::getContext();
      return $self->value;
    }
);
Str::$protoObject = new Object();
Str::$protoObject->setMethods(Str::$protoMethods, true, false, true);
class Number extends Object {
  public $className = "Number";
  public $value = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function __construct($value = null) {
    parent::__construct();
    $this->proto = self::$protoObject;
    if (func_num_args() === 1) {
      $this->value = (float)$value;
    }
  }
  static function getGlobalConstructor() {
    $Number = new Func(function($value = 0) {
      $self = Func::getContext();
      if ($self instanceof Number) {
        $self->value = to_number($value);
        return $self;
      } else {
        return to_number($value);
      }
    });
    $Number->instantiate = function() {
      return new Number();
    };
    $Number->set('prototype', Number::$protoObject);
    $Number->setMethods(Number::$classMethods, true, false, true);
    $Number->set('NaN', NAN);
    $Number->set('MAX_VALUE', 1.8e308);
    $Number->set('MIN_VALUE', -1.8e308);
    $Number->set('NEGATIVE_INFINITY', -INF);
    $Number->set('POSITIVE_INFINITY', INF);
    return $Number;
  }
}
Number::$classMethods = array(
  'isFinite' => function($value) {
      $value = to_number($value);
      return !($value === INF || $value === -INF || is_nan($value));
    },
  'parseInt' => function($value, $radix = null) {
      $value = to_string($value);
      $value = preg_replace('/^[\\t\\x0B\\f \\xA0\\r\\n]+/', '', $value);
      $sign = ($value[0] === '-') ? -1 : 1;
      $value = preg_replace('/^[+-]/', '', $value);
      if ($radix === null && strtolower(substr($value, 0, 2)) === '0x') {
        $radix = 16;
      }
      if ($radix === null) {
        $radix = 10;
      } else {
        $radix = to_number($radix);
        if (is_nan($radix) || $radix < 2 || $radix > 36) {
          return NAN;
        }
      }
      if ($radix === 10) {
        return preg_match('/^[0-9]/', $value) ? (float)(intval($value) * $sign) : NAN;
      } elseif ($radix === 16) {
        $value = preg_replace('/^0x/i', '', $value);
        return preg_match('/^[0-9a-f]/i', $value) ? (float)(hexdec($value) * $sign) : NAN;
      } elseif ($radix === 8) {
        return preg_match('/^[0-7]/', $value) ? (float)(octdec($value) * $sign) : NAN;
      }
      $value = strtoupper($value);
      $len = strlen($value);
      $numValidChars = 0;
      for ($i = 0; $i < $len; $i++) {
        $n = ord($value[$i]);
        if ($n >= 48 && $n <= 57) {
          $n = $n - 48;
        } elseif ($n >= 65 && $n <= 90) {
          $n = $n - 55;
        } else {
          $n = 36;
        }
        if ($n < $radix) {
          $numValidChars += 1;
        } else {
          break;
        }
      }
      if ($numValidChars > 0) {
        $value = substr($value, 0, $numValidChars);
        return floatval(base_convert($value, $radix, 10));
      }
      return NAN;
    },
  'parseFloat' => function($value) {
      $value = to_string($value);
      $value = preg_replace('/^[\\t\\x0B\\f \\xA0\\r\\n]+/', '', $value);
      $sign = ($value[0] === '-') ? -1 : 1;
      $value = preg_replace('/^[+-]/', '', $value);
      if (preg_match('/^(\d+\.\d*|\.\d+|\d+)e([+-]?[0-9]+)/i', $value, $m)) {
        return (float)($sign * $m[1] * pow(10, $m[2]));
      }
      if (preg_match('/^(\d+\.\d*|\.\d+|\d+)/i', $value, $m)) {
        return (float)($m[0] * $sign);
      }
      return NAN;
    },
  'isNaN' => function($value) {
      return is_nan(to_number($value));
    }
);
Number::$protoMethods = array(
  'valueOf' => function() {
      $self = Func::getContext();
      return $self->value;
    },
  'toString' => function($radix = null) {
      $self = Func::getContext();
      return to_string($self->value);
    }
);
Number::$protoObject = new Object();
Number::$protoObject->setMethods(Number::$protoMethods, true, false, true);
class Bln extends Object {
  public $className = "Boolean";
  public $value = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function __construct($value = null) {
    parent::__construct();
    $this->proto = self::$protoObject;
    if (func_num_args() === 1) {
      $this->value = $value;
    }
  }
  static function getGlobalConstructor() {
    $Boolean = new Func(function($value = false) {
      $self = Func::getContext();
      if ($self instanceof Bln) {
        $self->value = $value ? true : false;
        return $self;
      } else {
        return $value ? true : false;
      }
    });
    $Boolean->instantiate = function() {
      return new Bln();
    };
    $Boolean->set('prototype', Bln::$protoObject);
    $Boolean->setMethods(Bln::$classMethods, true, false, true);
    return $Boolean;
  }
}
Bln::$classMethods = array();
Bln::$protoMethods = array(
  'valueOf' => function() {
      $self = Func::getContext();
      return $self->value;
    },
  'toString' => function() {
      $self = Func::getContext();
      return to_string($self->value);
    }
);
Bln::$protoObject = new Object();
Bln::$protoObject->setMethods(Bln::$protoMethods, true, false, true);
class Error extends Object {
  public $className = "Error";
  public $stack = null;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  function __construct($str = null) {
    parent::__construct();
    $this->proto = self::$protoObject;
    if (func_num_args() === 1) {
      $this->set('message', to_string($str));
    }
  }
  public function getMessage() {
    $message = $this->get('message');
    return $this->className . ($message ? ': ' . $message : '');
  }
  static function create($str, $framesToPop = 0) {
    $error = new self($str);
    $stack = debug_backtrace();
    while ($framesToPop--) {
      array_shift($stack);
    }
    $error->stack = $stack;
    return $error;
  }
  static function getGlobalConstructor() {
    $Error = new Func(function($str = null) {
      $error = new self($str);
      $error->stack = debug_backtrace();
      return $error;
    });
    $Error->set('prototype', self::$protoObject);
    $Error->setMethods(self::$classMethods, true, false, true);
    return $Error;
  }
}
class RangeError extends Error {
  public $className = "RangeError";
}
class ReferenceError extends Error {
  public $className = "ReferenceError";
}
class SyntaxError extends Error {
  public $className = "SyntaxError";
}
class TypeError extends Error {
  public $className = "TypeError";
}
Error::$classMethods = array();
Error::$protoMethods = array(
  'toString' => function() {
      $self = Func::getContext();
      return $self->get('message');
    }
);
Error::$protoObject = new Object();
Error::$protoObject->setMethods(Error::$protoMethods, true, false, true);
class Ex extends Exception {
  public $value = null;
  const MAX_STR_LEN = 32;
  function __construct($value) {
    if ($value instanceof Error) {
      $message = $value->getMessage();
    } else {
      $message = to_string($value);
    }
    parent::__construct($message);
    $this->value = $value;
  }
  static function handleError($level, $message, $file, $line, $context) {
    if ($level === E_NOTICE) {
      return false;
    }
    $err = Error::create($message, 1);
    $err->set('level', $level);
    throw new Ex($err);
  }
  static function handleException($ex) {
    $stack = null;
    if ($ex instanceof Ex) {
      $value = $ex->value;
      if ($value instanceof Error) {
        $stack = $value->stack;
        $frame = array_shift($stack);
        if (isset($frame['file'])) {
          echo $frame['file'] . "(" . $frame['line'] . ")\n";
        }
        echo $value->getMessage() . "\n";
      }
    }
    if ($stack === null) {
      echo $ex->getFile() . "(" . $ex->getLine() . ")\n";
      echo $ex->getMessage() . "\n";
      $stack = $ex->getTrace();
    }
    echo self::renderStack($stack) . "\n";
    echo "----\n";
    exit(1);
  }
  static function renderStack($stack) {
    $lines = array();
    foreach ($stack as $frame) {
      $args = isset($frame['args']) ? $frame['args'] : array();
      $list = array();
      foreach ($args as $arg) {
        if (is_string($arg)) {
          $list[] = self::stringify($arg);
        } else if (is_array($arg)) {
          $list[] = 'array()';
        } else if (is_null($arg)) {
          $list[] = 'null';
        } else if (is_bool($arg)) {
          $list[] = ($arg) ? 'true' : 'false';
        } else if (is_object($arg)) {
          $class = get_class($arg);
          if ($arg instanceof Object) {
            $constructor = $arg->get('constructor');
            if ($constructor instanceof Func && $constructor->name) {
              $class .= '[' . $constructor->name . ']';
            }
          }
          $list[] = $class;
        } else if (is_resource($arg)) {
          $list[] = get_resource_type($arg);
        } else {
          $list[] = $arg;
        }
      }
      $function = $frame['function'];
      if ($function === '{closure}') {
        $function = '<anonymous>';
      }
      if (isset($frame['class'])) {
        $function = $frame['class'] . '->' . $function;
      }
      $line = '    at ';
      if (isset($frame['file'])) {
        $line .= $frame['file'] . '(' . $frame['line'] . ') ';
      }
      $line .= $function . '(' . join(', ', $list) . ') ';
      array_push($lines, $line);
    }
    return join("\n", $lines);
  }
  static function stringify($str) {
    $len = strlen($str);
    if ($len === 0) {
      return "''";
    }
    $str = preg_replace('/^[^\x20-\x7E]+/', '', $str, 1);
    $trimAt = null;
    $hasExtendedChars = preg_match('/[^\x20-\x7E]/', $str, $matches, PREG_OFFSET_CAPTURE);
    if ($hasExtendedChars === 1) {
      $trimAt = $matches[0][1];
    }
    if ($len > self::MAX_STR_LEN) {
      $trimAt = ($trimAt === null) ? self::MAX_STR_LEN : min($trimAt, self::MAX_STR_LEN);
    }
    if ($trimAt !== null) {
      return "'" . substr($str, 0, $trimAt) . "...'($len)";
    } else {
      return "'" . $str . "'";
    }
  }
}
if (function_exists('set_error_handler')) {
  set_error_handler(array('Ex', 'handleError'));
}
if (function_exists('set_exception_handler')) {
  set_exception_handler(array('Ex', 'handleException'));
}
class Buffer extends Object {
  public $raw = '';
  public $length = 0;
  static $protoObject = null;
  static $classMethods = null;
  static $protoMethods = null;
  static $SHOW_MAX = 51;
  function __construct() {
    parent::__construct();
    $this->proto = self::$protoObject;
    if (func_num_args() > 0) {
      $this->init(func_get_args());
    }
  }
  function init($args) {
    global $Buffer;
    list($subject, $encoding, $offset) = array_pad($args, 3, null);
    $type = gettype($subject);
    if ($type === 'integer' || $type === 'double') {
      $this->raw = str_repeat("\0", (int)$subject);
    } else if ($type === 'string') {
      $encoding = ($encoding === null) ? 'utf8' : to_string($encoding);
      if ($encoding === 'hex') {
        $this->raw = hex2bin($subject);
      } else if ($encoding === 'base64') {
        $this->raw = base64_decode($subject);
      } else {
        $this->raw = $subject;
      }
    } else if (_instanceof($subject, $Buffer)) {
      $this->raw = $subject->raw;
    } else if ($subject instanceof Arr) {
      $this->raw = $util['arrToRaw']($subject);
    } else {
      throw new Ex(Error::create('Invalid parameters to construct Buffer'));
    }
    $len = strlen($this->raw);
    $this->length = $len;
    $this->set('length', (float)$len);
  }
  function toJSON($max = null) {
    $raw = $this->raw;
    if ($max !== null && $max < strlen($raw)) {
      return '<Buffer ' . bin2hex(substr($raw, 0, $max)) . '...>';
    } else {
      return '<Buffer ' . bin2hex($raw) . '>';
    }
  }
  static function getGlobalConstructor() {
    $Buffer = new Func('Buffer', function() {
      $self = new Buffer();
      $self->init(func_get_args());
      return $self;
    });
    $Buffer->set('prototype', Buffer::$protoObject);
    $Buffer->setMethods(Buffer::$classMethods, true, false, true);
    return $Buffer;
  }
}
Buffer::$classMethods = array(
  'isBuffer' => function($obj) {
      global $Buffer;
      return _instanceof($obj, $Buffer);
    },
  'byteLength' => function($string, $enc = null) {
      $b = new Buffer($string, $enc);
      return $b->length;
    }
);
Buffer::$protoMethods = array(
  'get' => function($index) {
      $self = Func::getContext();
      $i = (int)$index;
      if ($i < 0 || $i >= $self->length) {
        throw new Ex(Error::create('offset is out of bounds'));
      }
      return (float)ord($self->raw[$i]);
    },
  'set' => function($index, $byte) {
      $self = Func::getContext();
      $i = (int)$index;
      if ($i < 0 || $i >= $self->length) {
        throw new Ex(Error::create('offset is out of bounds'));
      }
      $old = $self->raw;
      $self->raw = substr($old, 0, $i) . chr($byte) . substr($old, $i + 1);
      return $self->raw;
    },
  'write' => function($data, $enc = null, $start = null, $len = null) {
      $self = Func::getContext();
      if (func_num_args() > 1 && !is_string($enc)) {
        $len = $start;
        $start = $enc;
        $enc = null;
      }
      $data = new Buffer($data, $enc);
      $new = $data->raw;
      if ($len !== null) {
        $newLen = (int)$len;
        $new = substr($new, 0, $newLen);
      } else {
        $newLen = $data->length;
      }
      $start = (int)$start;
      $old = $self->raw;
      $oldLen = $self->length;
      if ($start + $newLen > strlen($old)) {
        $newLen = $oldLen - $start;
      }
      $pre = ($start === 0) ? '' : substr($old, 0, $start);
      $self->raw = $pre . $new . substr($old, $start + $newLen);
    },
  'slice' => function($start, $end = null) {
      $self = Func::getContext();
      $len = $self->length;
      if ($len === 0) {
        return new Buffer(0);
      }
      $start = (int)$start;
      if ($start < 0) {
        $start = $len + $start;
        if ($start < 0) $start = 0;
      }
      if ($start >= $len) {
        return new Buffer(0);
      }
      $end = ($end === null) ? $len : (int)$end;
      if ($end < 0) {
        $end = $len + $end;
      }
      if ($end < $start) {
        $end = $start;
      }
      if ($end > $len) {
        $end = $len;
      }
      $new = substr($self->raw, $start, $end - $start);
      return new Buffer($new, 'binary');
    },
  'toString' => function($enc = 'utf8', $start = null, $end = null) {
      $self = Func::getContext();
      $raw = $self->raw;
      if (func_num_args() > 1) {
        $raw = substr($raw, $start, $end - $start + 1);
      }
      if ($enc === 'hex') {
        return bin2hex($raw);
      }
      if ($enc === 'base64') {
        return base64_encode($raw);
      }
      return $raw;
    },
  'toJSON' => function() {
      $self = Func::getContext();
      return $self->toJSON();
    },
  'inspect' => function() {
      $self = Func::getContext();
      return $self->toJSON(Buffer::$SHOW_MAX);
    },
  'clone' => function() {
      $self = Func::getContext();
      return new Buffer($self->raw, 'binary');
    }
);
Buffer::$protoObject = new Object();
Buffer::$protoObject->setMethods(Buffer::$protoMethods, true, false, true);
$global = Object::$global;
$undefined = null;
$Infinity = INF;
$NaN = NAN;
$Object = Object::getGlobalConstructor();
$Function = Func::getGlobalConstructor();
$Array = Arr::getGlobalConstructor();
$Boolean = Bln::getGlobalConstructor();
$Number = Number::getGlobalConstructor();
$String = Str::getGlobalConstructor();
$Date = Date::getGlobalConstructor();
$Error = Error::getGlobalConstructor();
$RangeError = RangeError::getGlobalConstructor();
$ReferenceError = ReferenceError::getGlobalConstructor();
$SyntaxError = SyntaxError::getGlobalConstructor();
$TypeError = TypeError::getGlobalConstructor();
$RegExp = RegExp::getGlobalConstructor();
$Buffer = Buffer::getGlobalConstructor();
$escape = call_user_func(function() {
  $list = array('%2A' => '*', '%2B' => '+', '%2F' => '/', '%40' => '@');
  return new Func(function($str) use (&$list) {
    $result = rawurlencode($str);
    foreach ($list as $pct => $ch) {
      $result = str_replace($pct, $ch, $result);
    }
    return $result;
  });
});
$unescape = new Func(function($str) {
  $str = str_replace('+', '%2B', $str);
  return urldecode($str);
});
$encodeURI = call_user_func(function() {
  $list = array('%21' => '!', '%27' => '\'', '%28' => '(', '%29' => ')', '%2A' => '*', '%7E' => '~');
  return new Func(function($str) use (&$list) {
    $result = rawurlencode($str);
    foreach ($list as $pct => $ch) {
      $result = str_replace($pct, $ch, $result);
    }
    return $result;
  });
});
$decodeURI = new Func(function($str) {
  $str = str_replace('+', '%2B', $str);
  return urldecode($str);
});
$encodeURIComponent = call_user_func(function() {
  $list = array('%21' => '!', '%23' => '#', '%24' => '$', '%26' => '&', '%27' => '\'', '%28' => '(', '%29' => ')', '%2A' => '*', '%2B' => '+', '%2C' => ',', '%2F' => '/', '%3A' => ':', '%3B' => ';', '%3D' => '=', '%3F' => '?', '%40' => '@', '%7E' => '~');
  return new Func(function($str) use (&$list) {
    $result = rawurlencode($str);
    foreach ($list as $pct => $ch) {
      $result = str_replace($pct, $ch, $result);
    }
    return $result;
  });
});
$decodeURIComponent = new Func(function($str) {
  $str = str_replace('+', '%2B', $str);
  return urldecode($str);
});
$isNaN = $Number->get('isNaN');
$isFinite = $Number->get('isFinite');
$parseInt = $Number->get('parseInt');
$parseFloat = $Number->get('parseFloat');
$Math = call_user_func(function() {
  $randMax = mt_getrandmax();
  $methods = array(
    'random' => function() use (&$randMax) {
        return (float)(mt_rand() / ($randMax + 1));
      },
    'round' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)round($num);
      },
    'ceil' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)ceil($num);
      },
    'floor' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)floor($num);
      },
    'abs' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)abs($num);
      },
    'max' => function() {
        $max = -INF;
        foreach (func_get_args() as $num) {
          $num = to_number($num);
          if (is_nan($num)) return NAN;
          if ($num > $max) $max = $num;
        }
        return (float)$max;
      },
    'min' => function() {
        $min = INF;
        foreach (func_get_args() as $num) {
          $num = to_number($num);
          if (is_nan($num)) return NAN;
          if ($num < $min) $min = $num;
        }
        return (float)$min;
      },
    'pow' => function($num, $exp) {
        $num = to_number($num);
        $exp = to_number($exp);
        if (is_nan($num) || is_nan($exp)) {
          return NAN;
        }
        return (float)pow($num, $exp);
      },
    'log' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)log($num);
      },
    'exp' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)exp($num);
      },
    'sqrt' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)sqrt($num);
      },
    'sin' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)sin($num);
      },
    'cos' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)cos($num);
      },
    'tan' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)tan($num);
      },
    'atan' => function($num) {
        $num = to_number($num);
        return is_nan($num) ? NAN : (float)atan($num);
      },
    'atan2' => function($y, $x) {
        $y = to_number($y);
        $x = to_number($x);
        if (is_nan($y) || is_nan($x)) {
          return NAN;
        }
        return (float)atan2($y, $x);
      }
  );
  $constants = array(
    'E' => M_E,
    'LN10' => M_LN10,
    'LN2' => M_LN2,
    'LOG10E' => M_LOG10E,
    'LOG2E' => M_LOG2E,
    'PI' => M_PI,
    'SQRT1_2' => M_SQRT1_2,
    'SQRT2' => M_SQRT2
  );
  $Math = new Object();
  $Math->setMethods($methods, true, false, true);
  $Math->setProps($constants, true, false, true);
  return $Math;
});
$JSON = call_user_func(function() {
  $decode = function($value) use (&$decode) {
    if ($value === null) {
      return Object::$null;
    }
    $type = gettype($value);
    if ($type === 'integer') {
      return (float)$value;
    }
    if ($type === 'string' || $type === 'boolean' || $type === 'double') {
      return $value;
    }
    if ($type === 'array') {
      $result = new Arr();
      foreach ($value as $item) {
        $result->push($decode($item));
      }
    } else {
      $result = new Object();
      foreach ($value as $key => $item) {
        if ($key === '_empty_') {
          $key = '';
        }
        $result->set($key, $decode($item));
      }
    }
    return $result;
  };
  $escape = function($str) {
    return str_replace("\\/", "/", json_encode($str));
  };
  $encode = function($parent, $key, $value, $opts, $encodeNull = false) use (&$escape, &$encode) {
    if ($value instanceof Object) {
      if (method_exists($value, 'toJSON')) {
        $value = $value->toJSON();
      } else
      if (($toJSON = $value->get('toJSON')) instanceof Func) {
        $value = $toJSON->call($value);
      } else
      if (($valueOf = $value->get('valueOf')) instanceof Func) {
        $value = $valueOf->call($value);
      }
    }
    if ($opts->replacer instanceof Func) {
      $value = $opts->replacer->call($parent, $key, $value, $opts->level + 1);
    }
    if ($value === null) {
      return $encodeNull ? 'null' : $value;
    }
    if ($value === Object::$null || $value === INF || $value === -INF) {
      return 'null';
    }
    $type = gettype($value);
    if ($type === 'boolean') {
      return $value ? 'true' : 'false';
    }
    if ($type === 'integer' || $type === 'double') {
      return ($value !== $value) ? 'null' : $value . '';
    }
    if ($type === 'string') {
      return $escape($value);
    }
    $opts->level += 1;
    $prevGap = $opts->gap;
    if ($opts->gap !== null) {
      $opts->gap .= $opts->indent;
    }
    $result = null;
    if ($value instanceof Arr) {
      $parts = array();
      $len = $value->length;
      for ($i = 0; $i < $len; $i++) {
        $parts[] = $encode($value, $i, $value->get($i), $opts, true);
      }
      if ($opts->gap === null) {
        $result = '[' . join(',', $parts) . ']';
      } else {
        $result = (count($parts) === 0) ? "[]" :
          "[\n" . $opts->gap . join(",\n" . $opts->gap, $parts) . "\n" . $prevGap . "]";
      }
    }
    if ($result === null) {
      $parts = array();
      $sep = ($opts->gap === null) ? ':' : ': ';
      foreach ($value->getOwnKeys(true) as $key) {
        $item = $value->get($key);
        if ($item !== null) {
          $parts[] = $escape($key) . $sep . $encode($value, $key, $item, $opts);
        }
      }
      if ($opts->gap === null) {
        $result = '{' . join(',', $parts) . '}';
      } else {
        $result = (count($parts) === 0) ? "{}" :
          "{\n" . $opts->gap . join(",\n" . $opts->gap, $parts) . "\n" . $prevGap . "}";
      }
    }
    $opts->level -= 1;
    $opts->gap = $prevGap;
    return $result;
  };
  $methods = array(
    'parse' => function($string, $reviver = null) use(&$decode) {
        $string = '{"_":' . $string . '}';
        $value = json_decode($string);
        if ($value === null) {
          throw new Ex(SyntaxError::create('Unexpected end of input'));
        }
        return $decode($value->_);
      },
    'stringify' => function($value, $replacer = null, $space = null) use (&$encode) {
        $opts = new stdClass();
        $opts->indent = null;
        $opts->gap = null;
        if (is_int_or_float($space)) {
          $space = floor($space);
          if ($space > 0) {
            $space = str_repeat(' ', $space);
          }
        }
        if (is_string($space)) {
          $length = strlen($space);
          if ($length > 10) $space = substr($space, 0, 10);
          if ($length > 0) {
            $opts->indent = $space;
            $opts->gap = '';
          }
        }
        $opts->replacer = ($replacer instanceof Func) ? $replacer : null;
        $opts->level = -1.0;
        $obj = ($opts->replacer !== null) ? new Object('', $value) : null;
        return $encode($obj, '', $value, $opts);
      }
  );
  $JSON = new Object();
  $JSON->setMethods($methods, true, false, true);
  $JSON->fromNative = $decode;
  return $JSON;
});
$console = call_user_func(function() {
  $stdout = defined('STDOUT') ? STDOUT : null;
  $stderr = defined('STDERR') ? STDERR : null;
  $toString = function($values) {
    $output = array();
    foreach ($values as $value) {
      if ($value instanceof Object) {
        $toString = $value->get('inspect');
        if (!($toString instanceof Func)) {
          $toString = $value->get('toString');
        }
        if (!($toString instanceof Func)) {
          $toString = Object::$protoObject->get('toString');
        }
        $value = $toString->call($value);
      } else {
        $value = to_string($value);
      }
      $output[] = $value;
    }
    return join(' ', $output) . "\n";
  };
  $console = new Object();
  $console->set('log', new Func(function() use (&$stdout, &$toString) {
    if ($stdout === null) {
      $stdout = fopen('php://stdout', 'w');
    }
    $output = $toString(func_get_args());
    write_all($stdout, $output);
  }));
  $console->set('error', new Func(function() use (&$stderr, &$toString) {
    if ($stderr === null) {
      $stderr = fopen('php://stderr', 'w');
    }
    $output = $toString(func_get_args());
    write_all($stderr, $output);
  }));
  return $console;
});
$process = new Object();
$process->set('sapi_name', php_sapi_name());
$process->set('exit', new Func(function($code = 0) {
  $code = intval($code);
  exit($code);
}));
$process->set('binding', new Func(function($name) {
  $module = Module::get($name);
  if ($module === null) {
    throw new Ex(Error::create("Binding `$name` not found."));
  }
  return $module;
}));
$process->argv = isset(GlobalObject::$OLD_GLOBALS['argv']) ? GlobalObject::$OLD_GLOBALS['argv'] : array();
$process->argv = array_slice($process->argv, 1);
$process->set('argv', Arr::fromArray($process->argv));


$apple_pie = null; $frame = null;
$Pie = new Func("Pie", function($filling = null) {
  $this_ = Func::getContext();
  call_method($this_, "fill_with", $filling);
  set($this_, "eaten", false);
});
set(get($Pie, "prototype"), "eat", new Func(function() {
  $this_ = Func::getContext();
  set($this_, "eaten", true);
}));
set(get($Pie, "prototype"), "fill_with", new Func(function($filling = null) {
  $this_ = Func::getContext();
  set($this_, "filling", $filling);
}));
$apple_pie = _new($Pie, "apple");
get($apple_pie, "eat");
$frame = "";
for ($i = 0.0; $i < 28.0; $i++) {
  $frame = _concat($frame, "\xE2\x98\x95");
}
call_method($console, "log", $frame);
call_method($console, "log", _concat("\xE2\x98\x95 Who ate the ", get($apple_pie, "filling"), " pie?!? \xE2\x98\x95"));
call_method($console, "log", $frame);
