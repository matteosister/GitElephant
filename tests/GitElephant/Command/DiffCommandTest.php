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

use GitElephant\TestCase;

/**
 * DiffCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\DiffCommand
     */
    private $diffCommand;

    /**
     * set up
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('foo');
        $this->getRepository()->commit('first commit', true);
        $this->diffCommand = new DiffCommand();
    }

    /**
     * diff test
     */
    public function testDiff(): void
    {
        $commit = $this->getRepository()->init(false, 'master')->getCommit();
        $this->assertEquals(
            DiffCommand::DIFF_COMMAND . " '--full-index' '--no-color' '--no-ext-diff' '-M' '--dst-prefix=DST/' '--src-prefix=SRC/' 'HEAD^..HEAD'",
            $this->diffCommand->diff('HEAD')
        );
        $this->assertEquals(
            DiffCommand::DIFF_COMMAND . " '--full-index' '--no-color' '--no-ext-diff' '-M' '--dst-prefix=DST/' '--src-prefix=SRC/' 'branch2..HEAD' -- 'foo'",
            $this->diffCommand->diff('HEAD', 'branch2', 'foo')
        );
        $this->assertEquals(
            sprintf(
                DiffCommand::DIFF_COMMAND . " '--full-index' '--no-color' '--no-ext-diff' '-M' '--dst-prefix=DST/' '--src-prefix=SRC/' '%s^..%s'",
                $commit->getSha(),
                $commit->getSha()
            ),
            $this->diffCommand->diff($commit)
        );
    }
}
