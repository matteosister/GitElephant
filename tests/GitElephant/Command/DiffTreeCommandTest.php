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

use GitElephant\Command\DiffTreeCommand,
    GitElephant\TestCase,
    GitElephant\Objects\Commit;

/**
 * DiffTreeCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffTreeCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\DiffTreeCommand;
     */
    private $diffTreeCommand;

    /**
     * set up
     */
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('first commit', true);
        $this->diffTreeCommand = new DiffTreeCommand();
    }

    /**
     * set up
     */
    public function testRootDiff()
    {
        $commit = $this->getRepository()->getCommit();
        $command = $this->diffTreeCommand->rootDiff($commit);
        $this->assertEquals(
            sprintf("diff-tree '--cc' '--root' '--dst-prefix=DST/' '--src-prefix=SRC/' '%s'", $commit),
            $command
        );
    }
}
