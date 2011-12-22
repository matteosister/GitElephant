<?php

/**
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
use GitElephant\Objects\Tree,
GitElephant\Objects\TreeBranch,
GitElephant\Objects\TreeTag,
GitElephant\Objects\TreeObject,
GitElephant\Objects\Diff\Diff,
GitElephant\Objects\Commit,
GitElephant\Objects\Log,
GitElephant\Objects\TreeishInterface;
use GitElephant\Command\MainCommand,
GitElephant\Command\BranchCommand,
GitElephant\Command\TagCommand,
GitElephant\Command\LsTreeCommand,
GitElephant\Command\DiffCommand,
GitElephant\Command\ShowCommand,
GitElephant\Command\LogCommand,
GitElephant\Command\RevListCommand,
GitElephant\Command\CatFileCommand;
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
    private $diffCommand;
    private $showCommand;
    private $logCommand;
    private $revListCommand;
    private $catFileCommand;

    /**
     * Class constructor
     *
     * @param string         $repositoryPath the path of the git repository
     * @param GitBinary|null $binary         the GitBinary instance that calls the commands
     */
    public function __construct($repositoryPath, GitBinary $binary = null)
    {
        if ($binary == null) {
            $binary = new GitBinary();
        }
        if (!is_dir($repositoryPath)) {
            throw new \InvalidArgumentException(sprintf('the path "%s" is not a repository folder', $repositoryPath));
        }
        $this->path   = $repositoryPath;
        $this->caller = new Caller($binary, $repositoryPath);

        // command objects
        $this->mainCommand    = new MainCommand();
        $this->branchCommand  = new BranchCommand();
        $this->tagCommand     = new TagCommand();
        $this->lsTreeCommand  = new LsTreeCommand();
        $this->diffCommand    = new DiffCommand();
        $this->showCommand    = new ShowCommand();
        $this->logCommand     = new LogCommand();
        $this->revListCommand = new RevListCommand();
        $this->catFileCommand = new CatFileCommand();
    }

    /**
     * Init the repository
     *
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
     *
     * @return void
     */
    public function stage($path = '.')
    {
        $this->caller->execute($this->mainCommand->add($path));
    }

    /**
     * Commit content to the repository, eventually staging all unstaged content
     *
     * @param string      $message  the commit message
     * @param bool        $stageAll whether to stage on not everything before commit
     * @param string|null $ref      the reference to commit to (checkout -> commit -> checkout previous)
     */
    public function commit($message, $stageAll = false, $ref = null)
    {
        if ($ref != null) {
            $currentBranch = $this->getMainBranch();
            $this->checkout($ref);
        }
        if ($stageAll) {
            $this->stage();
        }
        $this->caller->execute($this->mainCommand->commit($message));
        if ($ref != null) {
            $this->checkout($currentBranch);
        }
    }

    /**
     * Get the repository status
     *
     * @return array output lines
     */
    public function getStatus()
    {
        $this->caller->execute($this->mainCommand->status());
        return array_map('trim', $this->caller->getOutputLines());
    }

    /**
     * Create a new branch
     *
     * @param string $name       the new branch name
     * @param null   $startPoint the reference to create the branch from
     */
    public function createBranch($name, $startPoint = null)
    {
        $this->caller->execute($this->branchCommand->create($name, $startPoint));
    }

    /**
     * Delete a branch by its name
     * This function change the state of the repository on the filesystem
     *
     * @param string $name The branch to delete
     */
    public function deleteBranch($name)
    {
        $this->caller->execute($this->branchCommand->delete($name));
    }

    /**
     * An array of TreeBranch objects
     *
     * @return array
     */
    public function getBranches()
    {
        $branches = array();
        $this->caller->execute($this->branchCommand->lists());
        foreach ($this->caller->getOutputLines() as $branchString) {
            $branches[] = new TreeBranch($branchString);
        }
        usort($branches, array($this, 'sortBranches'));
        return $branches;
    }

    /**
     * Return the actually checked out branch
     *
     * @return Objects\TreeBranch
     */
    public function getMainBranch()
    {
        $filtered = array_filter(
            $this->getBranches(), function(TreeBranch $branch)
            {
                return $branch->getCurrent();
            }
        );
        sort($filtered);
        return $filtered[0];
    }

    /**
     * Retrieve a TreeBranch object by a branch name
     *
     * @param string $name The branch name
     *
     * @return null|TreeBranch
     */
    public function getBranch($name)
    {
        foreach ($this->getBranches() as $treeBranch) {
            if ($treeBranch->getName() == $name) {
                return $treeBranch;
            }
        }
        return null;
    }

    /**
     * Create a new tag
     * This function change the state of the repository on the filesystem
     *
     * @param string $name       The new tag name
     * @param null   $startPoint The reference to create the tag from
     * @param null   $message    the tag message
     */
    public function createTag($name, $startPoint = null, $message = null)
    {
        $this->caller->execute($this->tagCommand->create($name, $startPoint, $message));
    }

    /**
     * Delete a tag by it's name or by passing a TreeTag object
     * This function change the state of the repository on the filesystem
     *
     * @param string|TreeTag $tag The tag name or the TreeTag object
     */
    public function deleteTag($tag)
    {
        $this->caller->execute($this->tagCommand->delete($tag));
    }

    /**
     * Gets an array of TreeTag objects
     *
     * @return array An array of TreeTag objects
     */
    public function getTags()
    {
        $tags = array();
        $this->caller->execute($this->tagCommand->lists());
        foreach ($this->caller->getOutputLines() as $tagString) {
            $tag = new TreeTag($tagString);
            $outputLines = $this->caller->execute($this->revListCommand->getTagCommit($tag))->getOutputLines();
            $tag->setSha($outputLines[0]);
            $tags[] = $tag;
        }
        return $tags;
    }

    /**
     * Return a tag object
     *
     * @param string $name The tag name
     *
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
     * Return a Commit object
     *
     * @param string $ref The commit reference
     *
     * @return Objects\Commit
     */
    public function getCommit($ref = 'HEAD')
    {
        $command = $this->showCommand->showCommit($ref);
        return new Commit($this->caller->execute($command)->getOutputLines());
    }

    /**
     * Get a log object
     *
     * @param TreeObject $obj    The TreeObject instance
     * @param null       $branch The branch to read from
     *
     * @return Objects\Log
     */
    public function getLog($obj, $branch = null)
    {
        $command = $this->logCommand->showLog($obj, $branch);
        return new Log($this->caller->execute($command)->getOutputLines());
    }

    /**
     * Checkout a branch
     * This function change the state of the repository on the filesystem
     *
     * @param string|TreeishInterface $ref the ref to checkout
     */
    public function checkout($ref)
    {
        $this->caller->execute($this->mainCommand->checkout($ref));
    }

    /**
     * Retrieve an instance of Tree
     * Tree Object is Countable, Iterable and has ArrayAccess for easy manipulation
     *
     * @param string|TreeishInterface $ref  the treeish to check
     * @param string|TreeObject       $path the physical path to the tree relative to the repository root
     *
     * @return Objects\Tree
     */
    public function getTree($ref = 'HEAD', $path = '')
    {
        $outputLines = $this->caller->execute($this->lsTreeCommand->tree($ref))->getOutputLines();
        return new Tree($outputLines, $path);
    }

    /**
     * Get a Diff object for a commit with its parent
     *
     * @param Objects\TreeishInterface      $treeish1 A TreeishInterface instance
     * @param Objects\TreeishInterface|null $treeish2 A TreeishInterface instance
     * @param null|string|TreeObject        $path     The path to get the diff for or a TreeObject instance
     *
     * @return Objects\Diff\Diff
     */
    public function getDiff(TreeishInterface $treeish1, TreeishInterface $treeish2 = null, $path = null)
    {
        $command     = $this->diffCommand->diff($treeish1, $treeish2, $path);
        $outputLines = $this->caller->execute($command)->getOutputLines();
        return new Diff($outputLines);
    }

    /**
     * Order the branches list
     *
     * @param Objects\TreeBranch $a first branch
     * @param Objects\TreeBranch $b second branch
     *
     * @return int
     */
    private function sortBranches(TreeBranch $a, TreeBranch $b)
    {
        if ($a->getName() == 'master') {
            return -1;
        } else {
            if ($b->getName() == 'master') {
                return 1;
            } else {
                return 0;
            }
        }
    }

    /**
     * output a node content
     *
     * @param \GitElephant\Objects\TreeObject       $obj     The TreeObject of type BLOB
     * @param \GitElephant\Objects\TreeishInterface $treeish A treeish object
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function outputContent(TreeObject $obj, TreeishInterface $treeish)
    {
        $command = $this->catFileCommand->content($obj, $treeish);
        return $this->caller->execute($command)->getOutputLines();
    }

    /**
     * Get the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
