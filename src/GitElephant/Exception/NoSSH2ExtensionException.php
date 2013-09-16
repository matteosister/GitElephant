<?php
/**
 * User: matteo
 * Date: 17/09/13
 * Time: 0.10
 * Just for fun...
 */


namespace GitElephant\Exception;

/**
 * Class NoSSH2ExtensionException
 */
class NoSSH2ExtensionException extends \Exception
{
    protected $message = 'It seems like you don\t have the SSH2 pecl extension enabled.';
} 