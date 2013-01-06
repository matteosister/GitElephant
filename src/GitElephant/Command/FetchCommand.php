<?php
/**
 * User: matteo
 * Date: 05/01/13
 * Time: 0.37
 * 
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;

/**
 * Log command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class FetchCommand extends BaseCommand
{
    const GIT_FETCH = 'fetch';

    /**
     * @return FetchCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * fetch from a remote
     *
     * @param string $remote remote
     *
     * @return string
     */
    public function fetch($remote = 'origin')
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_FETCH);
        $this->addCommandSubject($remote);

        return $this->getCommand();
    }

    /**
     * fetch all remotes
     *
     * @return string
     */
    public function fetchAll()
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_FETCH);
        $this->addCommandArgument('--all');

        return $this->getCommand();
    }
}
