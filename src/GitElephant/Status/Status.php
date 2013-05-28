<?php
/**
 * User: matteo
 * Date: 28/05/13
 * Time: 21.34
 * Just for fun...
 */

namespace GitElephant\Status;

use GitElephant\Command\MainCommand;
use GitElephant\Repository;

/**
 * Class Status
 *
 * @package GitElephant\Status
 */
class Status
{
    private $repository;

    private $files;

    /**
     * @param Repository $repository
     */
    private function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->createFromCommand();
    }

    /**
     * @param Repository $repository
     *
     * @return Status
     */
    public static function get(Repository $repository)
    {
        return new self($repository);
    }

    /**
     * create from git command
     */
    private function createFromCommand()
    {
        $command = MainCommand::getInstance()->status(true);
        $lines = $this->repository->getCaller()->execute($command)->getOutputLines(true);
        $this->parseOutputLines($lines);
    }

    /**
     * create objects from command output
     * https://www.kernel.org/pub/software/scm/git/docs/git-status.html in the output section
     *
     *
     * @param array $lines
     */
    private function parseOutputLines($lines)
    {
        foreach ($lines as $line) {
            preg_match('/([MADRCU\?])?([MADRCU\?])?\ "?(\S+)"? ?( -> )?(\S+)?/', $line, $matches);
            $x = $matches[1];
            $y = $matches[2];
            $file = $matches[3];
            $this->files[] = StatusFile::create($x, $y, $file);
        }
    }

    /**
     * @param string $line
     *
     * @return mixed
     */
    protected function splitStatusLine($line)
    {
        preg_match('/([MADRCU\?])?([MADRCU\?])?\ "?(\S+)"? ?( -> )?(\S+)?/', $line, $matches);

        return $matches;
    }

    /**
     * added files
     *
     * @return array
     */
    public function untracked()
    {
        return array_filter($this->files, function(StatusFile $statusFile) {
            return StatusFile::UNTRACKED === $statusFile->getType();
        });
    }
}