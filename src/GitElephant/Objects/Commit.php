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


/**
 * Commit an object representing a commit
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Commit
{
    private $sha;
    private $parent;
    private $author;
    private $committer;
    private $message;

    /**
     * Class constructor
     *
     * @param $outputLines Output of the git show command
     * @see ShowCommand::commitInfo
     */
    public function __construct($outputLines)
    {
        var_dump($outputLines);
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


}
