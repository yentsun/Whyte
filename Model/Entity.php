<?php

abstract class Whyte_Model_Entity {

	protected $_data = array();
	protected $_validators = array();
	protected $_filters = array();
	protected $_errors = null;
	protected $_fetched = array();

	public function __construct(array $data=null) {

		$this->_bootstrap();
		if ($data) {
			$this->_preDataPopulation();
			$this->_populateData($data);
			$this->_postDataPopulation();
		}
	}

	abstract protected function _dataPattern();

	protected function _setFilters() {

	}

	protected function _preDataPopulation() {

	}

	protected function _postDataPopulation() {

	}

	private function _bootstrap() {

		$this->_data = $this->_dataPattern();
		$this->_setFilters();
		$this->_validators = $this->_data;
		foreach ($this->_data as $property => $validator) {
			$this->_data[$property] = null;
		}
	}

	protected function _populateData(array $data) {

		foreach ($this->_data as $name => $value) {
			if (isset($data[$name]))
				$this->{$name} = $data[$name];
		}
	}

	public function toArray() {

		return $this->_data;
	}

	public function update(array $data,$skipEmpty=true) {

		foreach ($data as $property => $value) {
			if (array_key_exists($property,$this->_data)) {
				if ($skipEmpty) {
					if (!empty($data[$property])) 
						$this->$property = $data[$property];
				} else {
					$this->$property = $data[$property];
				}
			}
		}
		return $this;
	}

	public function __set($property,$value) {

		if (!array_key_exists($property,$this->_data))
			throw new Invalid_Input_Exception('Нельзя устанавливать новые свойства для объекта!');
		else {
			$this->_data[$property] = $value;
		}
	}

	public function &__get($property) {

		if (array_key_exists($property,$this->_data))
			return $this->_data[$property];
		else
			throw new Zend_Exception('Попытка получить несуществующее свойство ('.$property.')!');
	}

	public function __isset($property) {

		return isset($this->_data[$property]);
	}

	public function __unset($property) {

		if (isset($this->_data[$property])) {
			unset($this->_data[$property]);
		}
	}

	private function _filterAndCheck() {

		$entity = new Zend_Filter_Input($this->_filters,$this->_validators,$this->_data);
		if ($entity->isValid()) {
			foreach ($this->_filters as $name => $filters) {
				$this->_data[$name] = $entity->getUnescaped($name);
			}
			return true;
		} else {
			$this->_errors = $entity->getMessages();
			return false;
		}
	}

	public function hasErrors() {

		$this->_filterAndCheck();
		if (!empty($this->_errors)) return true;
		else return false;
	}

	public function getErrors() {

		return $this->_errors;
	}

	public function getValidators($property) {

		return $this->_validators[$property];
	}

	public function getRequired() {

		$requiredProperties = $this->_validators;
		foreach ($requiredProperties as $propName => $rules) {
			if (isset($rules['allowEmpty']))
				if ($rules['allowEmpty'] == true) 
					unset($requiredProperties[$propName]);
		}
		return array_keys($requiredProperties);
	}
}

class Invalid_Input_Exception extends Zend_Exception {

}
