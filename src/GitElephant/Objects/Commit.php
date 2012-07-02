<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Objects;

use GitElephant\Objects\GitAuthor;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Objects\Commit\Message;

/**
 * The Commit object represent a commit
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Commit implements TreeishInterface
{
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
     * the GitAuthor instance for author
     *
     * @var \GitElephant\Objects\GitAuthor
     */
    private $author;

    /**
     * the GitAuthor instance for committer
     *
     * @var \GitElephant\Objects\GitAuthor
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
     * Class constructor
     *
     * @param array $outputLines Output of the git show command
     *
     * @see ShowCommand::commitInfo
     */
    public function __construct($outputLines)
    {
        $message = array();
        $this->parents = array();

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
                $author = new GitAuthor();
                $author->setName($matches[1]);
                $author->setEmail($matches[2]);
                $this->author = $author;
                $date = \DateTime::createFromFormat('U P', $matches[3] . ' ' . $matches[4]);
                $this->datetimeAuthor = $date;
            }
            if (preg_match('/^committer (.*) <(.*)> (\d+) (.*)$/', $line, $matches) > 0) {
                $committer = new GitAuthor();
                $committer->setName($matches[1]);
                $committer->setEmail($matches[2]);
                $this->committer = $committer;
                $date = \DateTime::createFromFormat('U P', $matches[3] . ' ' . $matches[4]);
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
        return $this->getSha();
    }

    /**
     * author getter
     *
     * @return GitAuthor
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * committer getter
     *
     * @return GitAuthor
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
     * @return mixed
     */
    public function getSha()
    {
        return $this->sha;
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