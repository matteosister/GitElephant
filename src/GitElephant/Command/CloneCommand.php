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

use GitElephant\Command\BaseCommand;

/**
 * CloneCommand
 *
 * @todo   : description
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class CloneCommand extends BaseCommand
{
    const GIT_CLONE_COMMAND = 'clone';

    /**
     * @return CloneCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * Command to clone a repository
     *
     * @param string $url repository url
     *
     * @return string command
     */
    public function cloneUrl($url)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_CLONE_COMMAND);
        $this->addCommandSubject($url.' .');

        return $this->getCommand();
    }
}
