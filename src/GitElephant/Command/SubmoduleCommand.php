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
 * Submodule command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class SubmoduleCommand extends BaseCommand
{
    const SUBMODULE_COMMAND = 'submodule';
    const SUBMODULE_ADD_COMMAND = 'add';

    /**
     * @return SubmoduleCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * add a submodule
     *
     * @param string $gitUrl git url of the submodule
     * @param string $path   path to register the submodule to
     *
     * @return string
     */
    public function add($gitUrl, $path = null)
    {
        $this->clearAll();
        $this->addCommandName(sprintf('%s %s', self::SUBMODULE_COMMAND, self::SUBMODULE_ADD_COMMAND));
        $this->addCommandArgument($gitUrl);
        if (null !== $path) {
            $this->addPath($path);
        }

        return $this->getCommand();
    }

    /**
     * Lists tags
     *
     * @return string the command
     */
    public function lists()
    {
        $this->clearAll();
        $this->addCommandName(self::SUBMODULE_COMMAND);

        return $this->getCommand();
    }
}
