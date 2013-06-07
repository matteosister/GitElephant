<?php
/**
 * User: matteo
 * Date: 29/05/13
 * Time: 23.13
 * Just for fun...
 */


namespace GitElephant\Status;

/**
 * Class StatusCollection
 *
 * @package GitElephant\Status
 */
class StatusFileCollection implements \Countable
{
    private $files;

    /**
     * class constructor
     *
     * @param array $files
     */
    private function __construct($files = array())
    {
        $this->files = $files;
    }

    /**
     * @param array $files
     *
     * @return StatusFileCollection
     */
    public static function create($files = array())
    {
        return new self($files);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->files;
    }

    /**
     * @return StatusFile
     */
    public function first()
    {
        if (0 === count($this->files)) {
            return null;
        }

        return $this->files[0];
    }

    /**
     * @return StatusFile
     */
    public function last()
    {
        if (0 === count($this->files)) {
            return null;
        }

        return $this->files[count($this->files) - 1];
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->files);
    }
}