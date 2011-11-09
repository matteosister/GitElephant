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

namespace GitWrapper\Command;

use GitWrapper\GitBinary;

/**
 * Caller
 *
 * Caller Class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class Caller
{
    private $binary;
    private $repositoryPath;
    private $result;

    public function __construct(GitBinary $binary, $repositoryPath)
    {
        $this->binary = $binary;
        $this->repositoryPath = $repositoryPath;
    }

    public function getBinaryPath()
    {
        return $this->binary->getPath();
    }

    public function execute($cmd)
    {
        $cmd = $this->binary->getPath().' '.$cmd;
        var_dump($cmd);
        var_dump($this->repositoryPath);

        $stdErr = fopen('php://temp', 'r');
        $stdOut = fopen('php://temp', 'r');

        $descriptorSpec = array(
           0 => array("pipe", "r"), // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),            // stdout is a temp file that the child will write to
           2 => $stdErr             // stderr is a temp file that the child will write to
        );

        $pipes = array();
        $process = proc_open(
            $cmd,
            $descriptorSpec,
            $pipes,
            $this->repositoryPath,
            $_ENV
        );

        if (is_resource($process)) {
            fclose($pipes[0]);
            $this->result = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        } else {
            fclose($stdOut);
            fclose($stdErr);
            throw new \RuntimeException(sprintf('Cannot execute "%s"', $this->getCommand()));
        }
    }

    public function getResult()
    {
        return $this->result;
    }
}
