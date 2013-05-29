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
                case self::ADDED:
                    $this->type = self::ADDED;
                    break;
            }
        }

        return $this->type;
    }

    /**
     * description of the status
     *
     * @return string
     */
    public function getDescription()
    {
        $status = $this->x.$this->y;
        $matching = array(
            '/ [MD]/' => 'not updated',
            '/M[ MD]/' => 'updated in index',
            '/A[ MD]/' => 'added to index',
            '/D[ M]/' => 'deleted from index',
            '/R[ MD]/' => 'renamed in index',
            '/C[ MD]/' => 'copied in index',
            '/[MARC] /' => 'index and work tree matches',
            '/[ MARC]M/' => 'work tree changed since index',
            '/[ MARC]D/' => 'deleted in work tree',
            '/DD/' => 'unmerged, both deleted',
            '/AU/' => 'unmerged, added by us',
            '/UD/' => 'unmerged, deleted by them',
            '/UA/' => 'unmerged, added by them',
            '/DU/' => 'unmerged, deleted by us',
            '/AA/' => 'unmerged, both added',
            '/UU/' => 'unmerged, both modified',
            '/??/' => 'untracked',
            '/!!/' => 'ignored',
        );
        $out = array();
        foreach ($matching as $pattern => $label) {
            if (preg_match(preg_quote($pattern), $status)) {
                $out[] = $label;
            }
        }

        return implode(', ', $out);
    }
}