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
use GitElephant\Objects\TreeBranch;
use GitElephant\Objects\TreeTag;
use GitElephant\Command\MainCommand;
use GitElephant\Command\BranchCommand;
use GitElephant\Command\TagCommand;
use GitElephant\Command\LsTreeCommand;
use GitElephant\Utilities;

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
    private $tagCommand;
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
        $this->tagCommand = new TagCommand();
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
     * Stage the working tree content
     * 
     * @param string $path the path to store
     * @return void
     */
    public function stage($path = '.')
    {
        $this->caller->execute($this->mainCommand->add($path));
    }

    /**
     * Commit content to the repository, eventually staging all unstaged content
     *
     * @param $message The commit message
     * @param bool $stage whether stage or not content before the commit
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
        usort($branches, array($this, 'sortBranches'));
        return $branches;
    }

    /**
     * @return GitElephant\Objects\TreeBranch
     */
    public function getMainBranch()
    {
        $filtered = array_filter($this->getBranches(), function(TreeBranch $branch) {
            return $branch->getCurrent();
        });
        sort($filtered);
        return $filtered[0];
    }

    public function getBranch($name)
    {
        foreach ($this->getBranches() as $treeBranch) {
            if ($treeBranch->getName() == $name) {
                return $treeBranch;
            }
        }
        return null;
    }

    public function createTag($name, $startPoint = null, $message)
    {
        $this->caller->execute($this->tagCommand->create($name, $startPoint, $message));
    }

    public function deleteTag($name)
    {
        $this->caller->execute($this->tagCommand->delete($name));
    }

    public function getTags()
    {
        $tags = array();
        $this->caller->execute($this->tagCommand->lists());
        foreach($this->caller->getOutputLines() as $tagString) {
            $tags[] = new TreeTag($tagString);
        }
        return $tags;
    }

    /**
     * @return GitElephant\Objects\TreeTag
     */
    public function getTag($name)
    {
        foreach ($this->getTags() as $treeTag) {
            if ($treeTag->getName() == $name) {
                return $treeTag;
            }
        }
        return null;
    }

    /**
     * @param string $path the physical path to the tree relative to the repository root
     * @param string|null $ref the treeish to check
     * @return GitElephant\Objects\Tree
     */
    public function getTree($ref, $path = '')
    {
        if (is_string($ref)) {
            $command = $this->lsTreeCommand->tree($ref);
        }
        if ($ref instanceof TreeBranch) {
            $command = $this->lsTreeCommand->tree($ref->getFullRef());
        }
        if ($ref instanceof TreeTag) {
            $command = $this->lsTreeCommand->tree($ref->getFullRef());
        }
        return new Tree($this->caller->execute($command)->getOutputLines(), $path);
    }


    private function sortBranches(TreeBranch $a, TreeBranch $b) {
        if ($a->getName() == 'master') {
            return -1;
        } else if ($b->getName() == 'master') {
            return 1;
        } else {
            return 0;
        }
    }
}
