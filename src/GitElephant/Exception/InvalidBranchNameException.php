<?php

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