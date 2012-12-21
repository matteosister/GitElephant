<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant
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
    GitElephant\Objects\TreeishInterface,
    GitElephant\Command\MainCommand,
    GitElephant\Command\BranchCommand,
    GitElephant\Command\MergeCommand,
    GitElephant\Command\TagCommand,
    GitElephant\Command\LogCommand,
    GitElephant\Command\CloneCommand,
    GitElephant\Command\CatFileCommand;
use GitElephant\Command\LsTreeCommand;
use GitElephant\Command\SubmoduleCommand;

/**
 * Repository
 *
 * Base Class for repository operations
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Repository
{
    /**
     * the repository path
     *
     * @var string
     */
    private $path;

    /**
     * the caller instance
     *
     * @var \GitElephant\Command\Caller
     */
    private $caller;

    /**
     * A general repository name
     *
     * @var string $name the repository name
     */
    private $name;

    /**
     * Class constructor
     *
     * @param string         $repositoryPath the path of the git repository
     * @param GitBinary|null $binary         the GitBinary instance that calls the commands
     * @param string         $name           a repository name
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($repositoryPath, GitBinary $binary = null, $name = null)
    {
        if ($binary == null) {
            $binary = new GitBinary();
        }
        if (!is_dir($repositoryPath)) {
            throw new \InvalidArgumentException(sprintf('the path "%s" is not a repository folder', $repositoryPath));
        }
        $this->path   = $repositoryPath;
        $this->caller = new Caller($binary, $repositoryPath);
        $this->name = $name;
    }

    /**
     * Init the repository
     *
     * @return void
     */
    public function init()
    {
        $this->caller->execute(MainCommand::getInstance()->init());
    }

    /**
     * Stage the working tree content
     *
     * @param string|TreeObject $path the path to store
     *
     * @return void
     */
    public function stage($path = '.')
    {
        $this->caller->execute(MainCommand::getInstance()->add($path));
    }

    /**
     * Move a file/directory
     *
     * @param string|TreeObject $from source path
     * @param string|TreeObject $to   destination path
     */
    public function move($from, $to)
    {
        $this->caller->execute(MainCommand::getInstance()->move($from, $to));
    }

    /**
     * Remove a file/directory
     *
     * @param string|TreeObject $path      the path to remove
     * @param bool              $recursive recurse
     * @param bool              $force     force
     */
    public function remove($path, $recursive = false, $force = false)
    {
        $this->caller->execute(MainCommand::getInstance()->remove($path, $recursive, $force));
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
        $currentBranch = null;
        if ($ref != null) {
            $currentBranch = $this->getMainBranch();
            $this->checkout($ref);
        }
        if ($stageAll) {
            $this->stage();
        }
        $this->caller->execute(MainCommand::getInstance()->commit($message, $stageAll));
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
        $this->caller->execute(MainCommand::getInstance()->status());

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
        $this->caller->execute(BranchCommand::getInstance()->create($name, $startPoint));
    }

    /**
     * Delete a branch by its name
     * This function change the state of the repository on the filesystem
     *
     * @param string $name The branch to delete
     */
    public function deleteBranch($name)
    {
        $this->caller->execute(BranchCommand::getInstance()->delete($name));
    }

    /**
     * An array of TreeBranch objects
     *
     * @return array
     */
    public function getBranches()
    {
        $branches = array();
        $this->caller->execute(BranchCommand::getInstance()->lists());
        foreach ($this->caller->getOutputLines() as $branchString) {
            if ($branchString != '') {
                $branches[] = new TreeBranch($branchString);
            }
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
     * Merge a Branch in the current checked out branch
     *
     * @param Objects\TreeBranch $branch The branch to merge in the current checked out branch
     */
    public function merge(TreeBranch $branch)
    {
        $this->caller->execute(MergeCommand::getInstance()->merge($branch));
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
        $this->caller->execute(TagCommand::getInstance()->create($name, $startPoint, $message));
    }

    /**
     * Delete a tag by it's name or by passing a TreeTag object
     * This function change the state of the repository on the filesystem
     *
     * @param string|TreeTag $tag The tag name or the TreeTag object
     */
    public function deleteTag($tag)
    {
        $this->caller->execute(TagCommand::getInstance()->delete($tag));
    }

    /**
     * add a git submodule to the repository
     *
     * @param string $gitUrl git url of the submodule
     * @param string $path   path to register the submodule to
     */
    public function addSubmodule($gitUrl, $path)
    {
        $this->caller->execute(SubmoduleCommand::getInstance()->add($gitUrl, $path));
    }

    /**
     * Gets an array of TreeTag objects
     *
     * @return array An array of TreeTag objects
     */
    public function getTags()
    {
        $tags = array();
        $this->caller->execute(TagCommand::getInstance()->lists());
        foreach ($this->caller->getOutputLines() as $tagString) {
            if ($tagString != '') {
                $tags[] = new TreeTag($this, trim($tagString));
            }
        }

        return $tags;
    }

    /**
     * Return a tag object
     *
     * @param string $name The tag name
     *
     * @return TreeTag
     */
    public function getTag($name)
    {
        foreach ($this->getTags() as $treeTag) {
            if ($name === $treeTag->getName()) {
                return $treeTag;
            }
        }

        return null;
    }

    /**
     * Try to get a branch or a tag by its name.
     *
     * @param string $name the reference name (a tag name or a branch name)
     *
     * @return \GitElephant\Objects\TreeTag|\GitElephant\Objects\TreeBranch|null
     */
    public function getBranchOrTag($name)
    {
        $branchFinderOutput = $this->caller->execute(BranchCommand::getInstance()->singleInfo($name))->getOutputLines(true);
        $tagFinderOutput = $this->caller->execute(TagCommand::getInstance()->lists())->getOutputLines(true);
        if (count($branchFinderOutput) > 0) {
            return new TreeBranch($branchFinderOutput[0]);
        }
        foreach ($tagFinderOutput as $line) {
            if ($line === $name) {
                return new TreeTag($this, $name);
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
        $commit = new Commit($this, $ref);

        return $commit;
    }

    /**
     * count the commit to arrive to the given treeish
     *
     * @param string $start
     *
     * @return int|void
     */
    public function countCommits($start = 'HEAD')
    {
        $commit = new Commit($this, $start);

        return $commit->count();
    }

    /**
     * Get a log for a ref
     *
     * @param string|TreeishInterface $ref    the treeish to check
     * @param string|TreeObject       $path   the physical path to the tree relative to the repository root
     * @param int|null                $limit  limit to n entries
     * @param int|null                $offset skip n entries
     *
     * @return \GitElephant\Objects\Log
     */
    public function getLog($ref = 'HEAD', $path = null, $limit = 10, $offset = null)
    {
        return new Log($this, $ref, $path, $limit, $offset);
    }

    /**
     * Get a log for an object
     *
     * @param \GitElephant\Objects\TreeObject             $obj    The TreeObject instance
     * @param null|string|\GitElephant\Objects\TreeBranch $branch The branch to read from
     * @param int                                         $limit  Limit to n entries
     * @param int|null                                    $offset Skip n entries
     *
     * @return \GitElephant\Objects\Log
     */
    public function getTreeObjectLog(TreeObject $obj, $branch = null, $limit = 1, $offset = null)
    {
        $command = LogCommand::getInstance()->showObjectLog($obj, $branch, $limit, $offset);

        return Log::createFromOutputLines($this, $this->caller->execute($command)->getOutputLines());
    }

    /**
     * Checkout a branch
     * This function change the state of the repository on the filesystem
     *
     * @param string|TreeishInterface $ref the ref to checkout
     */
    public function checkout($ref)
    {
        $this->caller->execute(MainCommand::getInstance()->checkout($ref));
    }

    /**
     * Retrieve an instance of Tree
     * Tree Object is Countable, Iterable and has ArrayAccess for easy manipulation
     *
     * @param string|TreeishInterface $ref  the treeish to check
     * @param string|TreeObject       $path TreeObject or null for root
     *
     * @return Objects\Tree
     */
    public function getTree($ref = 'HEAD', $path = null)
    {
        if (is_string($path) && '' !== $path) {
            $outputLines = $this->getCaller()->execute(LsTreeCommand::getInstance()->tree($ref, $path))->getOutputLines(true);
            $path = TreeObject::createFromOutputLine($outputLines[0]);
        }

        return new Tree($this, $ref, $path);
    }

    /**
     * Get a Diff object for a commit with its parent, by default the diff is between the current head and its parent
     *
     * @param \GitElephant\Objects\Commit|string      $commit1 A TreeishInterface instance
     * @param \GitElephant\Objects\Commit|string|null $commit2 A TreeishInterface instance
     * @param null|string|TreeObject                  $path    The path to get the diff for or a TreeObject instance
     *
     * @return Objects\Diff\Diff
     */
    public function getDiff($commit1 = null, $commit2 = null, $path = null)
    {
        return Diff::create($this, $commit1, $commit2, $path);
    }

    /**
     * Clone a repository
     *
     * @param string $url the repository url (i.e. git://github.com/matteosister/GitElephant.git)
     */
    public function cloneFrom($url)
    {
        $this->caller->execute(CloneCommand::getInstance()->cloneUrl($url));
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
     * @param \GitElephant\Objects\TreeObject              $obj     The TreeObject of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function outputContent(TreeObject $obj, $treeish)
    {
        $command = CatFileCommand::getInstance()->content($obj, $treeish);

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

    /**
     * Get the repository name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the repository name
     *
     * @param string $name the repository name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Caller setter
     *
     * @param \GitElephant\Command\Caller $caller the caller variable
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     * Caller getter
     *
     * @return \GitElephant\Command\Caller
     */
    public function getCaller()
    {
        return $this->caller;
    }
}
