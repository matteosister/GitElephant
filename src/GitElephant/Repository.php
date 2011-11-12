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
use GitElephant\Objects\NestedTree;
use GitElephant\Objects\TreeBranch;
use GitElephant\Command\MainCommand;
use GitElephant\Command\BranchCommand;
use GitElephant\Command\LsTreeCommand;

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

    private $mainCommand;
    private $branchCommand;
    private $lsTreeCommand;

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

        // command objects
        $this->mainCommand = new MainCommand();
        $this->branchCommand = new BranchCommand();
        $this->lsTreeCommand = new LsTreeCommand();
    }
    
    /**
     * Init the repository
     * @return void
     */
    public function init()
    {
        $this->caller->execute($this->mainCommand->init());
    }

    /**
     * stage working tree content
     * @return void
     */
    public function stage($path = '.')
    {
        $this->caller->execute($this->mainCommand->add($path));
    }

    /**
     * Commit
     *
     * @param $message
     * @param whether to stage or not content before the commit
     * @return void
     */
    public function commit($message, $stage = false)
    {
        if ($stage) $this->stage();
        $this->caller->execute($this->mainCommand->commit($message));
    }

    public function getStatus($oneLine = false)
    {
        $this->caller->execute($this->mainCommand->status());
        return $oneLine ? $this->caller->getOutput() : $this->caller->getOutputLines();
    }

    public function createBranch($name, $startPoint = null)
    {
        $this->caller->execute($this->branchCommand->create($name, $startPoint));
    }

    public function deleteBranch($name)
    {
        $this->caller->execute($this->branchCommand->delete($name));
    }

    public function getBranches()
    {
        $branches = array();
        $this->caller->execute($this->branchCommand->lists());
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
     * @param string|null $what the name of the tree, root by default
     * @return GitElephant\Command\Tree\Tree
     */
    public function getTree($path = '', $ref = 'HEAD')
    {
        $command = $this->lsTreeCommand->callLsTree($ref);
        $tree = new Tree($this->caller->execute($command, true, $this->path.'/'.$path)->getOutputLines());
        return $tree;
    }

    public function getNestedTree($ref = 'HEAD')
    {
        $tree = new NestedTree($this->caller);
        var_dump($tree->getFilesIn('22a498449649ed1b02c557cdf4c86b762fbf9b85'));
    }
}
