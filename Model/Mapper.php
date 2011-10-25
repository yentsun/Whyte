<?php

abstract class Whyte_Model_Mapper {

	protected $_map = array();
	protected $_gateway = null;
	protected $_identityMap = null;
	protected $_mappers = null;
	protected $_factoryErrors = array();
	public $paginator = null;

	public function __construct(Zend_Db_Table_Abstract $gateway=null) {

		$this->_init($gateway);
		$this->_getMappers();
		$this->_setMap();
	}

	protected function _init() {

		if (isset($this->_tableName))
			$this->_gateway = new Zend_Db_Table($this->_tableName);
	}

	abstract protected function _setMap();

	public function getMapValue($property) {

		return $this->_map[$property];
	}

	public function getMap() {

		return $this->_map;
	}

	protected function _factory($inputData,array $additionalData=null,array $exclude=null) {

		$data = array();
		if ($inputData instanceof Zend_Db_Table_Row) { //if we take data from DB gateway
			$map = $this->_map;
			if (!empty($exclude)) {
				foreach ($exclude as $field)
					unset($map[$field]);
			}
			foreach ($map as $modelProp => $mappedProp) {
				$data[$modelProp] = $inputData->$mappedProp;
			}
		} elseif (is_array($inputData) && !empty($additionalData)) { //if we take additional data array
			foreach ($this->_map as $modelProp => $mappedProp) {
				if (is_string($mappedProp))
					$data[$modelProp] = $inputData[$mappedProp];
				$data = array_merge($data,$additionalData);
			}
		} elseif (is_array($inputData)) { //if we take from POST 
			foreach ($this->_map as $modelProp => $mappedProp) {
				if (isset($inputData[$modelProp]))
					$data[$modelProp] = $inputData[$modelProp];
			}
		}

		return new $this->_entityClass($data);
	}

	protected function _toMappedArray($data) {

		if ($data instanceof Whyte_Model_Entity)
			$data = $data->toArray();
		if (is_array($data)) {
			$mappedData = array();
			foreach ($this->_map as $modelProp => $mappedProp) {
				if (isset($data[$modelProp])) $mappedData[$mappedProp] = $data[$modelProp];
			}
			return $mappedData;
		} else
			return false;
	}

	public function getGateway() {

		return $this->_gateway;
	}

	public function getTableName() {

		return isset($this->_tableName) ? $this->_tableName : null;
	}

	protected function _getSingleEntity($mappedProperty,$value) {

		$select = $this->_gateway->select()
				->where($mappedProperty.' = ?',$value);
		$row = $this->_gateway->fetchRow($select);
		return $this->_factory($row);
	}

	protected function _getIdentity($id) {

		if (array_key_exists($id,$this->_identityMap))
			return $this->_identityMap[$id];
	}

	protected function _setIdentity($id,$entity) {

		$this->_identityMap[$id] = $entity;
	}

	protected function _paginate(Zend_Db_Select $select,$page=1,$perPage=null) {

		Zend_Paginator::setDefaultScrollingStyle('Elastic');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('_pagination.phtml');
		$paginator = Zend_Paginator::factory($select);
		$paginator->setCurrentPageNumber($page);
		$perPage = $perPage ? $perPage : Zend_Registry::get('config')->pagination->items->perpage;
		$paginator->setItemCountPerPage($perPage);
		$paginator->setPageRange(Zend_Registry::get('config')->pagination->items->range);

		$this->paginator = $paginator;

		return $paginator->getCurrentItems();
	}

	public function addJoin(Zend_Db_Select $select) {

		$column = isset($this->_joinBy) ? $this->_joinBy : $this->_map['id'];
		return $select->joinUsing($this->_tableName,$column);
	}

	protected function _getMappers() {

		if (!empty($this->_mappers)) {
			$mapperObjects = array();
			foreach ($this->_mappers as $key=>$name) {
				$className = 'Application_Model_'.$name.'Mapper';
				if (class_exists($className)) {
					$mapperObjects[$name] = new $className;
				}
			}
			$this->_mappers = $mapperObjects;
		}
	}

	protected function _errorAndRepopulate(array $errors,array $data) {

		return '<div class="error">Форма заполнена неверно!<div class="data">'.htmlentities(json_encode($errors)).'</div><div class="repopulate">'.htmlentities(json_encode($data)).'</div></div>';
	}

	protected function _prepareItems($rows) {
		
		$result = array();
		foreach ($rows as $row) {
			$result[] = $this->_factory($row);
		}
		return $result;
	}
}
