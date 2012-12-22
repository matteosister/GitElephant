<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Command\CloneCommand,
    GitElephant\TestCase,
    GitElephant\Objects\Commit;

/**
 * CloneCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CloneCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\CloneCommand;
     */
    private $cloneCommand;

    /**
     * set up
     */
    public function setUp()
    {
        $this->initRepository();
        $this->cloneCommand = new CloneCommand();
    }

    /**
     * set up
     */
    public function testClone()
    {
        $command = $this->cloneCommand->cloneUrl('git://github.com/matteosister/GitElephant.git');
        $this->assertEquals('clone git://github.com/matteosister/GitElephant.git', $command);
    }
}
