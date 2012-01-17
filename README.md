# GitElephant ![Still maintained](http://stillmaintained.com/matteosister/GitElephant.png)&nbsp;![Travis build status](https://secure.travis-ci.org/matteosister/GitElephant.png)#

GitElephant is an abstraction layer to manage your git repositories with php

Watch a [simple live example](http://gitelephant.cypresslab.net/) of what you can do with GitElephant, [GitElephantBundle](https://github.com/matteosister/GitElephantBundle), Symfony2 and a git repository...

[Download the demo bundle code](https://github.com/matteosister/GitElephantDemoBundle) used in the live example

Requirements
------------

- php >= 5.3
- *nix system with git installed

I work on an ubuntu box, but the lib should work well with every unix system. I don't have a windows installation to test...if someone want to help...

Installation
------------

**composer**

To install GitElephant with composer you simply need to create a *composer.json* in your project root and add:

``` json
{
    "require": {
        "cypresslab/gitelephant": ">=0.6.0"
    }
}
```

Then run

``` bash
$ wget -nc http://getcomposer.org/composer.phar
$ php composer.phar install
```

You have now GitElephant installed in *vendor/cypresslab/gitelephant*

And an handy autoload file to include in you project in *vendor/.composer/autoload.php*

**pear**

Add the Cypresslab channel

``` bash
$ pear channel-discover pear.cypresslab.net
```

And install the package. *By now GitElephant is in alpha state. So remember the -alpha in the library name*

``` bash
$ pear install cypresslab/GitElephant-alpha
```

On [Cypresslab pear channel homepage](http://pear.cypresslab.net/) you can find other useful information

Testing
-------

The library is fully tested with PHPUnit for unit tests, and Behat for BDD. To run tests you need these (awesome) libraries installed on your system.

Go to the base library folder and run the test suites

``` bash
$ phpunit # phpunit test suite
$ behat # behat test suite
```

If you want to run the test suite you should have all the dependencies loaded.

From the root of the library you have to do

``` bash
$ wget -nc http://getcomposer.org/composer.phar
$ php composer.phar install
```

this will fetch all the needed dependencies inside the vendor dir

Code style
----------

* GitElephant follows the [Symfony2 Coding Standard](https://github.com/opensky/Symfony2-coding-standard)
* I'm using [gitflow](https://github.com/nvie/gitflow)

How to use
----------

``` php
<?php

use GitElephant\Repository;
$repo = new Repository('/path/to/git/repository');
```

the *Repository* class is the main class where you can find every method you need...

 **Read repository**

``` php
<?php
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
$repo->getCommit('1ac370d'); // sha (follow [git standards](http://book.git-scm.com/4_git_treeishes.html) to format the sha)

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
```

**Manage repository**

You could also use GitElephant to manage your git repositories via PHP.

Your web server user (like www-data) needs to have access to the folder of the git repository

``` php
<?php
$repo->init(); // init
$repo->cloneFrom("git://github.com/matteosister/GitElephant.git"); // clone

// stage changes
$repo->stage('file1.php');
$repo->stage(); // stage all

// commit
$repo->commit('my first commit');
$repo->commit('my first commit', true); // commit and stage every pending changes in the working tree

// checkout
$repo->checkout($this->getCommit('v1.0')); // checkout a tag
$repo->checkout('master'); // checkout master

// manage branches
$repo->createBranch('develop'); // create a develop branch from current checked out branch
$repo->createBranch('develop', 'master'); // create a develop branch from master
$repo->deleteBranch('develop'); // delete the develop branch

// manage tags
// create  a tag named v1.0 from master with the given tag message
$repo->createTag('v1.0', 'master', 'my first release!');
// create  a tag named v1.0 from the current checked out branch with the given tag message
$repo->createTag('v1.0', null, 'my first release!');
// create a tag from a Commit object
$repo->createTag($repo->getCommit());
```

A versioned tree of files
-------------------------

A git repository is a tree structure versioned in time. So if you need to represent a repository in a, let's say, web browser, you will need
a tree representation of the repository, at a given point in history.

**Tree class**

``` php
<?php
$tree = $repo->getTree(); // retrieve the actual *HEAD* tree
$tree = $repo->getTree($repo->getCommit('1ac370d')); // retrieve a tree for a given commit
$tree = $repo->getTree('master', 'lib/vendor'); // retrieve a tree for a given path
```

The Tree class implements *ArrayAccess*, *Countable* and *Iterator* interfaces.

You can use it as an array of git objects.

``` php
<?php
foreach ($tree as $treeObject) {
    echo $treeObject;
}
```

A TreeObject instance is a php representation of a node in a git tree

``` php
<?php
echo $treeObject; // the name of the object (folder, file or link)
$treeObject->getType(); // a class constanf of TreeObject::TYPE_BLOB, TreeObject::TYPE_TREE and TreeObject::TYPE_LINK
$treeObject->getSha();
$treeObject->getSize();
$treeObject->getName();
$treeObject->getSize();
$treeObject->getPath();
```

You can also pass a tree object to the repository to get its subtree

``` php
<?php
$subtree = $repo->getTree('master', $treeObject);
```

Diffs
-----

If you want to check a Diff between two commits the Diff class comes in

``` php
<?php
// get the diff between the given commit and it parent
$diff = $repo->getDiff($repo->getCommit());
// get the diff between two commits
$diff = $repo->getDiff($repo->getCommit('1ac370d'), $repo->getCommit('8fb7281'));
// same as before for a given path
$diff = $repo->getDiff($repo->getCommit('1ac370d'), $repo->getCommit('8fb7281'), 'lib/vendor');
// or even pass a TreeObject
$diff = $repo->getDiff($repo->getCommit('1ac370d'), $repo->getCommit('8fb7281'), $treeObject);
```

The Diff class implements *ArrayAccess*, *Countable* and *Iterator* interfaces

You can iterate over DiffObject

``` php
<?php
foreach ($diff as $diffObject) {
    // mode is a constant of the DiffObject class
    // DiffObject::MODE_INDEX an index change
    // DiffObject::MODE_MODE a mode change
    // DiffObject::MODE_NEW_FILE a new file change
    // DiffObject::MODE_DELETED_FILE a deleted file change
    echo $diffObject->getMode();
}
```

A DiffObject is a class that implements *ArrayAccess*, *Countable* and *Iterator* interfaces. It represent a file, folder or submodule changed in the diff
Every DiffObject can have multiple chunks of changes. For example "added 3 lines at line 20" and "modified 4 lines at line 560"
So you can iterate over DiffObject to get DiffChunks. DiffChunks are the last steps of the diff iteration.
They are a collection of DiffChunkLine Objects

``` php
<?php
foreach ($diffObject as $diffChunk) {
    if (count($diffChunk) > 0) {
        echo "change detected from line ".$diffChunk->getDestStartLine()." to ".$diffChunk->getDestEndLine();
        foreach ($diffChunk as $diffChunkLine) {
            echo $diffChunkLine; // output the line content
        }
    }
}
```

This is just an example of what the Diff class can do. Run the diff behat test suite for other nice things

``` bash
$ behat features/diff.feature
```

Symfony2
--------

There is a [GitElephantBundle](https://github.com/matteosister/GitElephantBundle) to use this library inside a Symfony2 project.

Want to contribute?
-------------------

*You are my new hero!*

Just remember:

* Symfony2 coding standard
* test everything you develop with phpunit AND behat.
* if you don't use gitflow, just remember to develop on a branch (not master) and send a pull request

Thanks
------

Many thanks to Linus and all those who have worked/contributed in any way to git.
Because **it's awesome!!!** I can't imagine being a developer without it.
