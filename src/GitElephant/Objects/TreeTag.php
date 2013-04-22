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

use GitElephant\Objects\TreeishInterface,
    GitElephant\Repository,
    GitElephant\Command\TagCommand,
    GitElephant\Command\RevListCommand;


/**
 * An object representing a git tag
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeTag implements TreeishInterface
{
    /**
     * @var \GitElephant\Repository
     */
    private $repository;

    /**
     * tag name
     *
     * @var string
     */
    private $name;

    /**
     * full reference
     *
     * @var string
     */
    private $fullRef;

    /**
     * sha
     *
     * @var string
     */
    private $sha;

    /**
     * static generator to generate a single commit from output of command.show service
     *
     * @param \GitElephant\Repository $repository  repository
     * @param array                   $outputLines output lines
     * @param string                  $name        name
     *
     * @return Commit
     */
    public static function createFromOutputLines(Repository $repository, $outputLines, $name)
    {
        $tag = new self($repository, $name);
        $tag->parseOutputLines($outputLines);

        return $tag;
    }

    /**
     * Class constructor
     *
     * @param \GitElephant\Repository $repository repository instance
     * @param string                  $name       name
     *
     * @internal param string $line a single tag line from the git binary
     */
    public function __construct(Repository $repository, $name)
    {
        $this->repository = $repository;
        $this->name    = $name;
        $this->fullRef = 'refs/tags/' . $this->name;
        $this->createFromCommand();
    }

    /**
     * factory method
     *
     * @param \GitElephant\Repository $repository repository instance
     * @param string                  $name       name
     *
     * @return TreeTag
     */
    public static function pick(Repository $repository, $name)
    {
        return new self($repository, $name);
    }

    /**
     * get the commit properties from command
     *
     * @see ShowCommand::commitInfo
     */
    private function createFromCommand()
    {
        $command = TagCommand::getInstance()->lists();
        $outputLines = $this->getCaller()->execute($command, true, $this->getRepository()->getPath())->getOutputLines();
        $this->parseOutputLines($outputLines);
    }

    /**
     * parse the output of a git command showing a commit
     *
     * @param array $outputLines output lines
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function parseOutputLines($outputLines)
    {
        $found = false;
        foreach ($outputLines as $tagString) {
            if ($tagString != '') {
                if ($this->name === trim($tagString)) {
                    $lines = $this->getCaller()->execute(RevListCommand::getInstance()->getTagCommit($this))->getOutputLines();
                    $this->setSha($lines[0]);
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            throw new \InvalidArgumentException(sprintf('the tag %s doesn\'t exists', $this->name));
        }
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
     * @return \GitElephant\Command\Caller
     */
    private function getCaller()
    {
        return $this->getRepository()->getCaller();
    }

    /**
     * Repository setter
     *
     * @param \GitElephant\Repository $repository the repository variable
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
     * name getter
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

    /**
     * sha setter
     *
     * @param string $sha sha
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
}
