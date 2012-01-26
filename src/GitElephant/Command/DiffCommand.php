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
use GitElephant\Objects\Commit;
use GitElephant\Objects\TreeishInterface;

/**
 * Diff command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffCommand extends BaseCommand
{
    const DIFF_COMMAND = 'diff';

    /**
     * build a diff command
     *
     * @param TreeishInterface      $of   the reference to diff
     * @param TreeishInterface|null $with the source reference to diff with $of, if not specified is the current HEAD
     * @param null                  $path the path to diff, if not specified the full repository
     *
     * @return string
     */
    public function diff($of, $with = null, $path = null)
    {
        $this->clearAll();
        $this->addCommandName(self::DIFF_COMMAND);
        $this->addCommandArgument('--full-index');
        $this->addCommandArgument('--no-color');
        // no whitespaces
        $this->addCommandArgument('-w');
        // detect renames
        $this->addCommandArgument('-M');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');

        $subject = '';

        if ($with == null) {
            $subject .= $of.'^..'.$of;
        } else {
            $subject .= $with.'..'.$of;
        }

        if ($path != null) {
            if (is_string($path)) {
                $subject .= ' -- ' . $path;
            } else {
                $subject .= ' -- ' . $path->getPath();
            }
        }

        $this->addCommandSubject($subject);
        return $this->getCommand();
    }
}
