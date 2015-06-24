.. image:: https://github.com/yentsun/Whyte/blob/master/images/logo-final-v01.png

**Whyte Model** is a set of two abstract classes used as a base for *model*
layer implementation in `Zend Framework <http://framework.zend.com/>`_.
It was made with two things in mind:

- **Define your model once** per project, and then be able to check its instances
  for validity anywhere. Be it *POST* data, parsed *xml* or external
  database input. Usually, when you use ``Zend_Form``, you *redefine* your model.

- Manage **forms without Zend_Form**. If you face an already coded
  html-template with complex forms, you will be getting hard times
  tailoring ``Zend_Form`` to output exactly the markup you need. Whyte Model
  doesn't mess with markup (in fact it doesn't render anything) but can
  validate and repopulate the form.

- **Legacy Database Structures**, where you rather map objects to existing table
  columns, but avoid using their original names like ``$this->tbl_something``
  along with bare ``Zend_DB_Table_Row``

Documentation with examples can be found at http://whyte.readthedocs.org/