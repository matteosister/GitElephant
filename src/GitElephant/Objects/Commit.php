<?php

/*
 * This file is part of the GitWrapper package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant\Objects;

use GitElephant\Objects\GitAuthor;


/**
 * Commit an object representing a commit
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Commit
{
    private $sha;
    private $tree;
    private $parent;
    private $author;
    private $committer;
    private $message;
    private $datetime_author;
    private $datetime_committer;

    /**
     * Class constructor
     *
     * @param $outputLines Output of the git show command
     * @see ShowCommand::commitInfo
     */
    public function __construct($outputLines)
    {
        $this->message = array();
        foreach ($outputLines as $line) {
            $matches = array();
            if (preg_match('/^commit (\w+)$/', $line, $matches) > 0) {
                $this->sha = $matches[1];
            }
            if (preg_match('/^tree (\w+)$/', $line, $matches) > 0) {
                $this->tree = $matches[1];
            }
            if (preg_match('/^parent (\w+)$/', $line, $matches) > 0) {
                $this->parent = $matches[1];
            }
            if (preg_match('/^author (\w+) <(.*)> (\d+) (.*)$/', $line, $matches) > 0) {
                $author = new GitAuthor();
                $author->setName($matches[1]);
                $author->setEmail($matches[2]);
                $this->author = $author;
                $date = new \DateTime();
                $date->createFromFormat("U P", $matches[3].' '.$matches[4]);
                $this->datetime_author = $date;
            }
            if (preg_match('/^committer (\w+) <(.*)> (\d+) (.*)$/', $line, $matches) > 0) {
                $committer = new GitAuthor();
                $committer->setName($matches[1]);
                $committer->setEmail($matches[2]);
                $this->committer = $committer;
                $date = new \DateTime();
                $date->createFromFormat("U P", $matches[3].' '.$matches[4]);
                $this->datetime_committer = $date;
            }
            if (preg_match('/^    (.*)$/', $line, $matches)) {
                $this->message[] = $matches[1];
            }
        }
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getCommitter()
    {
        return $this->committer;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getSha()
    {
        return $this->sha;
    }

    public function getTree()
    {
        return $this->tree;
    }

    public function getDatetimeAuthor()
    {
        return $this->datetime_author;
    }

    public function getDatetimeCommitter()
    {
        return $this->datetime_committer;
    }

}
