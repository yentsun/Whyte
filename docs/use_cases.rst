.. use_cases:

=========
Use cases
=========

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
            'status_id'=> 'Int' // like 0 - inactive; 1 - normal; 2 - admin
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
fields. If the DB table fields match the model ones, the use of a mapper can be
skipped.

Getting required fields for the form
====================================

Now let us work with the form. Say we have a ready form in html and we'd like
to mark required fields in it. In our *controller* we can::

    ...
    $this->view->required = Application_Model_User::get_required();
    ...

For the *User* model we defined earlier the ``required`` view variable will have
``'email', 'password_hash', 'first_name', 'last_name', 'phone',
'registration_date', 'status_id'``. Not all of those fields will be displayed
in our form for the user input but those are the fields required for the model.
We are going to add the rest in our code before validation.

Then, assuming fields of our form are named exactly as our *User* model
properties, in our form view we add the following lines::

   <script type="text/javascript">
       var required = <?= json_encode($this->required) ?>;
       $('form label, form input, form div, form select').each(function(){
           var name = $(this).attr('name');
           if (jQuery.inArray(name, required) !== -1) {
               $(this).addClass('required');
               $('label[for="'+name+'"]').append('<span class="req"> *</span>');
           }
       });
   </script>

This is just an example of processing and marking required fields of a form
with javascript and jquery. One can implement it even better.

Validating the form input
=========================

