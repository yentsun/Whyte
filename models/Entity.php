<?php

require '../exceptions/EntityNotValid.php';

abstract class Whyte_Model_Entity {

    protected $_mapper_class = null;
    protected $_properties = array();
    protected $_validators = array();
    protected $_filters = array();
    protected $_errors = array();
    protected $_fetched = array();
    public static $paginator = null;

    public function __construct($data=NULL) {

        $this->_pre_data_population($data);
        $this->_move_validators();
        if ($data)
            $this->_populate_data($data);
        $this->_post_data_population();
    }

    protected function _pre_data_population(array $data=null) {}

    protected function _post_data_population() {}

    /*take all validators away from _properties
    and populate _validators*/
    private function _move_validators() {

        $this->_validators = $this->_properties;
        $this->_properties = array();
    }

    protected function _populate_data(array $data) {

        foreach ($data as $name=>$value) {
            if (isset($data[$name]))
                $this->{$name} = $data[$name];
        }
    }

    public function to_array() {

        return $this->_properties;
    }

    public function __set($name, $value) {

        if (array_key_exists($name, $this->_validators))
            $this->_properties[$name] = $value;
    }

    public function __get($name) {

        if (array_key_exists($name, $this->_properties))
            return $this->_properties[$name];
        else
            return null;
    }

    public function __isset($name) {

        return isset($this->_properties[$name]);
    }

    public function __unset($name) {

        if (isset($this->_properties[$name])) {
            unset($this->_properties[$name]);
        }
    }

    private function _filter_and_check() {

        $entity = new Zend_Filter_Input($this->_filters, $this->_validators,
            $this->_properties);
        if ($entity->isValid()) {
            foreach ($this->_validators as $name => $vals) {
                $this->_properties[$name] = $entity->getUnescaped($name);
            }
            return true;
        } else {
            $this->_errors = $entity->getMessages();
            return false;
        }
    }

    public function has_errors() {

        $this->_filter_and_check();
        if (!empty($this->_errors))
            return true;
        else
            return false;
    }

    public function get_validators($name) {

        return $this->_validators[$name];
    }

    /**
     * If entity has 'datetime' property, return Zend_Date->toString() in given
     * format
     * @param string $format
     * @return null|string
     */
    public function datetime($format=null, $property_name='datetime',
        $input_format=null) {

        $format = $format ? $format : Zend_Date::DATE_MEDIUM;
        if (isset($this->_properties[$property_name]))
            if ($this->_properties[$property_name]) {
                if ($this->$property_name != '0000-00-00') {
                    $date = new Zend_Date($this->$property_name, $input_format);
                    return $date->toString($format);
                }
            }
        return null;
    }

    /*Get required properties based on validators*/
    public static function get_required() {

        $classname = get_called_class();
        $dummy = new $classname();
        $required_properties = $dummy->_validators;
        foreach ($required_properties as $name=>$rules) {
            if (isset($rules['allowEmpty']))
                if ($rules['allowEmpty'] == true)
                    unset($required_properties[$name]);
        }
        return array_keys($required_properties);
    }

    /**
     * Create entity instance and if its valid, record to DB and return new
     * instance id. Otherwise return error array.
     * @static
     * @param array $data
     * @param bool  $add_to_index
     * @return array | int
     */
    public static function create(array $data, $add_to_index=true) {

        $classname = get_called_class();
        $entity = new $classname($data);
        if ($entity->has_errors()){
            $array_of_errors = array();
            foreach ($entity->_errors as $key=>$array)
                $array_of_errors[] = implode(' ', $array);
            $exception = new Whyte_Exception_EntityNotValid(sprintf(
                'Bad data (%s)', implode(' ', $array_of_errors)));
            $exception->messages = $entity->_errors;
            $exception->original_data = $data;
            throw $exception;
        }
        $mapper = new $entity->_mapper_class;
        self::_clear_cache($classname);
        $new_id = $mapper->add($entity);
        if ($add_to_index && is_int($new_id))
            $entity->_add_to_search_index($new_id);
        return $new_id;
    }

    /**
     * Fetch entity instance by id and return it.
     * If nothing is found - return NULL
     * @static
     * @param $id
     * @return Application_Model_Entity|null
     */
    public static function fetch($id) {

        $classname = get_called_class();
        $dummy = new $classname();
        $mapper = new $dummy->_mapper_class;
        $data_array = $mapper->get('id', (int) $id);
        if ($data_array)
            return new $classname($data_array);
        else
            return null;
    }

    /**
     * Update existing entity record and return the updated instance. If data
     * to update is invalid, return error array.
     * @static
     * @param array $data
     * @param       $id
     * @return \Application_Model_Entity|array
     */
    public static function update(array $data, $id, $add_to_index=true) {

        $classname = get_called_class();
        $entity = $classname::fetch($id);
        foreach ($data as $name=>$value) {
            //update everything except 'id'
            if (array_key_exists($name, $entity->_validators) && $name != 'id')
                $entity->$name = $value;
        }
        if ($entity->has_errors()) {
            $exception = new Whyte_Exception_EntityNotValid('Bad data!');
            $exception->messages = $entity->_errors;
            $exception->original_data = $data;
            throw $exception;
        } else {
            $dummy = new $classname();
            $mapper = new $dummy->_mapper_class;
            $mapper->update($entity, 'id');
            self::_clear_cache($classname);
            if ($add_to_index)
                $entity->_add_to_search_index();
            return self::fetch($entity->id);
        }
    }

    /**
     * Delete entity record by id
     * @static
     * @throws Exception
     * @param $id
     */
    public static function delete($id) {

        if ($id) {
            $class_name = get_called_class();
            $dummy = new $class_name();
            $mapper = new $dummy->_mapper_class;
            $rows = $mapper->delete('id', $id);
            if (!$rows)
                throw new Exception('Now rows were affected!');
            self::_clear_cache($class_name);
            self::_remove_from_search_index($id);
        } else
            throw new Exception('id must be set!');
    }

    /**
     * Clear 'functions' cache with entity class name as tag
     * @static
     * @param $class_name
     */
    private static function _clear_cache($class_name) {

        $cache_manager = Zend_Registry::get('cache_manager');
        $cache = $cache_manager->getCache('functions');
        $cache->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array($class_name)
        );
    }

    /**
     * If entity has 'to_search_doc' method, perform addition to search index
     * @param null $id
     */
    private function _add_to_search_index($id=null) {

        if (method_exists($this, 'to_search_doc')) {
            $search = new Application_Model_Search(get_called_class());
            $search->save_document($this->to_search_doc($id));
        }
    }

    private function _remove_from_search_index($id) {

        $search = new Application_Model_Search(get_called_class());
        $search->delete($id);
    }

    /**
     * Simple count all entity records
     * @static
     * @return int
     */
    public static function count_all() {

        $classname = get_called_class();
        $dummy = new $classname();
        $mapper = new $dummy->_mapper_class;
        return (int) $mapper->count_all();
    }

    /**
     * Fetch all entity records as instances
     * @static
     * @return array
     */
    public static function fetch_all($limit=null) {

        $classname = get_called_class();
        $dummy = new $classname();
        $mapper = new $dummy->_mapper_class;
        $rows = $mapper->fetch_all($limit);
        $result_set = array();
        foreach ($rows as $row) {
            $entity = new $classname($mapper->row_to_array($row));
            $entity->row = $row;
            $result_set[$entity->id] = $entity;
        }
        return $result_set;
    }

    public static function search($query) {

        $search = new Application_Model_Search(get_called_class());
        return $search->perform_search($query);
    }

    /**
     * Return a dummy instance of an entity with all properties as empty strings
     * @static
     * @return mixed
     */
    public static function dummy() {

        $classname = get_called_class();
        $dummy = new $classname();
        foreach ($dummy->_validators as $name=>$validators)
            $dummy->$name = '';
        return $dummy;
    }
}