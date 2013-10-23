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
use GitElephant\Objects\Tree;
use GitElephant\Objects\TreeBranch;
use GitElephant\Objects\TreeTag;
use GitElephant\Objects\TreeObject;
use GitElephant\Objects\Diff\Diff;
use GitElephant\Objects\Commit;
use GitElephant\Objects\Log;
use GitElephant\Objects\LogRange;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Command\MainCommand;
use GitElephant\Command\BranchCommand;
use GitElephant\Command\MergeCommand;
use GitElephant\Command\TagCommand;
use GitElephant\Command\LogCommand;
use GitElephant\Command\CloneCommand;
use GitElephant\Command\CatFileCommand;
use GitElephant\Command\LsTreeCommand;
use GitElephant\Command\SubmoduleCommand;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Repository
 *
 * Base Class for repository operations
 *
 * @author Matteo Giachino <matteog@gmail.com>
 * @author Dhaval Patel <tech.dhaval@gmail.com>
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
     * create a repository from a remote git url, or a local filesystem
     * and save it in a temp folder
     *
     * @param string|Repository $git            the git remote url, or the filesystem path
     * @param null              $repositoryPath path
     * @param GitBinary         $binary         binary
     * @param null              $name           repository name
     *
     * @return Repository
     */
    public static function createFromRemote($git, $repositoryPath = null, GitBinary $binary = null, $name = null)
    {
        if (null === $repositoryPath) {
            $tempDir = realpath(sys_get_temp_dir());
            $repositoryPath = sprintf('%s%s%s', $tempDir, DIRECTORY_SEPARATOR, sha1(uniqid()));
            $fs = new Filesystem();
            $fs->mkdir($repositoryPath);
        }
        $repository = new Repository($repositoryPath, $binary, $name);
        $repository->cloneFrom($git, $repositoryPath);
        $repository->checkoutAllRemoteBranches();

        return $repository;
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
     * @param bool $namesOnly return an array of branch names as a string
     * @param bool $all       lists also remote branches
     *
     * @return array
     */
    public function getBranches($namesOnly = false, $all = false)
    {
        $branches = array();
        if ($namesOnly) {
            $outputLines = $this->caller->execute(BranchCommand::getInstance()->lists($all, true))->getOutputLines(true);
            $branches = array_map(function($v) {
                return ltrim($v, '* ');
            }, $outputLines);
            $sortMethod = 'sortBranchesByName';
        } else {
            $outputLines = $this->caller->execute(BranchCommand::getInstance()->lists($all))->getOutputLines(true);
            foreach ($outputLines as $branchLine) {
                $branches[] = TreeBranch::createFromOutputLine($this, $branchLine);
            }
            $sortMethod = 'sortBranches';
        }
        usort($branches, array($this, $sortMethod));

        return $branches;
    }

    /**
     * Return the actually checked out branch
     *
     * @return Objects\TreeBranch
     */
    public function getMainBranch()
    {
        $filtered = array_filter($this->getBranches(), function(TreeBranch $branch) {
            return $branch->getCurrent();
        });
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
     * Checkout all branches from the remote and make them local
     *
     * @param string $remote remote to fetch from
     *
     * @return void
     */
    public function checkoutAllRemoteBranches($remote = 'origin')
    {
        $actualBranch = $this->getMainBranch();
        $actualBranches = $this->getBranches(true, false);
        $allBranches = $this->getBranches(true, true);
        $realBranches = array_filter($allBranches, function($branch) use ($actualBranches) {
            return !in_array($branch, $actualBranches)
                && preg_match('/^remotes(.+)$/', $branch)
                && !preg_match('/^(.+)(HEAD)(.*?)$/', $branch);
        });
        foreach ($realBranches as $realBranch) {
            $this->checkout(str_replace(sprintf('remotes/%s/', $remote), '', $realBranch));
        }
        $this->checkout($actualBranch);
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
    public function addSubmodule($gitUrl, $path = null)
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
        if (in_array($name, $this->getBranches(true))) {
            return new TreeBranch($this, $name);
        }
        $tagFinderOutput = $this->caller->execute(TagCommand::getInstance()->lists())->getOutputLines(true);
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
     * @param string|TreeishInterface $ref         the treeish to check
     * @param string|TreeObject       $path        the physical path to the tree relative to the repository root
     * @param int|null                $limit       limit to n entries
     * @param int|null                $offset      skip n entries
     * @param boolean|false           $firstParent skip commits brought in to branch by a merge
     *
     * @return \GitElephant\Objects\Log
     */
    public function getLog($ref = 'HEAD', $path = null, $limit = 10, $offset = null, $firstParent = false)
    {
        return new Log($this, $ref, $path, $limit, $offset, $firstParent);
    }

    /**
     * Get a log for a range ref
     *
     * @param string|TreeishInterface $ref         the treeish to check
     * @param string|TreeObject       $path        the physical path to the tree relative to the repository root
     * @param int|null                $limit       limit to n entries
     * @param int|null                $offset      skip n entries
     * @param boolean|false           $firstParent skip commits brought in to branch by a merge
     *
     * @return \GitElephant\Objects\LogRange
     */
    public function getLogRange($refStart, $refEnd, $path = null, $limit = 10, $offset = null, $firstParent = false)
    {
        // Handle when clients provide bad start reference on branch creation
        if (preg_match('~^[0]+$~', $refStart)) {
            return new Log($this, $refEnd, $path, $limit, $offset, $firstParent);
        }

        // Handle when clients provide bad end reference on branch deletion
        if (preg_match('~^[0]+$~', $refEnd)) {
            $refEnd = $refStart;
        }

        return new LogRange($this, $refStart, $refEnd, $path, $limit, $offset, $firstParent);
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
     * Fetch from the remote repository.
     */
    public function fetch()
    {
        $this->caller->execute(MainCommand::getInstance()->fetch());
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
     * @param null   $to  where to clone the repo
     */
    public function cloneFrom($url, $to = null)
    {
        $this->caller->execute(CloneCommand::getInstance()->cloneUrl($url, $to));
    }

    /**
     * get the humanish name of the repository
     *
     * @return string
     */
    public function getHumanishName()
    {
        $name = substr($this->getPath(), strrpos($this->getPath(), '/') + 1);
        $name = str_replace('.git', '.', $name);
        $name = str_replace('.bundle', '.', $name);

        return $name;
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
     * Order the branches list by name
     *
     * @param Objects\TreeBranch $a first branch
     * @param Objects\TreeBranch $b second branch
     *
     * @return int
     */
    private function sortBranchesByName($a, $b)
    {
        if ($a == 'master') {
            return -1;
        } else {
            if ($b == 'master') {
                return 1;
            } else {
                return 0;
            }
        }
    }

    /**
     * output a node content as an array of lines
     *
     * @param \GitElephant\Objects\TreeObject              $obj     The TreeObject of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @return array
     */
    public function outputContent(TreeObject $obj, $treeish)
    {
        $command = CatFileCommand::getInstance()->content($obj, $treeish);

        return $this->caller->execute($command)->getOutputLines();
    }

    /**
     * output a node raw content
     *
     * @param \GitElephant\Objects\TreeObject              $obj     The TreeObject of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @return string
     */
    public function outputRawContent(TreeObject $obj, $treeish)
    {
        $command = CatFileCommand::getInstance()->content($obj, $treeish);

        return $this->caller->execute($command)->getRawOutput();
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
