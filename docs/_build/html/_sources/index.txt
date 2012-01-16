.. GitElephant documentation master file, created by
   sphinx-quickstart on Mon Jan 16 22:53:40 2012.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

.. highlight:: php

Welcome to GitElephant's documentation!
=======================================

Contents:

.. toctree::
   :maxdepth: 2

.. image:: http://stillmaintained.com/matteosister/GitElephant.png
.. image:: https://secure.travis-ci.org/matteosister/GitElephant.png

GitElephant is an abstraction layer to manage your git repositories with php

Watch a `simple live example <http://gitelephant.cypresslab.net/>`_ of what you can do with GitElephant, `GitElephantBundle <https://github.com/matteosister/GitElephantBundle>`_, Symfony2 and a git repository...

`Download the demo bundle code <https://github.com/matteosister/GitElephantDemoBundle/>`_ used in the live example

Requirements
------------

* php >= 5.3
* \*nix system with git installed

I work on an ubuntu box, but the lib should work well with every unix system. I don't have a windows installation to test...if someone want to help...

Installation
------------

**composer**

To install GitElephant with composer you simply need to create a *composer.json* in your project root and add

.. code-block:: javascript

    {
        "require": {
            "cypresslab/gitelephant": ">=0.6.0"
        }
    }

Then run

.. code-block:: bash

    $ wget -nc http://getcomposer.org/composer.phar
    $ php composer.phar install

You have now GitElephant installed in *vendor/cypresslab/gitelephant*

And an handy autoload file to include in you project in *vendor/.composer/autoload.php*

**pear**

Add the Cypresslab channel

.. code-block:: bash

    $ pear channel-discover pear.cypresslab.net

And install the package. *By now GitElephant is in alpha state. So remember the -alpha in the library name*

.. code-block:: bash

    $ pear install cypresslab/GitElephant-alpha

On `Cypresslab pear channel homepage <http://pear.cypresslab.net/>`_ you can find other useful information

Testing
-------

The library is fully tested with PHPUnit for unit tests, and Behat for BDD. To run tests you need these (awesome) libraries installed on your system.

Go to the base library folder and run the test suites

.. code-block:: bash

    $ phpunit # phpunit test suite
    $ behat # behat test suite

Code style
----------

* GitElephant follows the `Symfony2 Coding Standard <https://github.com/opensky/Symfony2-coding-standard>`_
* I'm using `gitflow <https://github.com/nvie/gitflow>`_

How to use
----------

::

    <?php

    use GitElephant\Repository;
    $repo = new Repository('/path/to/git/repository');

the *Repository* class is the main class where you can find every method you need...

**Read repository**::

    <?php
    // get the current status
    // returns an array of lines of the status message
    $repo->getStatus();

    // branches
    // return an array of TreeBranch objects
    $repo->getBranches();
    // return the TreeBranch instance of the current checked out branch
    $repo->getMainBranch();
    // return a TreeBranch instance by its name
    $repo->getBranch('master');

    // tags
    // array of TreeTag instances
    $repo->getTags();
    // a TreeTag instance by name
    $repo->getTag('v1.0');

    // commit
    // get a Commit instance of the current HEAD
    $repo->getCommit();
    // get a Commit instance for a tag
    $repo->getCommit('v1.0');
    // sha (follow git standard to format the sha)
    $repo->getCommit('1ac370d');

    // Log contains a collection of commit objects
    // syntax: getLog(<tree-ish>, limit = 15, offset = null)
    $log = $repo->getLog();
    $log = $repo->getLog('master', 5);
    $log = $repo->getLog('v0.1', 5, 10);

    // countable
    $log->count();
    count($log);

    // iterable
    foreach ($log as $commit) {
        echo $commit->getMessage();
    }

**Manage repository**

You could also use GitElephant to manage your git repositories via PHP.

Your web server user (like www-data) needs to have access to the folder of the git repository::

    <?php
    // init
    $repo->init();
    // or clone
    $repo->cloneFrom("git://github.com/matteosister/GitElephant.git");

    // stage changes
    $repo->stage('file1.php');
    $repo->stage(); // stage all

    // commit
    $repo->commit('my first commit');
    // commit and stage every pending changes in the working tree
    $repo->commit('my first commit', true);

    // checkout a tag
    $repo->checkout($this->getCommit('v1.0'));
    // checkout master
    $repo->checkout('master');

    // manage branches
    // create a develop branch from master
    $repo->createBranch('develop', 'master');
    // create a develop branch from current checked out branch
    $repo->createBranch('develop');
    // delete the develop branch
    $repo->deleteBranch('develop');

    // manage tags
    // create  a tag named v1.0 from master with the given tag message
    $repo->createTag('v1.0', 'master', 'my first release!');
    // create  a tag named v1.0 from the current checked out branch with the given tag message
    $repo->createTag('v1.0', null, 'my first release!');
    // create a tag from a Commit object
    $repo->createTag($repo->getCommit());

A versioned tree of files
-------------------------

A git repository is a tree structure versioned in time. So if you need to represent a repository in a, let's say, web browser, you will need
a tree representation of the repository, at a given point in history.

**Tree class**::

    <?php
    // retrieve the actual *HEAD* tree
    $tree = $repo->getTree();
    // retrieve a tree for a given commit
    $tree = $repo->getTree($repo->getCommit('1ac370d'));
    // retrieve a tree for a given path
    $tree = $repo->getTree('master', 'lib/vendor');

The Tree class implements *ArrayAccess*, *Countable* and *Iterator* interfaces.

You can use it as an array of git objects::

    <?php
    foreach ($tree as $treeObject) {
        echo $treeObject;
    }

A TreeObject instance is a php representation of a node in a git tree::

    <?php
    echo $treeObject; // the name of the object (folder, file or link)
    $treeObject->getType(); // a class constanf of TreeObject::TYPE_BLOB, TreeObject::TYPE_TREE and TreeObject::TYPE_LINK
    $treeObject->getSha();
    $treeObject->getSize();
    $treeObject->getName();
    $treeObject->getSize();
    $treeObject->getPath();

You can also pass a tree object to the repository to get its subtree::

    <?php
    $subtree = $repo->getTree('master', $treeObject);

Diffs
-----

If you want to check a Diff between two commits the Diff class comes in::

    <?php
    // get the diff between the given commit and it parent
    $diff = $repo->getDiff($repo->getCommit());
    // get the diff between two commits
    $diff = $repo->getDiff($repo->getCommit('1ac370d'), $repo->getCommit('8fb7281'));
    // same as before for a given path
    $diff = $repo->getDiff($repo->getCommit('1ac370d'), $repo->getCommit('8fb7281'), 'lib/vendor');
    // or even pass a TreeObject
    $diff = $repo->getDiff($repo->getCommit('1ac370d'), $repo->getCommit('8fb7281'), $treeObject);

The Diff class implements *ArrayAccess*, *Countable* and *Iterator* interfaces

You can iterate over DiffObject::

    <?php
    foreach ($diff as $diffObject) {
        // mode is a constant of the DiffObject class
        // DiffObject::MODE_INDEX an index change
        // DiffObject::MODE_MODE a mode change
        // DiffObject::MODE_NEW_FILE a new file change
        // DiffObject::MODE_DELETED_FILE a deleted file change
        echo $diffObject->getMode();
    }

A DiffObject is a class that implements *ArrayAccess*, *Countable* and *Iterator* interfaces.

It represent a file, folder or submodule changed in the diff

Every DiffObject can have multiple chunks of changes. For example "added 3 lines at line 20" and "modified 4 lines at line 560"

So you can iterate over DiffObject to get DiffChunks. DiffChunks are the last steps of the diff iteration.

They are a collection of DiffChunkLine Objects::

    <?php
    foreach ($diffObject as $diffChunk) {
        if (count($diffChunk) > 0) {
            echo "change detected from line ".$diffChunk->getDestStartLine()." to ".$diffChunk->getDestEndLine();
            foreach ($diffChunk as $diffChunkLine) {
                echo $diffChunkLine; // output the line content
            }
        }
    }

This is just an example of what the Diff class can do. Run the diff behat test suite for other nice things::

    $ behat features/diff.feature


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

