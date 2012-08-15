.. entity:

======
Entity
======

Public methods
==============

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