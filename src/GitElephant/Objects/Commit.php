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

namespace GitElephant\Objects;

use GitElephant\Command\BranchCommand;
use GitElephant\Command\MainCommand;
use GitElephant\Command\RevListCommand;
use GitElephant\Command\ShowCommand;
use GitElephant\Objects\Author;
use GitElephant\Objects\Commit\Message;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Repository;

/**
 * The Commit object represent a commit
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class Commit implements TreeishInterface, \Countable
{
    /**
     * @var \GitElephant\Repository
     */
    private $repository;

    /**
     * @var string
     */
    private $ref;

    /**
     * sha
     *
     * @var string
     */
    private $sha;

    /**
     * tree
     *
     * @var string
     */
    private $tree;

    /**
     * the commit parents
     *
     * @var array
     */
    private $parents;

    /**
     * the Author instance for author
     *
     * @var \GitElephant\Objects\Author
     */
    private $author;

    /**
     * the Author instance for committer
     *
     * @var \GitElephant\Objects\Author
     */
    private $committer;

    /**
     * the Message instance
     *
     * @var \GitElephant\Objects\Commit\Message
     */
    private $message;

    /**
     * the date for author
     *
     * @var \DateTime
     */
    private $datetimeAuthor;

    /**
     * the date for committer
     *
     * @var \Datetime
     */
    private $datetimeCommitter;

    /**
     * @param Repository $repository repository instance
     * @param string     $message    commit message
     * @param bool       $stageAll   automatically stage the dirty working tree. Alternatively call stage() on the repo
     *
     * @return Commit
     */
    public static function create(Repository $repository, $message, $stageAll = false)
    {
        $repository->getCaller()->execute(MainCommand::getInstance()->commit($message, $stageAll));

        return $repository->getCommit();
    }

    /**
     * @param Repository              $repository repository
     * @param TreeishInterface|string $treeish    treeish
     *
     * @return Commit
     */
    public static function pick(Repository $repository, $treeish = null)
    {
        $commit = new self($repository, $treeish);
        $commit->createFromCommand();

        return $commit;
    }

    /**
     * static generator to generate a single commit from output of command.show service
     *
     * @param \GitElephant\Repository $repository  repository
     * @param array                   $outputLines output lines
     *
     * @return Commit
     */
    public static function createFromOutputLines(Repository $repository, $outputLines)
    {
        $commit = new self($repository);
        $commit->parseOutputLines($outputLines);

        return $commit;
    }

    /**
     * Class constructor
     *
     * @param \GitElephant\Repository $repository the repository
     * @param string                  $treeish    a treeish reference
     */
    private function __construct(Repository $repository, $treeish = 'HEAD')
    {
        $this->repository = $repository;
        $this->ref = $treeish;
        $this->parents = array();
    }

    /**
     * get the commit properties from command
     *
     * @see ShowCommand::commitInfo
     */
    public function createFromCommand()
    {
        $command = ShowCommand::getInstance()->showCommit($this->ref);
        $outputLines = $this->getCaller()->execute($command, true, $this->getRepository()->getPath())->getOutputLines();
        $this->parseOutputLines($outputLines);
    }

    /**
     * get the branches this commit is contained in
     *
     * @see BranchCommand::contains
     */
    public function getContainedIn()
    {
        $command = BranchCommand::getInstance()->contains($this->getSha());

        return array_map('trim', (array)$this->getCaller()->execute($command)->getOutputLines(true));
    }

    /**
     * @return int|void
     */
    public function count()
    {
        $command = RevListCommand::getInstance()->commitPath($this);

        return count($this->getCaller()->execute($command)->getOutputLines(true));
    }

    /**
     * parse the output of a git command showing a commit
     *
     * @param array $outputLines output lines
     */
    private function parseOutputLines($outputLines)
    {
        $message = '';
        foreach ($outputLines as $line) {
            $matches = array();
            if (preg_match('/^commit (\w+)$/', $line, $matches) > 0) {
                $this->sha = $matches[1];
            }
            if (preg_match('/^tree (\w+)$/', $line, $matches) > 0) {
                $this->tree = $matches[1];
            }
            if (preg_match('/^parent (\w+)$/', $line, $matches) > 0) {
                $this->parents[] = $matches[1];
            }
            if (preg_match('/^author (.*) <(.*)> (\d+) (.*)$/', $line, $matches) > 0) {
                $author = new Author();
                $author->setName($matches[1]);
                $author->setEmail($matches[2]);
                $this->author = $author;
                $date = \DateTime::createFromFormat('U', $matches[3]);
                $this->datetimeAuthor = $date;
            }
            if (preg_match('/^committer (.*) <(.*)> (\d+) (.*)$/', $line, $matches) > 0) {
                $committer = new Author();
                $committer->setName($matches[1]);
                $committer->setEmail($matches[2]);
                $this->committer = $committer;
                $date = \DateTime::createFromFormat('U', $matches[3]);
                $this->datetimeCommitter = $date;
            }
            if (preg_match('/^    (.*)$/', $line, $matches)) {
                $message[] = $matches[1];
            }
        }
        $this->message = new Message($message);
    }

    /**
     * Returns true if the commit is a root commit. Usually the first of the repository
     *
     * @return bool
     */
    public function isRoot()
    {
        return count($this->parents) == 0;
    }

    /**
     * toString magic method
     *
     * @return string the sha
     */
    public function __toString()
    {
        return $this->sha;
    }

    /**
     * @return \GitElephant\Command\Caller\Caller
     */
    private function getCaller()
    {
        return $this->getRepository()->getCaller();
    }

    /**
     * Repository setter
     *
     * @param \GitElephant\Repository $repository repository variable
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Repository getter
     *
     * @return \GitElephant\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * author getter
     *
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * committer getter
     *
     * @return Author
     */
    public function getCommitter()
    {
        return $this->committer;
    }

    /**
     * message getter
     *
     * @return \GitElephant\Objects\Commit\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * parent getter
     *
     * @return mixed
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * sha getter
     *
     * @param bool $short short version
     *
     * @return mixed
     */
    public function getSha($short = false)
    {
        return $short ? substr($this->sha, 0, 7) : $this->sha;
    }

    /**
     * tree getter
     *
     * @return mixed
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * datetimeAuthor getter
     *
     * @return mixed
     */
    public function getDatetimeAuthor()
    {
        return $this->datetimeAuthor;
    }

    /**
     * datetimeCommitter getter
     *
     * @return \DateTime
     */
    public function getDatetimeCommitter()
    {
        return $this->datetimeCommitter;
    }
}
