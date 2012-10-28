<?php
/**
 * User: matteo
 * Date: 27/10/12
 * Time: 0.44
 *
 * Just for fun...
 */

namespace GitElephant\Command;

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
     * @return array
     */
    function getOutputLines($stripBlankLines = false);
}
