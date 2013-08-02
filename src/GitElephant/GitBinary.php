<?php

namespace GitElephant;

/**
 * Git binary
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
