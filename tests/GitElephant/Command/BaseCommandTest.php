<?php
/**
 * User: matteo
 * Date: 20/05/13
 * Time: 21.47
 * Just for fun...
 */


namespace GitElephant\Command;


use GitElephant\TestCase;

/**
 * Class BaseCommandTest
 *
 * @package GitElephant\Command
 */
class BaseCommandTest extends TestCase
{
    /**
     * testGetCommand
     */
    public function testGetCommand()
    {
        $bc = new BaseCommand();
        $this->setExpectedException('RuntimeException');
        $this->fail($bc->getCommand());
    }
}