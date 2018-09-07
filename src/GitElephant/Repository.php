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

use GitElephant\Command\ResetCommand;
use GitElephant\Command\StashCommand;
use GitElephant\Objects\TreeObject;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use \GitElephant\Command\BranchCommand;
use \GitElephant\Command\Caller\Caller;
use \GitElephant\Command\CatFileCommand;
use \GitElephant\Command\CloneCommand;
use \GitElephant\Command\FetchCommand;
use \GitElephant\Command\LogCommand;
use \GitElephant\Command\LsTreeCommand;
use \GitElephant\Command\MainCommand;
use \GitElephant\Command\MergeCommand;
use \GitElephant\Command\PullCommand;
use \GitElephant\Command\PushCommand;
use \GitElephant\Command\RemoteCommand;
use \GitElephant\Command\RevParseCommand;
use \GitElephant\Command\SubmoduleCommand;
use \GitElephant\Command\TagCommand;
use \GitElephant\Objects\Author;
use \GitElephant\Objects\Branch;
use \GitElephant\Objects\Commit;
use \GitElephant\Objects\Diff\Diff;
use \GitElephant\Objects\Log;
use \GitElephant\Objects\LogRange;
use \GitElephant\Objects\NodeObject;
use \GitElephant\Objects\Remote;
use \GitElephant\Objects\Tag;
use \GitElephant\Objects\Tree;
use \GitElephant\Objects\TreeishInterface;
use \GitElephant\Status\Status;
use \GitElephant\Status\StatusIndex;
use \GitElephant\Status\StatusWorkingTree;
use \Symfony\Component\Filesystem\Filesystem;
use \Symfony\Component\Finder\Finder;
use \Symfony\Component\Finder\SplFileInfo;

/**
 * Repository
 *
 * Base Class for repository operations
 *
 * @author Matteo Giachino <matteog@gmail.com>
 * @author Dhaval Patel <tech.dhaval@gmail.com>
 * @author Kirk Madera <kmadera@robofirm.com>
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
     * A list of global configs to apply to every command
     *
     * @var array
     */
    private $globalConfigs = [];

    /**
     * A list of global options to apply to every command
     *
     * @var array
     */
    private $globalOptions = [];

    /**
     * A list of global arguments to apply to every command
     *
     * @var array
     */
    private $globalCommandArguments = [];

    /**
     * Class constructor
     *
     * @param string         $repositoryPath the path of the git repository
     * @param string|null $binary         the path to the git binary
     * @param string         $name           a repository name
     *
     * @throws Exception\InvalidRepositoryPathException
     */
    public function __construct($repositoryPath, string $binary = null, $name = null)
    {
        $this->path = $repositoryPath;
        $this->caller = new Caller($binary, $repositoryPath);
        $this->name = $name;
    }

    /**
     * Factory method
     *
     * @param string         $repositoryPath the path of the git repository
     * @param string|null $binary         the path to the git binary
     * @param string         $name           a repository name
     *
     * @return \GitElephant\Repository
     */
    public static function open($repositoryPath, string $binary = null, $name = null)
    {
        return new self($repositoryPath, $binary, $name);
    }

    /**
     * create a repository from a remote git url, or a local filesystem
     * and save it in a temp folder
     *
     * @param string|Repository $git            the git remote url, or the filesystem path
     * @param null              $repositoryPath path
     * @param string|null $binary         the path to the git binary
     * @param null              $name           repository name
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @return Repository
     */
    public static function createFromRemote($git, $repositoryPath = null, string $binary = null, $name = null)
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
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function init($bare = false)
    {
        $this->caller->execute(MainCommand::getInstance($this)->init($bare));

        return $this;
    }

    /**
     * Stage the working tree content
     *
     * @param string|NodeObject $path the path to store
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function stage($path = '.')
    {
        $this->caller->execute(MainCommand::getInstance($this)->add($path));

        return $this;
    }

    /**
     * Unstage a tree content
     *
     * @param string|NodeObject $path the path to unstage
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function unstage($path)
    {
        $this->caller->execute(MainCommand::getInstance($this)->unstage($path), true, null, [0, 1]);

        return $this;
    }

    /**
     * Move a file/directory
     *
     * @param string|NodeObject $from source path
     * @param string|NodeObject $to   destination path
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function move($from, $to)
    {
        $this->caller->execute(MainCommand::getInstance($this)->move($from, $to));

        return $this;
    }

    /**
     * Remove a file/directory
     *
     * @param string|NodeObject $path      the path to remove
     * @param bool              $recursive recurse
     * @param bool              $force     force
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function remove($path, $recursive = false, $force = false)
    {
        $this->caller->execute(MainCommand::getInstance($this)->remove($path, $recursive, $force));

        return $this;
    }

    /**
     * Commit content to the repository, eventually staging all unstaged content
     *
     * @param string        $message    the commit message
     * @param bool          $stageAll   whether to stage on not everything before commit
     * @param string|null   $ref        the reference to commit to (checkout -> commit -> checkout previous)
     * @param string|Author $author     override the author for this commit
     * @param bool          $allowEmpty override the author for this commit
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function commit(string $message, $stageAll = false, $ref = null, $author = null, $allowEmpty = false)
    {
        $currentBranch = null;
        if (!is_null($ref)) {
            $currentBranch = $this->getMainBranch();
            $this->checkout($ref);
        }
        if ($stageAll) {
            $this->stage();
        }
        $this->caller->execute(MainCommand::getInstance($this)->commit($message, $stageAll, $author, $allowEmpty));
        if (!is_null($ref)) {
            $this->checkout($currentBranch);
        }

        return $this;
    }

    /**
     * rev-parse command - often used to return a commit tag.
     *
     * @param array                    $options the options to apply to rev-parse
     * @param string|NodeObject|Commit $arg     the argument (may be a branch head, etc)
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function revParse(string $arg = null, array $options = [])
    {
        $this->caller->execute(RevParseCommand::getInstance()->revParse($arg, $options));

        return array_map('trim', $this->caller->getOutputLines(true));
    }

    /**
     * Check if this is a bare repository
     *
     * @return boolean
     */
    public function isBare()
    {
        $options = [RevParseCommand::OPTION_IS_BARE_REPOSIORY];
        $this->caller->execute(RevParseCommand::getInstance()->revParse(null, $options));

        return trim($this->caller->getOutput()) === 'true';
    }

    /**
     * @param TreeishInterface|Commit|string $arg
     * @param array                          $options
     */
    public function reset($arg, $options)
    {
        $this->caller->execute(ResetCommand::getInstance($this)->reset($arg, $options));
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
     * @return Status
     */
    public function getWorkingTreeStatus()
    {
        return StatusWorkingTree::get($this);
    }

    /**
     * @return Status
     */
    public function getIndexStatus()
    {
        return StatusIndex::get($this);
    }

    /**
     * isClean Return true if the repository is not dirty.
     *
     * @return boolean
     */
    public function isClean()
    {
        return $this->getStatus()->all()->isEmpty();
    }

    /**
     * isDirty Return true if the repository has some modified files.
     *
     * @return boolean
     */
    public function isDirty()
    {
        return !$this->isClean();
    }

    /**
     * Get the repository status as a string
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getStatusOutput()
    {
        $this->caller->execute(MainCommand::getInstance($this)->status());

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
    public function createBranch(string $name, $startPoint = null)
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
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function deleteBranch(string $name, bool $force = false)
    {
        $this->caller->execute(BranchCommand::getInstance($this)->delete($name, $force));

        return $this;
    }

    /**
     * An array of Branch objects
     *
     * @param bool $namesOnly return an array of branch names as a string
     * @param bool $all       lists also remote branches
     *
     * @throws \RuntimeException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getBranches(bool $namesOnly = false, bool $all = false)
    {
        $branches = [];
        if ($namesOnly) {
            $outputLines = $this->caller
                ->execute(BranchCommand::getInstance($this)->listBranches($all, true))
                ->getOutputLines(true);

            $branches = array_map(
                function ($v) {
                    return ltrim($v, '* ');
                },
                $outputLines
            );
        } else {
            $outputLines = $this->caller
                ->execute(BranchCommand::getInstance($this)->listBranches($all))
                ->getOutputLines(true);

            foreach ($outputLines as $branchLine) {
                $branches[] = Branch::createFromOutputLine($this, $branchLine);
            }
        }

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
    public function getBranch(string $name)
    {
        /** @var Branch $branch */
        foreach ($this->getBranches() as $branch) {
            if ($branch->getName() === $name) {
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
            function (string $branch) use ($actualBranches) {
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
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function merge(Branch $branch, string $message = '', string $mode = 'auto')
    {
        $valid_modes = [
            'auto', // deafult git behavior
            'ff-only', // force fast forward merge
            'no-ff', // force 3-way merge
        ];
        if (!in_array($mode, $valid_modes)) {
            throw new InvalidArgumentException("Invalid merge mode: $mode.");
        }

        $options = [];
        switch ($mode) {
            case 'ff-only':
                $options[] = MergeCommand::MERGE_OPTION_FF_ONLY;
                break;
            case 'no-ff':
                $options[] = MergeCommand::MERGE_OPTION_NO_FF;
                break;
        }

        $this->caller->execute(MergeCommand::getInstance($this)->merge($branch, $message, $options));

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
    public function createTag(string $name, $startPoint = null, string $message = null)
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
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function addSubmodule(string $gitUrl, $path = null)
    {
        $this->caller->execute(SubmoduleCommand::getInstance($this)->add($gitUrl, $path));

        return $this;
    }

    /**
     * initialize submodules
     *
     * @param  string $path init only submodules at the specified path
     *
     * @return Repository
     */
    public function initSubmodule($path = null)
    {
        $this->caller->execute(SubmoduleCommand::getInstance($this)->init($path));

        return $this;
    }

    /**
     * update submodules
     *
     * @param  bool   $recursive update recursively
     * @param  bool   $init      init before update
     * @param  bool   $force     force the checkout as part of update
     * @param  string $path      update only a specific submodule path
     *
     * @return Repository
     */
    public function updateSubmodule(
        bool $recursive = false,
        bool $init = false,
        bool $force = false,
        $path = null
    ) {
        $this->caller->execute(SubmoduleCommand::getInstance($this)->update($recursive, $init, $force, $path));

        return $this;
    }

    /**
     * Gets an array of Tag objects
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getTags()
    {
        $tags = [];
        $this->caller->execute(TagCommand::getInstance($this)->listTags());
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
    public function getTag(string $name)
    {
        $tagFinderOutput = $this->caller
            ->execute(TagCommand::getInstance()->listTags())
            ->getOutputLines(true);

        foreach ($tagFinderOutput as $line) {
            if ($line === $name) {
                return new Tag($this, $name);
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
    public function getBranchOrTag(string $name)
    {
        if (in_array($name, $this->getBranches(true))) {
            return new Branch($this, $name);
        }
        $tagFinderOutput = $this->caller->execute(TagCommand::getInstance($this)->listTags())->getOutputLines(true);
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
     * @return int
     */
    public function countCommits($start = 'HEAD')
    {
        $commit = Commit::pick($this, $start);

        return $commit->count();
    }

    /**
     * Get a log for a ref
     *
     * @param string|TreeishInterface|array $ref         the treeish to check, as a string, as an object or as an array
     * @param string|NodeObject             $path        the physical path to the tree relative to the repository root
     * @param int|null                      $limit       limit to n entries
     * @param int|null                      $offset      skip n entries
     * @param boolean|false                 $firstParent skip commits brought in to branch by a merge
     *
     * @return \GitElephant\Objects\Log
     */
    public function getLog(
        $ref = 'HEAD',
        $path = null,
        int $limit = 10,
        int $offset = null,
        bool $firstParent = false
    ) {
        return new Log($this, $ref, $path, $limit, $offset, $firstParent);
    }

    /**
     * Get a log for a range ref
     *
     * @param string            $refStart
     * @param string            $refEnd
     * @param string|NodeObject $path        the physical path to the tree relative to the repository root
     * @param int|null          $limit       limit to n entries
     * @param int|null          $offset      skip n entries
     * @param boolean|false     $firstParent skip commits brought in to branch by a merge
     *
     * @return \GitElephant\Objects\LogRange|\GitElephant\Objects\Log
     */
    public function getLogRange(
        $refStart,
        $refEnd,
        $path = null,
        int $limit = 10,
        int $offset = null,
        bool $firstParent = false
    ) {
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
     * @param \GitElephant\Objects\NodeObject         $obj    The Object instance
     * @param null|string|\GitElephant\Objects\Branch $branch The branch to read from
     * @param int                                     $limit  Limit to n entries
     * @param int|null                                $offset Skip n entries
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \GitElephant\Objects\Log
     */
    public function getObjectLog(NodeObject $obj, $branch = null, int $limit = 1, int $offset = null)
    {
        $command = LogCommand::getInstance($this)->showObjectLog($obj, $branch, $limit, $offset);

        return Log::createFromOutputLines($this, $this->caller->execute($command)->getOutputLines());
    }

    /**
     * Checkout a branch
     * This function change the state of the repository on the filesystem
     *
     * @param string|TreeishInterface $ref    the reference to checkout
     * @param bool                    $create like -b on the command line
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function checkout($ref, bool $create = false)
    {
        if ($create && is_null($this->getBranch($ref))) {
            $this->createBranch($ref);
        }
        $this->caller->execute(MainCommand::getInstance($this)->checkout($ref));

        return $this;
    }

    /**
     * Retrieve an instance of Tree
     * Tree Object is Countable, Iterable and has ArrayAccess for easy manipulation
     *
     * @param string|TreeishInterface $ref  the treeish to check
     * @param string|NodeObject       $path Object or null for root
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Objects\Tree
     */
    public function getTree($ref = 'HEAD', $path = null)
    {
        if (is_string($path) && '' !== $path) {
            $outputLines = $this
                ->getCaller()
                ->execute(LsTreeCommand::getInstance($this)->tree($ref, $path))
                ->getOutputLines(true);

            $path = TreeObject::createFromOutputLine($this, $outputLines[0]);
        }

        return new Tree($this, $ref, $path);
    }

    /**
     * Get a Diff object for a commit with its parent, by default the diff is between the current head and its parent
     *
     * @param \GitElephant\Objects\Commit|string      $commit1 A TreeishInterface instance
     * @param \GitElephant\Objects\Commit|string|null $commit2 A TreeishInterface instance
     * @param null|string|NodeObject                  $path    The path to get the diff for or a Object instance
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return Objects\Diff\Diff
     */
    public function getDiff(string $commit1 = null, string $commit2 = null, string $path = null)
    {
        return Diff::create($this, $commit1, $commit2, $path);
    }

    /**
     * Clone a repository
     *
     * @param string      $url           the repository url (i.e. git://github.com/matteosister/GitElephant.git)
     * @param null        $to            where to clone the repo
     * @param string|null $repoReference Repo reference to clone. Required if performing a shallow clone.
     * @param int|null    $depth         Depth to clone repo. Specify 1 to perform a shallow clone
     * @param bool        $recursive     Whether to recursively clone child repos.
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function cloneFrom(string $url, string $to = null, string $repoReference = null, int $depth = null, bool $recursive = false)
    {
        $command = (Command\CloneCommand::getInstance($this))->cloneUrl($url, $to, $repoReference, $depth, $recursive);
        $this->caller->execute($command);
        return $this;
    }

    /**
     * @param string $name remote name
     * @param string $url  remote url
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Repository
     */
    public function addRemote(string $name, string $url)
    {
        $this->caller->execute(RemoteCommand::getInstance($this)->add($name, $url));

        return $this;
    }

    /**
     * @param string $name         remote name
     * @param bool   $queryRemotes Fetch new information from remotes
     *
     * @return \GitElephant\Objects\Remote
     */
    public function getRemote(string $name, bool $queryRemotes = true)
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
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function getRemotes(bool $queryRemotes = true)
    {
        $remoteNames = $this->caller
            ->execute(RemoteCommand::getInstance($this)->show(null, $queryRemotes))
            ->getOutputLines(true);

        $remotes = [];
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
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function fetch($from = null, $ref = null, bool $tags = false)
    {
        $options = [];
        if ($tags === true) {
            $options = ['--tags'];
        }
        $this->caller->execute(FetchCommand::getInstance($this)->fetch($from, $ref, $options));
    }

    /**
     * Fetch from and merge with another repository or a local branch
     *
     * @param string $from
     * @param string $ref
     * @param bool   $rebase
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function pull($from = null, $ref = null, bool $rebase = true)
    {
        $this->caller->execute(PullCommand::getInstance($this)->pull($from, $ref, $rebase));
    }

    /**
     * Push changes to remote repository
     *
     * @param string $to
     * @param string $ref
     * @param string $args
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function push($to = null, $ref = null, string $args = null)
    {
        $this->caller->execute(PushCommand::getInstance($this)->push($to, $ref, $args));
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
     * @param \GitElephant\Objects\NodeObject              $obj     The Object of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return array
     */
    public function outputContent(NodeObject $obj, $treeish)
    {
        $command = CatFileCommand::getInstance($this)->content($obj, $treeish);

        return $this->caller->execute($command)->getOutputLines();
    }

    /**
     * output a node raw content
     *
     * @param \GitElephant\Objects\NodeObject              $obj     The Object of type BLOB
     * @param \GitElephant\Objects\TreeishInterface|string $treeish A treeish object
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return string
     */
    public function outputRawContent(NodeObject $obj, $treeish)
    {
        $command = CatFileCommand::getInstance($this)->content($obj, $treeish);

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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Caller setter
     *
     * @param \GitElephant\Command\Caller\Caller $caller the caller variable
     */
    public function setCaller(Caller $caller)
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

    /**
     * get global config list
     *
     * @return array Global config list
     */
    public function getGlobalConfigs()
    {
        return $this->globalConfigs;
    }

    /**
     * add a key/value pair to the global config list
     *
     * @param string $name  The config name
     * @param mixed  $value The config value
     */
    public function addGlobalConfig(string $name, $value)
    {
        $this->globalConfigs[$name] = $value;
    }

    /**
     * remove an element form the global config list, identified by key
     *
     * @param  string $name The config name
     */
    public function removeGlobalConfig(string $name)
    {
        if (isset($this->globalConfigs[$name])) {
            unset($this->globalConfigs[$name]);
        }
    }

    /**
     * get global options list
     *
     * @return array Global options list
     */
    public function getGlobalOptions()
    {
        return $this->globalOptions;
    }

    /**
     * add a key/value pair to the global option list
     *
     * @param string $name  The option name
     * @param mixed  $value The option value
     */
    public function addGlobalOption(string $name, $value)
    {
        $this->globalOptions[$name] = $value;
    }

    /**
     * remove an element form the global option list, identified by key
     *
     * @param  string $name The option name
     */
    public function removeGlobalOption(string $name)
    {
        if (isset($this->globalOptions[$name])) {
            unset($this->globalOptions[$name]);
        }
    }

    /**
     * get global command arguments list
     *
     * @return array Global command arguments list
     */
    public function getGlobalCommandArguments()
    {
        return $this->globalCommandArguments;
    }

    /**
     * add a value to the global command argument list
     *
     * @param string $value The command argument
     */
    public function addGlobalCommandArgument($value)
    {
        if (!in_array($value, $this->globalCommandArguments, true)) {
            $this->globalCommandArguments[] = $value;
        }
    }

    /**
     * remove an element form the global command argument list, identified by
     * value
     *
     * @param  string $value The command argument
     */
    public function removeGlobalCommandArgument($value)
    {
        if (in_array($value, $this->globalCommandArguments, true)) {
            $index = array_search($value, $this->globalCommandArguments);
            unset($this->globalCommandArguments[$index]);
        }
    }

    /**
     *  Save your local modifications to a new stash, and run git reset --hard to revert them.
     *
     * @param string|null $message
     * @param boolean     $includeUntracked
     * @param boolean     $keepIndex
     */
    public function stash(string $message = null, bool $includeUntracked = false, bool $keepIndex = false)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->save($message, $includeUntracked, $keepIndex);
        $this->caller->execute($command);
    }

    /**
     * Shows stash list
     *
     * @param array|null $options
     *
     * @return array
     */
    public function stashList(array $options = null)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->listStashes($options);
        $this->caller->execute($command);

        return array_map('trim', $this->caller->getOutputLines(true));
    }

    /**
     * Shows details for a stash
     *
     * @param string $stash
     *
     * @return string
     */
    public function stashShow(string $stash)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->show($stash);
        $this->caller->execute($command);

        return $this->caller->getOutput();
    }

    /**
     * Drops a stash
     *
     * @param string $stash
     */
    public function stashDrop(string $stash)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->drop($stash);
        $this->caller->execute($command);
    }

    /**
     * Applies a stash
     *
     * @param string  $stash
     * @param boolean $index
     */
    public function stashApply(string $stash, bool $index = false)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->apply($stash, $index);
        $this->caller->execute($command);
    }

    /**
     *  Applies a stash, then removes it from the stash
     *
     * @param string  $stash
     * @param boolean $index
     */
    public function stashPop(string $stash, bool $index = false)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->pop($stash, $index);
        $this->caller->execute($command);
    }

    /**
     *  Creates and checks out a new branch named <branchname> starting from the commit at which the <stash> was originally created
     *
     * @param string $branch
     * @param string $stash
     */
    public function stashBranch(string $branch, string $stash)
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->branch($branch, $stash);
        $this->caller->execute($command);
    }

    /**
     *  Save your local modifications to a new stash, and run git reset --hard to revert them.
     *
     */
    public function stashClear()
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->clear();
        $this->caller->execute($command);
    }

    /**
     *  Create a stash (which is a regular commit object) and return its object name, without storing it anywhere in the
     *  ref namespace.
     *
     * @return string
     */
    public function stashCreate()
    {
        $stashCommand = StashCommand::getInstance($this);
        $command = $stashCommand->clear();
        $this->caller->execute($command);

        return $this->caller->getOutput();
    }
}
