<?php
/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2013  Matteo Giachino
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see [http://www.gnu.org/licenses/].
 */

namespace GitElephant;

use GitElephant\Command\FetchCommand;
use GitElephant\Command\PullCommand;
use GitElephant\Command\PushCommand;
use GitElephant\Command\RemoteCommand;
use GitElephant\Exception\InvalidRepositoryPathException;
use GitElephant\Command\Caller\Caller;
use GitElephant\Objects\Author;
use GitElephant\Objects\Remote;
use GitElephant\Objects\Tree;
use GitElephant\Objects\Branch;
use GitElephant\Objects\Tag;
use GitElephant\Objects\Object;
use GitElephant\Objects\Diff\Diff;
use GitElephant\Objects\Commit;
use GitElephant\Objects\Log;
use GitElephant\Objects\LogRange;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Command\MainCommand;
use GitElephant\Command\BranchCommand;
use GitElephant\Command\MergeCommand;
use GitElephant\Command\RevParseCommand;
use GitElephant\Command\TagCommand;
use GitElephant\Command\LogCommand;
use GitElephant\Command\CloneCommand;
use GitElephant\Command\CatFileCommand;
use GitElephant\Command\LsTreeCommand;
use GitElephant\Command\SubmoduleCommand;
use GitElephant\Status\Status;
use GitElephant\Status\StatusIndex;
use GitElephant\Status\StatusWorkingTree;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
     * @var \GitElephant\Command\Caller\Caller
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
     * @throws Exception\InvalidRepositoryPathException
     */
    public function __construct($repositoryPath, GitBinary $binary = null, $name = null)
    {
        if ($binary == null) {
            $binary = new GitBinary();
        }
        if (!is_dir($repositoryPath)) {
            throw new InvalidRepositoryPathException($repositoryPath);
        }
        $this->path = $repositoryPath;
        $this->caller = new Caller($binary, $repositoryPath);
        $this->name = $name;
    }

    /**
     * Factory method
     *
     * @param string         $repositoryPath the path of the git repository
     * @param GitBinary|null $binary         the GitBinary instance that calls the commands
     * @param string         $name           a repository name
     *
     * @return \GitElephant\Repository
     */
    public static function open($repositoryPath, GitBinary $binary = null, $name = null)
    {
        return new self($repositoryPath, $binary, $name);
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
     * @throws \RuntimeException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
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
        if ($git instanceof Repository) {
            $git = $git->getPath();
        }
        $repository->cloneFrom($git, $repositoryPath);
        $repository->checkoutAllRemoteBranches();

        return $repository;
    }

    /**
     * Init the repository
     *
     * @param bool $bare created a bare repository
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function init($bare = false)
    {
        $this->caller->execute(MainCommand::getInstance()->init($bare));

        return $this;
    }

    /**
     * Stage the working tree content
     *
     * @param string|Object $path the path to store
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function stage($path = '.')
    {
        $this->caller->execute(MainCommand::getInstance()->add($path));

        return $this;
    }

    /**
     * Unstage a tree content
     *
     * @param string|Object $path the path to unstage
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function unstage($path)
    {
        $this->caller->execute(MainCommand::getInstance()->unstage($path), true, null, array(0, 1));

        return $this;
    }

    /**
     * Move a file/directory
     *
     * @param string|Object $from source path
     * @param string|Object $to   destination path
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function move($from, $to)
    {
        $this->caller->execute(MainCommand::getInstance()->move($from, $to));

        return $this;
    }

    /**
     * Remove a file/directory
     *
     * @param string|Object $path      the path to remove
     * @param bool          $recursive recurse
     * @param bool          $force     force
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function remove($path, $recursive = false, $force = false)
    {
        $this->caller->execute(MainCommand::getInstance()->remove($path, $recursive, $force));

        return $this;
    }

    /**
     * Commit content to the repository, eventually staging all unstaged content
     *
     * @param string        $message  the commit message
     * @param bool          $stageAll whether to stage on not everything before commit
     * @param string|null   $ref      the reference to commit to (checkout -> commit -> checkout previous)
     * @param string|Author $author   override the author for this commit
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function commit($message, $stageAll = false, $ref = null, $author = null, $allowEmpty = false)
    {
        $currentBranch = null;
        if ($ref != null) {
            $currentBranch = $this->getMainBranch();
            $this->checkout($ref);
        }
        if ($stageAll) {
            $this->stage();
        }
        $this->caller->execute(MainCommand::getInstance()->commit($message, $stageAll, $author, $allowEmpty));
        if ($ref != null) {
            $this->checkout($currentBranch);
        }

        return $this;
    }

    /**
     * rev-parse command - often used to return a commit tag.
     *
     * @param array         $options the options to apply to rev-parse 
     * @param string        $arg the argument (may be a branch head, etc)
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getRevParse($options = null, $arg = null)
    {
        $this->caller->execute(RevParseCommand::getInstance()->revParse($options, $arg));

        return array_map('trim', $this->caller->getOutputLines());
    }

    /**
     * Get the repository status
     *
     * @return Status
     */
    public function getStatus()
    {
        return Status::get($this);
    }

    /**
     * @return StatusWorkingTree
     */
    public function getWorkingTreeStatus()
    {
        return StatusWorkingTree::get($this);
    }

    /**
     * @return StatusIndex
     */
    public function getIndexStatus()
    {
        return StatusIndex::get($this);
    }

    /**
     * Get the repository status as a string
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getStatusOutput()
    {
        $this->caller->execute(MainCommand::getInstance()->status());

        return array_map('trim', $this->caller->getOutputLines());
    }

    /**
     * Create a new branch
     *
     * @param string $name       the new branch name
     * @param null   $startPoint the reference to create the branch from
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function createBranch($name, $startPoint = null)
    {
        Branch::create($this, $name, $startPoint);

        return $this;
    }

    /**
     * Delete a branch by its name
     * This function change the state of the repository on the filesystem
     *
     * @param string $name  The branch to delete
     * @param bool   $force Force the delete
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function deleteBranch($name, $force = false)
    {
        $this->caller->execute(BranchCommand::getInstance()->delete($name, $force));

        return $this;
    }

    /**
     * An array of Branch objects
     *
     * @param bool $namesOnly return an array of branch names as a string
     * @param bool $all       lists also remote branches
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getBranches($namesOnly = false, $all = false)
    {
        $branches = array();
        if ($namesOnly) {
            $outputLines = $this->caller->execute(BranchCommand::getInstance()->lists($all, true))->getOutputLines(
                true
            );
            $branches = array_map(
                function ($v) {
                    return ltrim($v, '* ');
                },
                $outputLines
            );
            $sorter = function ($a, $b) {
                if ($a == 'master') {
                    return -1;
                } else {
                    if ($b == 'master') {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            };
        } else {
            $outputLines = $this->caller->execute(BranchCommand::getInstance()->lists($all))->getOutputLines(true);
            foreach ($outputLines as $branchLine) {
                $branches[] = Branch::createFromOutputLine($this, $branchLine);
            }
            $sorter = function (Branch $a, Branch $b) {
                if ($a->getName() == 'master') {
                    return -1;
                } else {
                    if ($b->getName() == 'master') {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            };
        }
        usort($branches, $sorter);

        return $branches;
    }

    /**
     * Return the actually checked out branch
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Objects\Branch
     */
    public function getMainBranch()
    {
        $filtered = array_filter(
            $this->getBranches(),
            function (Branch $branch) {
                return $branch->getCurrent();
            }
        );
        sort($filtered);

        return $filtered[0];
    }

    /**
     * Retrieve a Branch object by a branch name
     *
     * @param string $name The branch name
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return null|Branch
     */
    public function getBranch($name)
    {
        /** @var Branch $branch */
        foreach ($this->getBranches() as $branch) {
            if ($branch->getName() == $name) {
                return $branch;
            }
        }

        return null;
    }

    /**
     * Checkout all branches from the remote and make them local
     *
     * @param string $remote remote to fetch from
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function checkoutAllRemoteBranches($remote = 'origin')
    {
        $actualBranch = $this->getMainBranch();
        $actualBranches = $this->getBranches(true, false);
        $allBranches = $this->getBranches(true, true);
        $realBranches = array_filter(
            $allBranches,
            function ($branch) use ($actualBranches) {
                return !in_array($branch, $actualBranches)
                && preg_match('/^remotes(.+)$/', $branch)
                && !preg_match('/^(.+)(HEAD)(.*?)$/', $branch);
            }
        );
        foreach ($realBranches as $realBranch) {
            $this->checkout(str_replace(sprintf('remotes/%s/', $remote), '', $realBranch));
        }
        $this->checkout($actualBranch);

        return $this;
    }

    /**
     * Merge a Branch in the current checked out branch
     *
     * @param Objects\Branch $branch  The branch to merge in the current checked out branch
     * @param string         $message The message for the merge commit, if merge is 3-way
     * @param string         $mode    The merge mode: ff-only, no-ff or auto
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function merge(Branch $branch, $message = '', $mode = 'auto')
    {
        $valid_modes = array(
            'auto',    // deafult git behavior
            'ff-only', // force fast forward merge
            'no-ff',   // force 3-way merge
        );
        if (!in_array($mode, $valid_modes)) {
            throw new \Symfony\Component\Process\Exception\InvalidArgumentException("Invalid merge mode: $mode.");
        }

        $options = array();
        switch ($mode) {
            case 'ff-only':
                $options[] = MergeCommand::MERGE_OPTION_FF_ONLY;
                break;
            case 'no-ff':
                $options[] = MergeCommand::MERGE_OPTION_NO_FF;
                break;
        }

        $this->caller->execute(MergeCommand::getInstance()->merge($branch, $message, $options));

        return $this;
    }

    /**
     * Create a new tag
     * This function change the state of the repository on the filesystem
     *
     * @param string $name       The new tag name
     * @param null   $startPoint The reference to create the tag from
     * @param null   $message    the tag message
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function createTag($name, $startPoint = null, $message = null)
    {
        Tag::create($this, $name, $startPoint, $message);

        return $this;
    }

    /**
     * Delete a tag by it's name or by passing a Tag object
     * This function change the state of the repository on the filesystem
     *
     * @param string|Tag $tag The tag name or the Tag object
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function deleteTag($tag)
    {
        if ($tag instanceof Tag) {
            $tag->delete();
        } else {
            Tag::pick($this, $tag)->delete();
        }

        return $this;
    }

    /**
     * add a git submodule to the repository
     *
     * @param string $gitUrl git url of the submodule
     * @param string $path   path to register the submodule to
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function addSubmodule($gitUrl, $path = null)
    {
        $this->caller->execute(SubmoduleCommand::getInstance()->add($gitUrl, $path));

        return $this;
    }

    /**
     * Gets an array of Tag objects
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getTags()
    {
        $tags = array();
        $this->caller->execute(TagCommand::getInstance()->lists());
        foreach ($this->caller->getOutputLines() as $tagString) {
            if ($tagString != '') {
                $tags[] = new Tag($this, trim($tagString));
            }
        }

        return $tags;
    }

    /**
     * Return a tag object
     *
     * @param string $name The tag name
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Tag|null
     */
    public function getTag($name)
    {
        foreach ($this->getTags() as $tag) {
            /** @var $tag Tag */
            if ($name === $tag->getName()) {
                return $tag;
            }
        }

        return null;
    }

    /**
     * Return the last created tag
     *
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return Tag|null
     */
    public function getLastTag()
    {
        $finder = Finder::create()
                  ->files()
                  ->in(sprintf('%s/.git/refs/tags', $this->path))
                  ->sortByChangedTime();
        if ($finder->count() == 0) {
            return null;
        }
        $files = iterator_to_array($finder->getIterator(), false);
        $files = array_reverse($files);
        /** @var $firstFile SplFileInfo */
        $firstFile = $files[0];
        $tagName = $firstFile->getFilename();

        return Tag::pick($this, $tagName);
    }

    /**
     * Try to get a branch or a tag by its name.
     *
     * @param string $name the reference name (a tag name or a branch name)
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \GitElephant\Objects\Tag|\GitElephant\Objects\Branch|null
     */
    public function getBranchOrTag($name)
    {
        if (in_array($name, $this->getBranches(true))) {
            return new Branch($this, $name);
        }
        $tagFinderOutput = $this->caller->execute(TagCommand::getInstance()->lists())->getOutputLines(true);
        foreach ($tagFinderOutput as $line) {
            if ($line === $name) {
                return new Tag($this, $name);
            }
        }

        return null;
    }

    /**
     * Return a Commit object
     *
     * @param string $ref The commit reference
     *
     * @throws \RuntimeException
     * @return Objects\Commit
     */
    public function getCommit($ref = 'HEAD')
    {
        $commit = Commit::pick($this, $ref);

        return $commit;
    }

    /**
     * count the commit to arrive to the given treeish
     *
     * @param string $start
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return int|void
     */
    public function countCommits($start = 'HEAD')
    {
        $commit = Commit::pick($this, $start);

        return $commit->count();
    }

    /**
     * Get a log for a ref
     *
     * @param string|TreeishInterface $ref         the treeish to check
     * @param string|Object           $path        the physical path to the tree relative to the repository root
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
     * @param string        $refStart
     * @param string        $refEnd
     * @param string|Object $path        the physical path to the tree relative to the repository root
     * @param int|null      $limit       limit to n entries
     * @param int|null      $offset      skip n entries
     * @param boolean|false $firstParent skip commits brought in to branch by a merge
     *
     * @internal param \GitElephant\Objects\TreeishInterface|string $ref the treeish to check
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
     * @param \GitElephant\Objects\Object             $obj    The Object instance
     * @param null|string|\GitElephant\Objects\Branch $branch The branch to read from
     * @param int                                     $limit  Limit to n entries
     * @param int|null                                $offset Skip n entries
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \GitElephant\Objects\Log
     */
    public function getObjectLog(Object $obj, $branch = null, $limit = 1, $offset = null)
    {
        $command = LogCommand::getInstance()->showObjectLog($obj, $branch, $limit, $offset);

        return Log::createFromOutputLines($this, $this->caller->execute($command)->getOutputLines());
    }

    /**
     * Checkout a branch
     * This function change the state of the repository on the filesystem
     *
     * @param string|TreeishInterface $ref the reference to checkout
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function checkout($ref)
    {
        $this->caller->execute(MainCommand::getInstance()->checkout($ref));

        return $this;
    }

    /**
     * Retrieve an instance of Tree
     * Tree Object is Countable, Iterable and has ArrayAccess for easy manipulation
     *
     * @param string|TreeishInterface $ref  the treeish to check
     * @param string|Object           $path Object or null for root
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Objects\Tree
     */
    public function getTree($ref = 'HEAD', $path = null)
    {
        if (is_string($path) && '' !== $path) {
            $outputLines = $this->getCaller()->execute(
                LsTreeCommand::getInstance()->tree($ref, $path)
            )->getOutputLines(true);
            $path = Object::createFromOutputLine($this, $outputLines[0]);
        }

        return new Tree($this, $ref, $path);
    }

    /**
     * Get a Diff object for a commit with its parent, by default the diff is between the current head and its parent
     *
     * @param \GitElephant\Objects\Commit|string      $commit1 A TreeishInterface instance
     * @param \GitElephant\Objects\Commit|string|null $commit2 A TreeishInterface instance
     * @param null|string|Object                      $path    The path to get the diff for or a Object instance
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
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
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function cloneFrom($url, $to = null)
    {
        $this->caller->execute(CloneCommand::getInstance()->cloneUrl($url, $to));

        return $this;
    }

    /**
     * @param string $name remote name
     * @param string $url  remote url
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function addRemote($name, $url)
    {
        $this->caller->execute(RemoteCommand::getInstance()->add($name, $url));

        return $this;
    }

    /**
     * @param string $name         remote name
     * @param bool   $queryRemotes Fetch new information from remotes
     *
     * @return \GitElephant\Objects\Remote
     */
    public function getRemote($name, $queryRemotes = true)
    {
        return Remote::pick($this, $name, $queryRemotes);
    }

    /**
     * gets a list of remote objects
     *
     * @param bool $queryRemotes Fetch new information from remotes
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getRemotes($queryRemotes = true)
    {
        $remoteNames = $this->caller->execute(RemoteCommand::getInstance()->show(null, $queryRemotes))
          ->getOutputLines(true);
        $remotes = array();
        foreach ($remoteNames as $remoteName) {
            $remotes[] = $this->getRemote($remoteName, $queryRemotes);
        }

        return $remotes;
    }

    /**
     * Download objects and refs from another repository
     *
     * @param string $from
     * @param string $ref
     * @param bool   $tags
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function fetch($from = null, $ref = null, $tags = false)
    {
        $options = array();
        if ($tags === true) {
            $options = array('--tags');
        }
        $this->caller->execute(FetchCommand::getInstance()->fetch($from, $ref, $options));
    }

    /**
     * Fetch from and merge with another repository or a local branch
     *
     * @param string $from
     * @param string $ref
     * @param bool   $rebase
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function pull($from = null, $ref = null, $rebase = true)
    {
        $this->caller->execute(PullCommand::getInstance()->pull($from, $ref, $rebase));
    }

    /**
     * Fetch from and merge with another repository or a local branch
     *
     * @param string $to
     * @param string $ref
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function push($to = null, $ref = null)
    {
        $this->caller->execute(PushCommand::getInstance()->push($to, $ref));
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
     * output a node content as an array of lines
     *
     * @param \GitElephant\Objects\Object                  $obj     The Object of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function outputContent(Object $obj, $treeish)
    {
        $command = CatFileCommand::getInstance()->content($obj, $treeish);

        return $this->caller->execute($command)->getOutputLines();
    }

    /**
     * output a node raw content
     *
     * @param \GitElephant\Objects\Object                  $obj     The Object of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return string
     */
    public function outputRawContent(Object $obj, $treeish)
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
     * @param \GitElephant\Command\Caller\Caller $caller the caller variable
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     * Caller getter
     *
     * @return \GitElephant\Command\Caller\Caller
     */
    public function getCaller()
    {
        return $this->caller;
    }
}
