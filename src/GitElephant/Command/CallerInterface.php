<?php

namespace GitElephant\Command;

/**
 * interface for the git command caller
 */
interface CallerInterface
{
    /**
     * execute a command
     *
     * @param string      $cmd the command
     * @param bool        $git prepend git to the command
     * @param null|string $cwd directory where the command should be executed
     *
     * @return CallerInterface
     */
    function execute($cmd, $git = true, $cwd = null);

    /**
     * after calling execute this method should return the output
     *
     * @param bool $stripBlankLines strips the blank lines
     *
     * @return array
     */
    function getOutputLines($stripBlankLines = false);
}