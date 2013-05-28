<?php
/**
 * User: matteo
 * Date: 28/05/13
 * Time: 21.37
 * Just for fun...
 */


namespace GitElephant\Status;

/**
 * Class StatusFile
 *
 * @package GitElephant\Status
 */
class StatusFile
{
    const UNTRACKED = '?';
    const IGNORED = '!';
    const UNMODIFIED = '';
    const MODIFIED = 'M';
    const ADDED = 'A';
    const DELETED = 'D';
    const RENAMED = 'R';
    const COPIED = 'C';
    const UPDATED_BUT_UNMERGED = 'U';

    /**
     * @var string
     */
    private $x;

    /**
     * @var string
     */
    private $y;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $x    X section of the status --porcelain output
     * @param string $y    Y section of the status --porcelain output
     * @param string $name file name
     */
    private function __construct($x, $y, $name)
    {
        $this->x = $x;
        $this->y = $y;
        $this->name = $name;
    }

    /**
     * @param string $x    X section of the status --porcelain output
     * @param string $y    Y section of the status --porcelain output
     * @param string $name file name
     *
     * @return StatusFile
     */
    public static function create($x, $y, $name)
    {
        return new self($x, $y, $name);
    }

    /**
     * Get X
     *
     * @return string
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Get Y
     *
     * @return string
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        if (null === $this->type) {
            switch ($this->x) {
                case self::UNTRACKED:
                    $this->type = self::UNTRACKED;
                    break;
            }
        }

        return $this->type;
    }
}