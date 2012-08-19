<?php

abstract class Whyte_Model_Mapper {

    protected $_map = array();
    protected $_gateway = NULL;
    protected $_table_name = NULL;
    protected $_mappers = NULL;

    public function __construct(Zend_Db_Table_Abstract $gateway=NULL) {

        if (!$gateway) {
            if (isset($this->_table_name))
                $this->_gateway = new Zend_Db_Table($this->_table_name);
        } else {
            $this->_gateway = $gateway;
            $info = $this->_gateway->info();
            $this->_table_name = $info['name'];
        }
        $this->_init();
    }

    /**
     * Post-construction initialization hook. Override per mapper
     */
    protected function _init() {}

    /**
     * Get mapper's map value by its name
     * @param $property
     * @return mixed
     */
    public function get_map_value($property) {

        return $this->_map[$property];
    }

    /**
     * Return mapper's table name
     * @return null
     */
    public function get_table_name() {

        return $this->_table_name;
    }

    /**
     * Return mapper's map
     * @return array
     */
    public function get_map() {

        return $this->_map;
    }

    /**
     * Return mapper's gateway.
     * @return null|Zend_Db_Table|Zend_Db_Table_Abstract
     */
    public function get_gateway() {

        return $this->_gateway;
    }

    /**
     * Transform Zend_Db_Table_Row to an assoc. array according to the map
     * @param Zend_Db_Table_Row $input_data
     * @param array             $add_properties
     * @return array
     */
    public function row_to_array(Zend_Db_Table_Row $input_data,
        array $add_properties=array()) {

        $data = array();
        $enhanced_map = array_merge($this->_map, $add_properties);
        foreach ($enhanced_map as $modelProp => $mappedProp) {
            //null for missing row attribs
            $data[$modelProp] = isset($input_data->$mappedProp)
                ? $input_data->$mappedProp
                : null;
        }
        return $data;
    }

    /**
     * Turn entity to an assoc. array with mapped db column names
     * @param Whyte_Model_Entity $entity
     * @return array
     */
    protected function _to_mapped_array(Whyte_Model_Entity $entity) {

        $entity = $entity->to_array();
        $mapped_data = array();
        foreach ($this->_map as $prop_name=>$col_name) {
            if (isset($entity[$prop_name]))
                $mapped_data[$col_name] = $entity[$prop_name];
        }
        return $mapped_data;
    }

    /**
     * Add entity to database
     * @param Whyte_Model_Entity $entity
     * @return mixed
     */
    public function add(Whyte_Model_Entity $entity) {

        $mapped_data = $this->_to_mapped_array($entity);
        return $this->_gateway->insert($mapped_data);
    }

    /**
     * Return a single record as mapped assoc. array filtered by property value
     * @param $property
     * @param $value
     * @return array|null
     */
    public function get($property, $value) {

        $select = $this->_gateway->select()
            ->where($this->_map[$property].' = ?', $value);
        $row = $this->_gateway->fetchRow($select);
        return $row ? $this->row_to_array($row) : null;
    }

    /**
     * Update record for entity by key property
     * @param Whyte_Model_Entity $entity
     * @param $key_property_name
     */
    public function update(Whyte_Model_Entity $entity, $key_property_name) {

        $this->_gateway->update(
            $this->_to_mapped_array($entity),
            array($this->_map[$key_property_name].' = ?' =>
                  $entity->$key_property_name));
    }

    /**
     * Delete record(s) by property value
     * @param $property
     * @param $value
     * @return int
     */
    public function delete($property, $value) {

        $where = $this->_gateway
            ->getAdapter()
            ->quoteInto($this->_map[$property].' = ?', $value);
        return $this->_gateway->delete($where);
    }

    /**
     * Count all records (optionally filtered)
     * @param null $string
     * @param null $value
     * @return mixed
     */
    public function count_all($string=null, $value=null) {

        $select = $this->_gateway
            ->select()
            ->from($this->_table_name, array('count(*) as amount'));
        if ($string and $value) {
            $where = $this->_gateway->getAdapter()->quoteInto($string, $value);
            $select->where($where);
        }
        $row = $this->_gateway->fetchRow($select);
        return $row->amount;
    }

    /**
     * Fetch a set of entity rows
     * @param null  $limit
     * @param array $where
     * @param array $where_not
     * @return mixed
     */
    public function fetch_all($limit=null, array $where=null,
        array $where_not=null) {

        $select = $this->_gateway
            ->select();
        if ($limit)
            $select = $select->limit($limit);
        if ($where) {
            foreach ($where as $name=>$value) {
                $where = $this->_gateway
                    ->getAdapter()
                    ->quoteInto($this->_map[$name].' = ?', $value);
                $select->where($where);
            }
        }
        if ($where_not) {
            foreach ($where_not as $name=>$value) {
                $where = $this->_gateway
                    ->getAdapter()
                    ->quoteInto($this->_map[$name].' <> ?', $value);
                $select->where($where);
            }
        }
        $rows = $this->_gateway->fetchAll($select);
        return $rows;
    }
}