<?php

/*
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant;

use GitElephant\GitBinary;
use GitElephant\Command\Caller;
use GitElephant\Objects\Tree;
use GitElephant\Objects\Node;
use GitElephant\Command\Main;
use GitElephant\Command\Branch;

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

    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Init the repository
     * @return void
     */
    public function init()
    {
        $main = new Main();
        $this->caller->execute($main->init());
    }

    /**
     * stage all working tree content
     * @return void
     */
    
    public function stageAll()
    {
        $main = new Main();
        $this->caller->execute($main->add());
    }

    /**
     * commit the staged contents
     * @param $message
     * @return void
     */
    public function commit($message)
    {
        $main = new Main();
        $this->caller->execute($main->commit($message));
    }

    public function createBranch($name)
    {

    }

    public function getStatus($oneLine = false)
    {
        $main = new Main();
        $this->caller->execute($main->status());
        return $oneLine ? $this->caller->getOutput() : $this->caller->getOutputLines();
    }

    /**
     * @param string $what the name of the tree, HEAD by default
     * @return GitElephant\Command\Tree\Tree
     */
    public function getTree($what = 'HEAD')
    {
        $tree = new Tree($what);
        $tree->lsTree($what);
        $this->caller->execute($tree->getCommand());
        foreach($this->caller->getOutputLines() as $nodeString) {
            $tree[] = new Node($nodeString);
        }
        return $tree;
    }

    public function getBranches()
    {
        
    }
}
