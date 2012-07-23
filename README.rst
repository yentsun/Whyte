.. image:: http://yentsun.com/redmine/attachments/download/101/logo-final-v01.png

**Whyte Model** is a set of two abstract classes used as a base for *model*
layer implementation in `Zend Framework <http://framework.zend.com/>`_.

Installation
============

These are the steps for a unix-system. As they are trivial, one should be fine
following them on Windows.

1. Navigate to your ZF-project's library folder. It should be on *include_path*
   basically:

    cd myzfproject/library

2. Git clone the *Whyte* library into the folder:

    git clone git://github.com/yentsun/Whyte.git

3. If your project follows `the recommended structure
   <http://framework.zend.com/manual/ru/project-structure.project.html>`_,
   in your ``application/configs/application.ini`` add *autoloadernamespaces*
   for class autoloading:

    autoloadernamespaces.whyte = Whyte_

Or you can simply add a ``require`` statement where appropriate.

Done! Now you can inherit *Whyte_Model_Entity* and *Whyte_Model_Mapper* classes
in your models/mappers:

    <?php

    class Application_Model_Something extends Whyte_Model_Entity {

    ...

    }