<?php

namespace TesseractOcr;

abstract class Enum {

	private static $_collection = array();

	private static $_constantes = null;

	public static function __callstatic($name, $args) {
	    throw new \Exception("__callstatic Not defined");
	    return self::_load(__CLASS__, $name);
	}

	public function load($class) {
		if (empty(self::$_constantes[$class])) {
			$re = new \ReflectionClass($class);
			self::$_constantes[$class] = $re->getConstants();
			self::$_collection[$class] = array();
		}
	}

	protected static function _load($class, $name) {
		self::load($class);

		if (!isset(self::$_constantes[$class][$name])) {
			throw new \Exception("Not defined {$name}");
		}

		if (empty(self::$_collection[$class][$name])) {
			self::$_collection[$class][$name] = new $class($name, self::$_constantes[$class][$name]);
		}
		return self::$_collection[$class][$name];
	}




	public static function getValues($class) {
		if (empty(self::$_constantes)) {
			throw new \Exception("Enum not load");
		}
		return self::$_constantes;
	}

	public static function getName($class, $index) {
	    $class = trim($class, '\\');
		if (empty(self::$_constantes[$class])) {
			throw new \Exception("Enum not load");
		}

		return array_search($index, self::$_constantes[$class]);
	}


	private $_name;
	private $_value;

	public function __construct($name, $value) {
		$this->_name = $name;
		$this->_value = $value;
	}

	public function name() {
		return $this->_name;
	}

	public function value() {
		return $this->_value;
	}

}