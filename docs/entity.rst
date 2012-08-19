.. entity:

======
Entity
======

``Whyte_Model_Entity`` is the core of *Whyte Model*. It serves as the base for all
models of a project.

Public methods
==============

.. method:: fetch($id)

  Fetch entity from DB and return it as ``Whyte_Model_Entity`` instance. If
  there is no DB record with the given ``$id`` - return ``null``. This is a
  static method::

      ...
      $id = $this->_getParam('id');
      $this->view->category = Application_Model_Category::fetch($id);
      ...

  It utilizes ``Whyte_Model_Mapper::get()`` to fetch the data from DB. You are
  encouraged to override the ``fetch()`` method per model to fine-tune fetched
  data (like use another mapper method to join with another table, etc).

  :param $id: entity's id

.. method:: fetch_all($limit=null)

   This static method is mostly an example of fetching all records of an entity.
   Usually one will have to override it per model to implement different
   filtering techniques::

       ...
       // here is an overridden `fetch_all` with some model-specific params
       $this->view->teams = Application_Model_Team::fetch_all($sport_id, $page);
       ...

   :param $limit: limit the result set
   :return: array

.. method:: create(array $data, $add_to_index=true)

  A static method that can be used to persist an entity::

      try {
        $new_id = Application_Model_Entity::create($data);
      } catch (Whyte_Exception_EntityNotValid $e) {
        ...
      }

  It returns the new entity id or throws ``Whyte_Exception_EntityNotValid`` if
  the given ``$data`` fails to pass validation.

  :param $data: entity data presented as an assoc. array
  :param $add_to_index: add to search index if true

.. method:: update(array $data, $id, $add_to_index=true)

  A static method that can be used to update a persisted entity::

      ...
      if ($this->_request->isPost()) {
          $data = $this->_request->getPost();
          try {
              $updated_game = Application_Model_Game::update($data, $id);
          } catch (Whyte_Exception_EntityNotValid $e) {
              ...
          }
      }
      ...

  It returns the updated entity or throws ``Whyte_Exception_EntityNotValid`` if
  the given ``$data`` fails to pass validation or throws
  ``Whyte_Exception_EntityNotFound``, if there is no record with given ``$id``.

  :param $data: entity data presented as an assoc. array
  :param $id: entity's id
  :param $add_to_index: add to search index if true

.. method:: delete($id)

   A static method that can be used to delete a persisted entity::

       $id = $this->_getParam('id');
       try {
           Application_Model_User::delete($id);
       } catch (Exception $e) {
           ...
       }

   If a record with ``$id`` is not found, it throws an ``Exception``.

   :param $id: entity's id

.. method:: to_array()

  Return entity instance as an associative array::

      $user->to_array();

      /*Returns something like:
      array(
          'id'=> 345,
          'email'=> 'user@example.com',
          'password_hash'=> '601f1889667efaebb33b8c12572835da3f027f78',
          'first_name'=> 'Mike',
          'last_name'=> 'Smith',
          'phone'=> '433-7300',
          'registration_date'=> '2012-06-01',
          'job_title'=> 'scout',
          'status_id'=> 1
      );
      */

.. method:: has_errors()

  Check if instance has errors (is invalid)::

      if ($user->has_errors()){
          echo 'Bad data';
          ...
      }

.. method:: get_validators($name)

  Return entity's validators by a property name::

      $user->get_validators('email');

      /* Returns something like:
         array('EmailAddress', 'presence'=>'required')
      */

  :param $name: Name of the property in question

  This method is useful when you define a new model that has similar properties
  with an already defined one::

      ...
      'email_address'=> $user->get_validators('email'),
      ...

.. method:: count_all()

   Count all entity records. It calls the
   `Whyte_Model_Mapper::count_all() <mapper.html#count_all>`_
   method which in turn generates a ``select count(*)`` query.

   :return: int

.. method:: dummy()

   This static method returns a 'dummy' instance of an entity with all
   properties as empty strings. Useful for populating an empty form for a
   new record while keeping the same create/update template.