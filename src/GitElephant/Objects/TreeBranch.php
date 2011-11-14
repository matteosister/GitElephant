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

namespace GitElephant\Objects;


/**
 * Branch
 *
 * An object representing a git branch
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeBranch
{
    private $current = false;
    private $name;
    private $sha;
    private $comment;

    public function __construct($branchString = null)
    {
        $branchString = trim($branchString);
        if (preg_match('/^\*\ (.*)/', $branchString)) {
            $this->current = true;
            $branchString = preg_replace('/^\*\ /', '', $branchString);
        }

        $first_blank = strpos($branchString, ' ');
        $this->name = trim(substr($branchString, 0, $first_blank));
        $branchString = substr($branchString, $first_blank);
        $branchString = preg_replace('/^\ +/', '', $branchString);
        $first_blank = strpos($branchString, ' ');
        $this->sha = trim(substr($branchString, 0, $first_blank));
        $this->comment = trim(substr($branchString, $first_blank));
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSha($sha)
    {
        $this->sha = $sha;
    }

    public function getSha()
    {
        return $this->sha;
    }

    public function setCurrent($current)
    {
        $this->current = $current;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
