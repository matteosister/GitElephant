<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Repository;
use GitElephant\Command\BaseCommand;
use GitElephant\Command\Remote\AddSubCommand;
use GitElephant\Command\Remote\ShowSubCommand;

/**
 * Class RemoteCommand
 * 
 * remote command generator
 *
 * @package GitElephant\Objects
 * @author  David Neimeyer <davidneimeyer@gmail.com>
 */

class RemoteCommand extends BaseCommand
{
    const GIT_REMOTE = 'remote';
    const GIT_REMOTE_OPTION_VERBOSE = '--verbose';
    const GIT_REMOTE_OPTION_VERBOSE_SHORT = '-v';

    /**
     * Fetch an instance of RemoteCommand object
     * 
     * @param \GitElephant\Repository $repository Optional repository object to inject
     * 
     * @return RemoteCommand
     */
    public static function getInstance(Repository $repository = null)
    {
        return new self();
    }

    /**
     * Build the remote command
     * 
     * NOTE: git-remote is most useful when using its subcommands, therefore
     * in practice you will likely pass a SubCommandCommand object. This
     * class provide "convenience" methods that do this for you.
     * 
     * @param \GitElephant\Command\SubCommandCommand $subcommand A subcommand object
     * @param array                                  $options    Options for the main git-remote command
     * 
     * @return string Command string to pass to caller
     */
    public function remote(SubCommandCommand $subcommand = null, Array $options = array())
    {
        $normalizedOptions = $this->normalizeOptions($options, $this->remoteCmdSwitchOptions());

        $this->clearAll();

        $this->addCommandName(self::GIT_REMOTE);

        foreach ($normalizedOptions as $key => $value) {
            $this->addCommandArgument($value);
        }

        if ($subcommand) {
            $this->addCommandSubject($subcommand);
        }

        return $this->getCommand();
    }

    /**
     * Valid options for remote command that do not require an associated value
     * 
     * @return array Associative array mapping all non-value options and their respective normalized option
     */
    public function remoteCmdSwitchOptions()
    {
        return array(
            self::GIT_REMOTE_OPTION_VERBOSE => self::GIT_REMOTE_OPTION_VERBOSE,
            self::GIT_REMOTE_OPTION_VERBOSE_SHORT => self::GIT_REMOTE_OPTION_VERBOSE,
        );
    }

    /**
     * git-remote --verbose command
     * 
     * @return string
     */
    public function verbose()
    {
        return $this->remote(null, array(self::GIT_REMOTE_OPTION_VERBOSE));
    }

    /**
     * git-remote show [name] command
     * 
     * NOTE: for technical reasons $name is optional, however under normal
     * implementation it SHOULD be passed!
     * 
     * @param string $name
     * 
     * @return string
     */
    public function show($name = null)
    {
        $subcmd = new ShowSubCommand();
        $subcmd->prepare($name);

        return $this->remote($subcmd);
    }

    /**
     * git-remote add [options] <name> <url>
     * 
     * @param string $name    remote name
     * @param string $url     URL of remote
     * @param array  $options options for the add subcommand
     * 
     * @return string
     */
    public function add($name, $url, $options = array())
    {
        $subcmd = new AddSubCommand();
        $subcmd->prepare($name, $url, $options);

        return $this->remote($subcmd);
    }
}
