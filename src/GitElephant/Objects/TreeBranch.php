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

use GitElephant\Objects\TreeishInterface;


/**
 * An object representing a git branch
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeBranch implements TreeishInterface
{
    private $current = false;
    private $name;
    private $sha;
    private $comment;
    private $fullRef;

    /**
     * Class constructor
     *
     * @param null|string $branchString a branch line output from the git binary
     */
    public function __construct($branchString = null)
    {
        $branchString = trim($branchString);
        if (preg_match('/^\*\ (.*)/', $branchString)) {
            $this->current = true;
            $branchString  = preg_replace('/^\*\ /', '', $branchString);
        }

        $firstBlank   = strpos($branchString, ' ');
        $this->name    = trim(substr($branchString, 0, $firstBlank));
        $this->fullRef = 'refs/heads/' . $this->name;
        $branchString  = substr($branchString, $firstBlank);
        $branchString  = preg_replace('/^\ +/', '', $branchString);
        $firstBlank   = strpos($branchString, ' ');
        $this->sha     = trim(substr($branchString, 0, $firstBlank));
        $this->comment = trim(substr($branchString, $firstBlank));
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
     * name setter
     *
     * @param string $name the branch name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * name setter
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * sha setter
     *
     * @param string $sha the sha of the branch
     */
    public function setSha($sha)
    {
        $this->sha = $sha;
    }

    /**
     * sha getter
     *
     * @return string
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * current setter
     *
     * @param bool $current whether if the branch is the current or not
     */
    public function setCurrent($current)
    {
        $this->current = $current;
    }

    /**
     * current getter
     *
     * @return bool
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * comment setter
     *
     * @param string $comment the branch comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * comment getter
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * fullref setter
     *
     * @param string $fullRef full git reference of the branch
     */
    public function setFullRef($fullRef)
    {
        $this->fullRef = $fullRef;
    }

    /**
     * fullRef getter
     *
     * @return string
     */
    public function getFullRef()
    {
        return $this->fullRef;
    }
}
