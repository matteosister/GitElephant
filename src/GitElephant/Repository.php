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
use GitElephant\Objects\TreeNode;
use GitElephant\Objects\TreeBranch;
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
        if (!is_dir($repository_path)) {
            throw new \InvalidArgumentException(sprintf('the path "%s" is not a repository folder', $repository_path));
        }
        $this->path = $repository_path;
        $this->caller = new Caller($binary, $repository_path);
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
     * stage working tree content
     * @return void
     */
    public function stage($path = '.')
    {
        $main = new Main();
        $this->caller->execute($main->add($path));
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

    public function getStatus($oneLine = false)
    {
        $main = new Main();
        $this->caller->execute($main->status());
        return $oneLine ? $this->caller->getOutput() : $this->caller->getOutputLines();
    }

    public function createBranch($name, $startPoint = null)
    {
        $branch = new Branch();
        $this->caller->execute($branch->create($name, $startPoint));
    }

    public function deleteBranch($name)
    {
        $branch = new Branch();
        $this->caller->execute($branch->delete($name));
    }

    public function getBranches()
    {
        $branches = array();
        $branch = new Branch();
        $this->caller->execute($branch->lists());
        foreach($this->caller->getOutputLines() as $branchString) {
            $branches[] = new TreeBranch($branchString);
        }
        return $branches;
    }

    /**
     * @return \GitElephant\Objects\TreeBranch
     */
    public function getMainBranch()
    {
        $filtered = array_filter($this->getBranches(), function($var) {
            return $var->getCurrent();
        });
        return $filtered[0];
    }

    /**
     * @param string $what the name of the tree, HEAD by default
     * @return GitElephant\Command\Tree\Tree
     */
    public function getTree($what = 'HEAD')
    {
        $tree = new Tree($what);
        $this->caller->execute($tree->lsTree($what));
        foreach($this->caller->getOutputLines() as $nodeString) {
            $tree[] = new TreeNode($nodeString);
        }
        return $tree;
    }
}
