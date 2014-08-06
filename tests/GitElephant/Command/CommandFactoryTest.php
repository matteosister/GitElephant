<?php
/**
 * Created by PhpStorm.
 * User: matteo
 * Date: 06/08/14
 * Time: 22.51
 */

namespace GitElephant\Command;

use GitElephant\TestCase;

class CommandFactoryTest extends TestCase
{
    /**
     * @var CommandFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = CommandFactory::create(array());
    }

    /**
     * @dataProvider camelizeProvider
     */
    public function test_camelize($expected, $name)
    {
        $camelize = $this->getPrivateOrProtectedMethod($this->factory, 'camelize');
        $this->assertEquals($expected, $camelize->invoke($this->factory, $name));
    }

    public function camelizeProvider()
    {
        return array(
            array('GitElephant\Command\CloneCommand', 'clone'),
            array('GitElephant\Command\CatFileCommand', 'cat_file'),
            array('GitElephant\Command\SubCommandCommand', 'sub_command'),
            array('GitElephant\Command\Remote\AddSubCommand', 'remote.add_sub'),
            array('GitElephant\Command\Test\Remote\AddSubCommand', 'test.remote.add_sub'),
        );
    }

    public function test_get()
    {
        $this->assertInstanceOf('GitElephant\Command\PullCommand', $this->factory->get('pull'));
        $this->assertInstanceOf('GitElephant\Command\CloneCommand', $this->factory->get('clone'));
        $this->assertInstanceOf('GitElephant\Command\Remote\AddSubCommand', $this->factory->get('remote.add_sub'));
    }
} 