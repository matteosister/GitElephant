GitElephant
===========

GitElephant is an abstraction layer to manage your git repositories with php

Requirements
------------

- php >= 5.3
- *nix system with git installed

I work on an ubuntu box, but the lib should work well with every unix system. I don't have a windows installation to test...if someone want to help...

Testing
-------

The library is fully tested with PHPUnit for unit tests, and Behat for BDD. To run tests you need these (awesome) libraries installed on your system.

Go to the base library folder and run the test suites

    // phpunit test suite
    $ phpunit
    // behat test suite
    $ behat

Code style
----------

* GitElephant follows the [Symfony2 Coding Standard](https://github.com/opensky/Symfony2-coding-standard)
* I'm using [gitflow](https://github.com/nvie/gitflow)

How to use
----------
    <?php

    use GitElephant\Repository,
    $repo = new Repository('/path/to/git/repository');

the *Repository* class is the main class where you can find every method you need...

 **Read repository**

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
    ....

**Manage repository**

You could also use GitElephant to manage your git repositories via PHP.

Your web server user (like www-data) needs to have access to the folder of the git repository

    <?php
    // init
    $repo->init();

    // commit
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
    .....

A versioned tree of files
-------------------------

A git repository is a tree structure versioned in time. So if you need to represent a repository in a, let's say, web browser, you will need
a tree representation of the repository, at a given point in history.

**Tree class**

    <?php
    // retrieve the actual *HEAD* tree
    $tree = $repo->getTree();
    // retrieve a tree for a given commit
    $tree = $repo->getTree($repo->getCommit('1ac370d'));
    // retrieve a tree for a given path
    $tree = $repo->getTree('master', 'lib/vendor');

The Tree object implements *ArrayAccess*, *Countable* and *Iterator* interfaces.

You can use it as an array of git objects.

    <?php
    foreach ($tree as $treeObject) {
        echo $treeObject;
    }

A TreeObject instance is a php representation of a node in a git tree

    <?php
    echo $treeObject; // the name of the object (folder, file or link)
    $treeObject->getType(); // a class constanf of TreeObject::TYPE_BLOB, TreeObject::TYPE_TREE and TreeObject::TYPE_LINK
    $treeObject->getSha();
    $treeObject->getSize();
    $treeObject->getName();
    $treeObject->getSize();
    $treeObject->getPath();

You can also pass a tree object to the repository to get its subtree

    <?php
    $subtree = $repo->getTree('master', $treeObject);

Diffs
-----

If you want to display a Diff between two commits the Diff class comes in