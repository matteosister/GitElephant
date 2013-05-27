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

use GitElephant\Command\BranchCommand;
use GitElephant\Command\MergeCommand;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Repository;


/**
 * An object representing a git branch
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Branch extends Object implements TreeishInterface
{
    /**
     * @var \GitElephant\Repository
     */
    private $repository;

    /**
     * current checked out branch
     *
     * @var bool
     */
    private $current = false;

    /**
     * branch name
     *
     * @var string
     */
    private $name;

    /**
     * sha
     *
     * @var string
     */
    private $sha;

    /**
     * branch comment
     *
     * @var string
     */
    private $comment;

    /**
     * the full branch reference
     *
     * @var string
     */
    private $fullRef;

    /**
     * static generator to generate a single commit from output of command.show service
     *
     * @param \GitElephant\Repository $repository repository
     * @param string                  $outputLine output line
     *
     * @return Branch
     */
    public static function createFromOutputLine(Repository $repository, $outputLine)
    {
        $matches = static::getMatches($outputLine);
        $branch = new self($repository, $matches[1]);
        $branch->parseOutputLine($outputLine);

        return $branch;
    }

    /**
     * Class constructor
     *
     * @param \GitElephant\Repository $repository repository instance
     * @param string                  $name       branch name
     */
    public function __construct(Repository $repository, $name)
    {
        $this->repository = $repository;
        $this->name = trim($name);
        $this->fullRef = 'refs/heads/'.$name;
        $this->createFromCommand();
    }

    /**
     * @param \GitElephant\Repository $repository repository instance
     * @param string                  $name       branch name
     *
     * @return Branch
     */
    public static function checkout(Repository $repository, $name)
    {
        return new self($repository, $name);
    }

    /**
     * get the branch properties from command
     *
     * @throws \InvalidArgumentException
     */
    private function createFromCommand()
    {
        $command = BranchCommand::getInstance()->lists();
        $outputLines = $this->repository->getCaller()->execute($command)->getOutputLines(true);
        foreach ($outputLines as $outputLine) {
            $matches = static::getMatches($outputLine);
            if ($this->name === $matches[1]) {
                $this->parseOutputLine($outputLine);

                return;
            }
        }
        throw new \InvalidArgumentException(sprintf('The %s branch doesn\'t exists', $this->name));
    }

    /**
     * parse an output line from the BranchCommand::singleInfo command
     *
     * @param string $branchString an output line for a branch
     */
    public function parseOutputLine($branchString)
    {
        if (preg_match('/^\* (.*)/', $branchString, $matches)) {
            $this->current = true;
            $branchString = substr($branchString, 2);
        } else {
            $branchString = trim($branchString);
        }
        $matches = static::getMatches($branchString);
        $this->name = $matches[1];
        $this->sha = $matches[2];
        $this->comment = $matches[3];
    }

    /**
     * get the matches from an output line
     *
     * @param string $branchString branch line output
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function getMatches($branchString)
    {
        $matches = array();
        preg_match('/^\*?\ *?(\S+)\ +(\S{40})\ +(.+)$/', trim($branchString), $matches);
        if (!count($matches)) {
            throw new \InvalidArgumentException(sprintf('the branch string is not valid: %s', $branchString));
        }

        return array_map('trim', $matches);
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
