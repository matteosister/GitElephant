# GitElephant ![Travis build status](https://secure.travis-ci.org/matteosister/GitElephant.png)#

GitElephant is an abstraction layer to manage your git repositories with php

It's not stable yet...I created a small [todo list](https://github.com/matteosister/GitElephant/blob/develop/ROADMAP.md) for the things that I would like to implement. If you want give a hand you are more than welcome!

Watch a [simple live example](http://gitelephant.cypresslab.net) of what you can do with GitElephant, Symfony2 and a git repository...

[Download the demo bundle code](https://github.com/matteosister/GitElephantDemoBundle) used in the live example

Requirements
------------

- php >= 5.3
- *nix system with git installed

I work on an ubuntu box, but the lib should work well with every unix system.
I don't have a windows installation to test...if someone want to help...

Installation
------------

**composer**

To install GitElephant with composer you simply need to create a *composer.json* in your project root and add:

``` json
{
    "require": {
        "cypresslab/gitelephant": "0.9.*"
    }
}
```

Then run

``` bash
$ curl -s https://getcomposer.org/installer | php
$ composer install
```

You have now GitElephant installed in *vendor/cypresslab/gitelephant*

And an handy autoload file to include in you project in *vendor/autoload.php*

**pear**

*I will remove pear support soon. Please switch to composer!*
Add the cypresslab channel

``` bash
$ pear channel-discover pear.cypresslab.net
```

And install the package. *By now GitElephant is in alpha state. So remember the -alpha in the library name*

``` bash
$ pear install cypresslab/GitElephant-alpha
```

On [Cypresslab pear channel homepage](http://pear.cypresslab.net/) you can find other useful information

How to use
----------

``` php
<?php

use GitElephant\Repository;
$repo = new Repository('/path/to/git/repository');
// or the factory method
$repo = Repository::open('/path/to/git/repository');
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
// or directly create a commit object
$commit = new Commit($repo, '1ac370d');
$commit = new Commit($repo, '1ac370d'); // head commit

// count commits
$repo->countCommits('1ac370d'); // number of commits to arrive at 1ac370d
// commit is countable, so, with a commit object, you can do
$commit->count();
// as well as
count($commit);

// Log contains a collection of commit objects
// syntax: getLog(<tree-ish>, path = null, limit = 15, offset = null)
$log = $repo->getLog();
$log = $repo->getLog('master', null, 5);
$log = $repo->getLog('v0.1', null, 5, 10);
// or directly create a log object
$log = new Log($repo);
$log = new Log($repo, 'v0.1', null, 5, 10);

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
$repo->checkoutAllRemoteBranches('origin'); // checkout all the branches from the remote repository

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
// generate a tree
$tree = new Tree($repo);
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
$treeObject->getType(); // one class constant of TreeObject::TYPE_BLOB, TreeObject::TYPE_TREE and TreeObject::TYPE_LINK
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
// alternatively you could directly use the sha of the commit
$diff = $repo->getDiff('1ac370d', '8fb7281');
// manually generate a Diff object
$diff = Diff::create($repo); // defaults to the last commit
// or as explained before
$diff = Diff::create($repo, '1ac370d', '8fb7281');
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

A DiffObject is a class that implements *ArrayAccess*, *Countable* and *Iterator* interfaces. It represent a file, folder or submodule changed in the Diff.

Every DiffObject can have multiple chunks of changes. For example:

```
    added 3 lines at line 20
    deleted 4 lines at line 560
```

You can iterate over DiffObject to get DiffChunks. DiffChunks are the last steps of the Diff process, they are a collection of DiffChunkLine Objects

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

Testing
-------

The library is fully tested with PHPUnit.

Go to the base library folder and install the dev dependencies with composer, and then run the phpunitt test suite

``` bash
$ composer --dev install
$ ./vendor/bin/phpunit # phpunit test suite
```

If you want to run the test suite you should have all the dependencies loaded.

Symfony2
--------

There is a [GitElephantBundle](https://github.com/matteosister/GitElephantBundle) to use this library inside a Symfony2 project.

Dependencies
------------

- [symfony/process](https://packagist.org/packages/symfony/process)
- [symfony/filesystem](https://packagist.org/packages/symfony/filesystem)

*for tests*

- [PHPUnit](https://github.com/sebastianbergmann/phpunit)

Code style
----------

* GitElephant follows the [Symfony2 Coding Standard](https://github.com/opensky/Symfony2-coding-standard)
* I'm using [gitflow](https://github.com/nvie/gitflow)

Want to contribute?
-------------------

*You are my new hero!*

Just remember:

* Symfony2 coding standard
* test everything you develop with phpunit
* if you don't use gitflow, just remember to branch from "develop" and send your PR there. **Please do not send pull requests on the master branch**.

Thanks
------

Many thanks to Linus and all those who have worked/contributed in any way to git.
Because **it's awesome!!!** I can't imagine being a developer without it.
