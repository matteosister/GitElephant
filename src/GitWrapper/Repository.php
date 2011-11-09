<?php

/*
 * This file is part of the GitWrapper package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitWrapper;

use GitWrapper\GitBinary;
use GitWrapper\Command\Caller;
use GitWrapper\Command\Tree\Tree;
use GitWrapper\Command\Tree\Node;
use GitWrapper\Command\Main;

/**
 * Repository
 *
 * Base Class for repository operations
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Repository
{
    private $path;
    private $caller;

    public function __construct($repository_path, GitBinary $binary = null)
    {
        if ($binary == null) {
            $binary = new GitBinary();
        }
        $this->path = $repository_path;
        $this->caller = new Caller($binary, $repository_path);
    }

    public function init()
    {
        $main = new Main();
        $this->caller->execute($main->init());
    }

    /**
     * @param string $what the name of the tree, HEAD by default
     * @return GitWrapper\Command\Tree\Tree
     */
    public function getTree($what = 'HEAD')
    {
        $tree = new Tree();
        $tree->lsTree($what);
        $this->caller->execute($tree->getCommand());
        foreach($this->caller->getOutputLines() as $nodeString) {
            $tree[] = new Node($nodeString);
        }
        return $tree;
    }
}
