<?php

namespace TesseractOcr;

abstract class Enum {

	private static $_collection = array();

	private static $_constantes = null;

	public function load($class) {
		if (self::$_constantes === null) {
			$re = new \ReflectionClass($class);
			self::$_constantes = $re->getConstants();
		}
	}

	protected static function _load($class, $name) {
		self::load($class);

		if (!array_key_exists($name, self::$_constantes)) {
			throw new \Exception("Not defined {$name}");
		}

		if (empty(self::$_collection[$name])) {
			self::$_collection[$name] = new $class($name, self::$_constantes[$name]);
		}
		return self::$_collection[$name];
	}

	public static function __callstatic($name, $args) {
		throw new \Exception("__callstatic Not defined");
		//return self::_load(__CLASS__, $name);
	}


	public static function getValues($class) {
		if (empty(self::$_constantes)) {
			throw new \Exception("Enum not load");
		}
		return self::$_constantes;
	}

	public static function getName($index) {
		if (empty(self::$_constantes)) {
			throw new \Exception("Enum not load");
		}
		throw new \Exception("implementar reflexão do getName");
	}


	private $_name;
	private $_value;

	public function __construct($name, $value) {
		$this->name = $name;
		$this->value = $value;
	}

	public function name() {
		return $this->name;
	}

	public function value() {
		return $this->_value;
	}

}