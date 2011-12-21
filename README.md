GitElephant
===========

GitElephant is an abstraction layer to manage your git repositories with php

Requirements
------------

- php >= 5.3

    GitElephant uses namespaces. So PHP 5.3 is required

- *nix system

    I work on an ubuntu box, but the lib should work well with every unix system. I don't have a windows installation to test...if someone want to help...

Testing
-------

The library is fully tested with PHPUnit for unit tests, and Behat for BDD.

To run tests you need this (awesome) libraries installed on your system.

Go to the base library folder and run the test suite you prefer

    // phpunit test suite
    $ phpunit
    // behat test suite
    $ behat

Coding standards
----------------

GitElephant follows the Symfony2 Coding Standard

How to use
----------

    <?php

    use GitElephant\Repository,
    GitElephant

    $repo = new Repository();