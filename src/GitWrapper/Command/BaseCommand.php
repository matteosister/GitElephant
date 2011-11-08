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
 * BaseCommand
 *
 * The base class for all the git commands wrapper
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class BaseCommand
{
    protected  $binary;
    private $commandName;
    private $arguments = array();
    private $subject;

    private $stdOut;

    public function __construct(GitBinary $binary)
    {
        $this->binary = $binary;
    }

    public static function create($cmd, $cwd = null, array $env = null) {
        return new static($cmd, $cwd, $env);
    }

    protected function addCommandName($commandName)
    {
        $this->commandName = $commandName;
    }

    protected function addArgument($arg)
    {
        $this->arguments[] = $arg;
    }

    protected function addSubject($subject)
    {
        $this->subject = $subject;
    }

    private function getCommand()
    {
        if ($this->commandName == null) {
            throw new \InvalidParameterException("You should pass a commandName to execute a command");
        }
        
        return $this->binary->getPath()
               .' '.$this->commandName
               .' '.implode(' ', array_map('escapeshellarg', $this->arguments))
               .' '.$this->subject;
    }

    public function execute($repositoryPath, $stdIn = null)
    {
        $stdOut = fopen('php://temp', 'r');
        $stdErr = fopen('php://temp', 'r');

        $descriptorSpec = array(
           0 => array("pipe", "r"), // stdin is a pipe that the child will read from
           1 => $stdOut,            // stdout is a temp file that the child will write to
           2 => $stdErr             // stderr is a temp file that the child will write to
        );
        $pipes   = array();

        $process = proc_open(
            $this->getCommand(),
            $descriptorSpec,
            $pipes,
            $repositoryPath,
            null
        );

        if (is_resource($process)) {
            if ($stdIn !== null) {
                fwrite($pipes[0], (string)$stdIn);
            }
            fclose($pipes[0]);

            $returnCode = proc_close($process);
            fseek($stdOut, 0);
            $this->stdOut = $stdOut;
        } else {
            fclose($stdOut);
            fclose($stdErr);
            throw new \RuntimeException(sprintf('Cannot execute "%s"', $cmd));
        }
    }

    public function getStdOut()
    {
        return $this->stdOut;
    }
}
