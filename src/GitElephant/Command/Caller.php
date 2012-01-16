<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Command
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\GitBinary;

/**
 * Caller
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Caller
{
    /**
     * GitBinary instance
     *
     * @var \GitElephant\GitBinary
     */
    private $binary;

    /**
     * the repository path
     *
     * @var string
     */
    private $repositoryPath;

    /**
     * error stream from the command
     *
     * @var string
     */
    private $stdErr;

    /**
     * the output lines of the command
     *
     * @var array
     */
    private $outputLines = array();

    /**
     * Class constructor
     *
     * @param \GitElephant\GitBinary $binary         the binary
     * @param string                 $repositoryPath the physical base path for the repository
     */
    public function __construct(GitBinary $binary, $repositoryPath)
    {
        $this->binary         = $binary;
        $this->repositoryPath = $repositoryPath;
    }

    /**
     * Get the binary path
     *
     * @return mixed
     */
    public function getBinaryPath()
    {
        return $this->binary->getPath();
    }

    /**
     * Executes a command
     *
     * @param string $cmd the command to execute
     * @param bool   $git if the command is git or a generic command
     * @param null   $cwd the directory where the command must be executed
     *
     * @return Caller
     * @throws \RuntimeException
     */
    public function execute($cmd, $git = true, $cwd = null)
    {
        $this->outputLines = array();
        if ($git) {
            $cmd = $this->binary->getPath() . ' ' . $cmd;
        }

        $descriptorSpec = array(
            0 => array("pipe", "r"), // Input
            1 => array("pipe", "w"), // Output
            2 => array("pipe", "w") // Error
        );

        $pipes   = array();
        $process = proc_open(
            $cmd,
            $descriptorSpec,
            $pipes,
            $cwd == null ? $this->repositoryPath : $cwd,
            null
        );

        if (is_resource($process)) {
            fclose($pipes[0]);
            while ($line = fgets($pipes[1])) {
                if ($line !== false) {
                    $this->outputLines[] = rtrim($line);
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

    /**
     * returns the error output of the last executed command
     *
     * @return bool|string
     */
    public function getError()
    {
        return $this->stdErr == '' ? false : trim($this->stdErr);
    }

    /**
     * returns the raw output of the last executed command
     *
     * @return string
     */
    public function getOutput()
    {
        return implode(" ", $this->outputLines);
    }

    /**
     * returns the output of the last executed command as an array of lines
     *
     * @return array
     */
    public function getOutputLines()
    {
        return $this->outputLines;
    }
}
