.. Whyte documentation master file, created by
   sphinx-quickstart on Mon Jul 23 17:58:19 2012.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

About
=====

**Whyte Model** is a set of two abstract classes used as a base for *model*
layer implementation in `Zend Framework <http://framework.zend.com/>`_.
It was made with two things in mind:

1. *Legacy Database Structures*, where you rather map objects to existing table
   fields, but not use their original names like ``$this->tbl_something``
   along with bare ``Zend_DB_Table_Row``
2. Manage *forms without Zend_Form*. Again, if you face an already
   coded html-template with complex forms, you will be getting hard times
   tailoring ``Zend_Form`` to output exactly the markup you need. Whyte Model
   doesn't mess with markup (in fact it doesn't render anything) but can
   validate and repopulate the form.

Contents:

.. toctree::
   :maxdepth: 2

    entity <entity>
    mapper

Installation
============

These are the steps for a unix-system. As they are trivial, one should be fine
following them on Windows.

1. Navigate to your ZF-project's library folder. It should be on *include_path*
   basically::

    cd myzfproject/library

   .. admonition:: Note

      Whyte Model assumes you have *Zend Autoloader* employed.

2. Download the package into the folder::

    git clone git://github.com/yentsun/Whyte.git

3. If your project follows `the recommended structure
   <http://framework.zend.com/manual/ru/project-structure.project.html>`_,
   in your ``application/configs/application.ini`` add *autoloadernamespaces*
   for class autoloading::

    autoloadernamespaces.whyte = "Whyte_"

   Or you can simply add a ``require`` statement where appropriate.

Done! Now you can inherit *Whyte_Model_Entity* and *Whyte_Model_Mapper* classes
in your models/mappers::

    <?php

    class Application_Model_Something extends Whyte_Model_Entity {

    ...

    }


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

