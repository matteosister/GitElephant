GitElephant
===========

GitElephant is an abstraction layer to manage your git repositories with php

Requirements
------------

- php >= 5.3

    GitElephant uses namespaces. So PHP 5.3 is required

- *nix system with git installed

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
    $repo = new Repository('/path/to/git/repository');

    // get the current status
    $repo->getStatus(); // returns an array of lines of the status message

    // branches
    $repo->getBranches(); // return an array of TreeBranch objects
    $repo->getMainBranch(); // return the TreeBranch instance of the current checked out branch
    $repo->getBranch('master'); // return a TreeBranch instance by its name

    // tags
    $repo->getTags(); // array of TreeTag instances
    $repo->getTag('v1.0'); // a TreeTag instance by name

    // commit
    $repo->getCommit(); // get a Commit instance of the current HEAD
    $repo->getCommit('v1.0'); // get a Commit instance for a tag

You could also use GitElephant to manage your git repositories via PHP.

Your web server user (like www-data) needs to have access to the folder of the git repository

    // init
    $repo->init();

    // commit
    $repo->commit('my first commit', true);

    // manage branches
    $repo->createBranch('develop', 'master'); // create a develop branch from master
    $repo->createBranch('develop'); // create a develop branch from current checked out branch
    $repo->deleteBranch('develop'); // delete the develop branch

    // manage tags
    $repo->createTag('v1.0', 'master', 'my first release!'); // create  a tag named v1.0 from master with the given tag message
    $repo->createTag('v1.0', null, 'my first release!'); // create  a tag named v1.0 from the current checked out branch with the given tag message

