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

.. method:: count_all($string=null, $value=null)

   Count all records, optionally filtered by filter condition::

       return $mapper->count_all(
           $mapper->get_map_value('category_id').' IN (?)',
           $category_ids
       );

   :param string $string: condition string for ``where`` statement
   :param mixed $value: filter value