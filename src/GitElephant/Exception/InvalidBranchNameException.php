<?php
/**
 * User: matteo
 * Date: 01/06/13
 * Time: 0.00
 * Just for fun...
 */


namespace GitElephant\Exception;

/**
 * Class InvalidBranchNameException
 *
 * @package GitElephant\Exception
 */
class InvalidBranchNameException extends \Exception
{
    protected $message = 'The name provided is not a valid branch name';
}