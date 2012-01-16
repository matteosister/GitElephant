<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant
 *
 * Just for fun...
 */

namespace GitElephant;

/**
 * Git binary
 *
 * It contains the reference to the system git binary
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class GitBinary
{
    /**
     * the path to the repository
     *
     * @var string $path
     */
    private $path;

    /**
     * Class constructor
     *
     * @param null $path the physical path to the git binary
     */
    public function __construct($path = null)
    {
        if ($path == null) {
            // unix only!
            $path = exec('which git');
        }
        $this->setPath($path);
    }

    /**
     * path getter
     * returns the path of the binary
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * path setter
     *
     * @param string $path the path to the system git binary
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}
