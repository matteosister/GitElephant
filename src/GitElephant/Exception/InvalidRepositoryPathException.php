<?php
/**
 * User: matteo
 * Date: 02/06/13
 * Time: 21.58
 * Just for fun...
 */


namespace GitElephant\Exception;

/**
 * Class InvalidRepositoryPathException
 *
 * @package GitElephant\Exception
 */
class InvalidRepositoryPathException extends \Exception
{
    protected $messageTpl = 'The path provided (%s) is not a valid git repository path';

    /**
     * @param string     $message  repository path
     * @param int        $code     code
     * @param \Exception $previous previous
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf($this->messageTpl, $message), $code, $previous);
    }
}