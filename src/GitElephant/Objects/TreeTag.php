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
 * An object representing a git tag
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeTag implements TreeishInterface
{
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
     * Class constructor
     *
     * @param string $line a single tag line from the git binary
     */
    public function __construct($line)
    {
        $this->name    = trim($line);
        $this->fullRef = 'refs/tags/' . $this->name;
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
