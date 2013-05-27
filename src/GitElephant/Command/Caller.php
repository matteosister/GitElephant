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
use GitElephant\Command\CallerInterface;
use Symfony\Component\Process\Process;


/**
 * Caller
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Caller implements CallerInterface
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
     * the output lines of the command
     *
     * @var array
     */
    private $outputLines = array();

    /**
     * raw output
     *
     * @var string
     */
    private $rawOutput;

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

        $process = new Process($cmd, $cwd == null ? $this->repositoryPath : $cwd);
        $process->setTimeout(15000);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getOutput());
        }
        $this->rawOutput = $process->getOutput();
        // rtrim values
        $values = array_map('rtrim', explode(PHP_EOL, $process->getOutput()));
        $this->outputLines = $values;

        return $this;
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
     * @param bool $stripBlankLines remove the blank lines
     *
     * @return array
     */
    public function getOutputLines($stripBlankLines = false)
    {
        if ($stripBlankLines) {
            $output = array();
            foreach ($this->outputLines as $line) {
                if ('' !== $line) {
                    $output[] = $line;
                }
            }

            return $output;
        }

        return $this->outputLines;
    }

    /**
     * Get RawOutput
     *
     * @return string
     */
    public function getRawOutput()
    {
        return $this->rawOutput;
    }
}
