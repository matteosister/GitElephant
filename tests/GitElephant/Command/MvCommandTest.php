<?php
/**
 * User: matteo
 * Date: 06/06/13
 * Time: 23.45
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\TestCase;

/**
 * Class MvCommandTest
 *
 * @package GitElephant\Command
 */
class MvCommandTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->addFolder('test_folder');
        $this->addFile('test2', 'test_folder');
        $this->getRepository()->commit('test', true);
    }

    /**
     * testRename
     */
    public function testRename()
    {
        /** @var MvCommand $cmdInstance */
        $cmdInstance = $this->getRepository()->getCommandFactory()->get('mv');
        $this->assertEquals("mv '-k' 'a' 'b'", $cmdInstance->rename('a', 'b'));
        $tree = $this->repository->getTree('HEAD', 'test');
        $this->assertEquals("mv '-k' 'test' 'b'", $cmdInstance->rename($tree->getBlob(), 'b'));
        $tree = $this->repository->getTree('HEAD', 'test_folder/test2');
        $this->assertEquals("mv '-k' 'test_folder/test2' 'b'", $cmdInstance->rename($tree->getBlob(), 'b'));
    }
}
