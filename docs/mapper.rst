.. mapper:

======
Mapper
======

``Whyte_Model_Mapper`` interacts with *Zend Framework's* data persistence layer.

Public methods
==============

.. method:: add(Whyte_Model_Entity $entity)

   Adds an entity to database::

       ...
       $new_id = $mapper->add($entity);
       ...

   :param Whyte_Model_Entity $entity: an entity instance
   :return: mixed (new record id)

.. method:: get($property, $value)

   Return a single record filtered by a property value::

       ...
       $data_array = $mapper->get('id', (int) $id);
       if ($data_array)
           return new Application_Model_User($data_array);
       ...

   :param string $property: property name
   :param mixed $value: property value
   :return: array | null

.. method:: update(Whyte_Model_Entity $entity, $key_property_name)

   Update entity record identified by key property::

       ...
       $mapper->update($user, 'id');
       ...

   :param Whyte_Model_Entity $entity: an updated entity instance
   :param string $key_property_name: the key property name

.. method:: delete($property, $value)

   Delete record(s) by property value and return number of affected records.

       ...
       $mapper->delete('id', 123);
       ...

   :param string $property: property name
   :param mixed $value: property value
   :return: int

.. method:: count_all($string=null, $value=null)

   Count all records, optionally filtered by filter condition::

       return $mapper->count_all(
           $mapper->get_map_value('category_id').' IN (?)',
           $category_ids
       );

   :param string $string: condition string for ``where`` statement
   :param mixed $value: filter value

.. method:: fetch_all($limit=null, array $where=null, array $where_not=null)

   Fetch a set of entity rows, optionally filtered by ``$where`` or
   ``$where_not`` parameter. Should be overridden per model for joined tables,
   various parameters, etc::

       ...
       $mapper = new Application_Model_GameMapper();
       $rows = $mapper->fetch_all($team_id, $cat_ids, $offset, $user_id);
       ...

   :return: array

.. method:: row_to_array(Zend_Db_Table_Row $input_data, array $add_properties=array())

   Transform Zend_Db_Table_Row to an assoc. array according to the map. This
   method should be used to spawn entity instances after table rows are
   received from DB::

      $mapper = new Application_Model_GameMapper();
      $rows = $mapper->fetch_all($team_id);
      $result_set = array();
      foreach ($rows as $row) {
          $game = new self($mapper->row_to_array($row));
      }
      return $result_set;

   :param Zend_Db_Table_Row $input_data: an fetched DB row
   :param array $add_properties: an array of arbitrary properties to add to
                                 the resulting array
   :return: array

.. method:: get_gateway()

   Return mapper's gateway. Usually an instance of ``Zend_Db_Table``::

       ...
       $mapper = new Application_Model_GameMapper();
       $adapter = $mapper->get_gateway()->getAdapter();
       $adapter->beginTransaction();
       ...

   :return: null | Zend_Db_Table | Zend_Db_Table_Abstract

.. method:: get_map()

   Return mapper's map::

       $team_mapper = new Application_Model_TeamMapper();
       $team_map = $team_mapper->get_map();
       $select
           ->joinLeft(array('team'=>'team'),
                     $this->_map['object_id'].' = team.id',
                      array('team_title'=>'team.'.$team_map['title'],
                            'team_logo'=>'team.'.$team_map['logo']));

   :return: array

.. method:: get_map_value($property)

   Get a mapped value of a property by its name::

       $team_mapper = new Application_Model_TeamMapper();
       $team_exist_validator = new Zend_Validate_Db_RecordExists(
                                      $team_mapper->get_table_name(),
                                      $team_mapper->get_map_value('id'));

   :param string $property: property name
   :return: string

.. method:: get_table_name()

   Return mapper's table name if any::

       $team_mapper = new Application_Model_TeamMapper();
       $team_exist_validator = new Zend_Validate_Db_RecordExists(
                                       $team_mapper->get_table_name(),
                                       $team_mapper->get_map_value('id'));

   :return: string | null