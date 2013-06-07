<?php
/**
 * User: matteo
 * Date: 06/06/13
 * Time: 23.34
 * Just for fun...
 */


namespace GitElephant\Command;

use GitElephant\Objects\Object;

/**
 * Class MvCommand
 *
 * @package GitElephant\Command
 */
class MvCommand extends BaseCommand
{
    const MV_COMMAND = 'mv';

    /**
     * @return MvCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * @param string|Object $source source name
     * @param string        $target dest name
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function rename($source, $target)
    {
        if ($source instanceof Object) {
            if (!$source->isBlob()) {
                throw new \InvalidArgumentException('The given object is not a blob, it couldn\'t be renamed');
            }
            $sourceName = $source->getFullPath();
        } else {
            $sourceName = $source;
        }
        $this->clearAll();
        $this->addCommandName(self::MV_COMMAND);
        // Skip move or rename actions which would lead to an error condition
        $this->addCommandArgument('-k');
        $this->addCommandSubject($sourceName);
        $this->addCommandSubject2($target);

        return $this->getCommand();
    }
}