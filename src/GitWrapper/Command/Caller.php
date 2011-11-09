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
    private $stdErr;
    private $outputLines;

    public function __construct(GitBinary $binary, $repositoryPath)
    {
        $this->binary = $binary;
        $this->repositoryPath = $repositoryPath;
    }

    public function getBinaryPath()
    {
        return $this->binary->getPath();
    }

    public function execute($cmd, $git = true)
    {
        $this->outputLines = array();
        if ($git) {
            $cmd = $this->binary->getPath().' '.$cmd;
        }

        $descriptorSpec = array(
           0 => array("pipe", "r"), // Input
           1 => array("pipe", "w"), // Output
           2 => array("pipe", "w")  // Error
        );

        $pipes = array();
        $process = proc_open(
            $cmd,
            $descriptorSpec,
            $pipes,
            $this->repositoryPath,
            null
        );

        if (is_resource($process)) {
            fclose($pipes[0]);
            while ($line = fgets($pipes[1])) {
                if ($line !== FALSE) {
                    $this->outputLines[] = trim($line);
                }
            }
            $this->stdErr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            if ($this->getError() !== false) {
                throw new \RuntimeException(sprintf('Cannot execute "%s", message: "%s", folder: "%s"', $cmd, $this->getError(), $this->repositoryPath));
            }
            return $this;
        } else {
            fclose($pipes[1]);
            fclose($pipes[2]);
            throw new \RuntimeException(sprintf('Cannot execute "%s"', $cmd));
        }
    }

    public function getError()
    {
        return $this->stdErr == '' ? false : trim($this->stdErr);
    }

    public function getOutput()
    {
        return implode(" ", $this->outputLines);
    }

    public function getOutputLines()
    {
        return $this->outputLines;
    }
}
