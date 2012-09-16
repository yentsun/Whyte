<?php

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

    /*Get required properties based on validators*/
    public static function get_required() {

        $class_name = get_called_class();
        $dummy = new $class_name();
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

        $class_name = get_called_class();
        $entity = new $class_name($data);
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
        if (method_exists($class_name, '_clear_cache'))
            $class_name::_clear_cache($class_name);
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
     * @return Whyte_Model_Entity|null
     */
    public static function fetch($id) {

        $class_name = get_called_class();
        $dummy = new $class_name();
        $mapper = new $dummy->_mapper_class;
        $data_array = $mapper->get('id', (int) $id);
        if ($data_array)
            return new $class_name($data_array);
        else
            return null;
    }

    private static function _fetch_clean($id) {

        $class_name = get_called_class();
        $dummy = new $class_name();
        $mapper = new $dummy->_mapper_class;
        $data_array = $mapper->get('id', (int) $id);
        if ($data_array)
            return new $class_name($data_array);
        else
            return null;
    }

    /**
     * Update existing entity record and return the updated instance. If data
     * to update is invalid, return error array.
     * @static
     * @param array $data
     * @param       $id
     * @return Whyte_Model_Entity|array
     */
    public static function update(array $data, $id, $add_to_index=true,
                                  $fetch_clean=true) {

        $class_name = get_called_class();
        $entity = $fetch_clean ?
            $class_name::_fetch_clean($id) :
            $class_name::fetch($id);
        if ($entity instanceof self) {
            foreach ($data as $name=>$value) {
                //update everything except 'id'
                if (array_key_exists($name, $entity->_validators) &&
                    $name != 'id')
                    $entity->$name = $value;
            }
        } else
            throw new Whyte_Exception_EntityNotFound();
        if ($entity->has_errors()) {
            $exception = new Whyte_Exception_EntityNotValid('Bad data!');
            $exception->messages = $entity->_errors;
            $exception->original_data = $data;
            throw $exception;
        } else {
            $dummy = new $class_name();
            $mapper = new $dummy->_mapper_class;
            $mapper->update($entity, 'id');
            if (method_exists($class_name, '_clear_cache'))
                $class_name::_clear_cache($class_name);
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
            if (method_exists($class_name, '_clear_cache'))
                $class_name::_clear_cache($class_name);
            if (method_exists($class_name, '_remove_from_search_index'))
                $class_name::_remove_from_search_index($id);
        } else
            throw new Exception('id must be set!');
    }

    /**
     * Clear cache with entity class name as tag. Should be overridden
     * per project
     * @static
     * @param $class_name
     */
    protected static function _clear_cache($class_name) {}

    /**
     * If entity has 'to_search_doc' method, perform addition to search index.
     * Should be specific per project
     * @param null $id
     */
    protected function _add_to_search_index($id=null) {}

    /**
     * Remove entity record from search index. Should be overridden per project
     * @param $id
     */
    protected function _remove_from_search_index($id) {}

    /**
     * Count all entity records
     * @static
     * @return int
     */
    public static function count_all() {

        $class_name = get_called_class();
        $dummy = new $class_name();
        $mapper = new $dummy->_mapper_class;
        return (int) $mapper->count_all();
    }

    /**
     * Fetch all entity records as objects
     * @static
     * @param null $limit
     * @return array
     */
    public static function fetch_all($limit=null) {

        $class_name = get_called_class();
        $dummy = new $class_name();
        $mapper = new $dummy->_mapper_class;
        $rows = $mapper->fetch_all($limit);
        $result_set = array();
        foreach ($rows as $row) {
            $entity = new $class_name($mapper->row_to_array($row));
            //save the row for further access to additional properties
            $entity->row = $row;
            $result_set[$entity->id] = $entity;
        }
        return $result_set;
    }

    /**
     * Search index for entities. Should be overridden per project
     * @static
     * @param $query
     */
    public static function search($query) {}

    /**
     * Return a dummy instance of an entity with all properties as empty
     * strings. Useful for populating an empty form for a new record while
     * keeping the same view for create/update.
     * @static
     * @return mixed
     */
    public static function dummy() {

        $class_name = get_called_class();
        $dummy = new $class_name();
        foreach ($dummy->_validators as $name=>$validators)
            $dummy->$name = '';
        return $dummy;
    }
}

class Whyte_Exception_EntityNotValid extends Exception {

    public $messages = null;
    public $original_data = null;
}

class Whyte_Exception_EntityNotFound extends Exception {}