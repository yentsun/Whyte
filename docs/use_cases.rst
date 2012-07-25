.. use_cases:

==========
Uses cases
==========

Defining a model
================

Lets imagine we have a *User* model in our project. We could define it in
``application/models/User.php`` using *Whyte Model* like this::

    <?php

    class Application_Model_User extends Whyte_Model_Entity {

        // we'll mention this line below
        protected $_mapper_class = 'Application_Model_UserMapper';

        protected $_properties = array(
            'id'=> array('Int', array('GreaterThan', 0)),
            'email'=> array('EmailAddress', 'presence'=>'required'),
            'password_hash'=> array('Hex', array('StringLength', 40, 40),
                                    'presence'=> 'required'),
            'first_name'=> array(),
            'last_name'=> array(),
            'phone'=> array(array('StringLength', 7, 18), 'presence'=>'required'),
            'registration_date'=> array(array('Date', 'YYYY-MM-dd')),
            'job_title'=> array('allowEmpty'=> true),
            'status_id'=> 'Int' // 0 - inactive; 1 - normal; 2 - admin
        );
    }

You can be sure that this is the only time we go through the *User* model
properties in the whole project. Oh wait! We have to map our properties to a
DB table fields. Let us do this in the *mapper*
``application/models/UserMapper.php``::

    <?php

    class Application_Model_UserMapper extends Whyte_Model_Mapper {

        protected $_table_name = 'user';

        protected $_map = array(
            'id'=> 'tbl_id',
            'email'=> 'tbl_login',
            'password_hash'=> 'tbl_password',
            'first_name'=> 'tbl_name',
            'last_name'=> 'tbl_family',
            'phone'=> 'tbl_phone',
            'registration_date'=> 'tbl_date',
            'club'=> 'tbl_club',
            'job_title'=> 'tbl_job',
            'status_id'=> 'tbl_show_it'
        );
    }

See those ``tbl_`` prefixes? The table fields a mighty old-school DB admin before
me defined. In this mapper we *connected* our model properties to DB table
fields.
