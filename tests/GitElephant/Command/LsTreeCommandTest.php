<?php

declare(strict_types=1);

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
 * LsTreeCommandTest
 *
 * LsTreeCommand class tests
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\GitElephant\Command\LsTreeCommand::class, 'tree')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\GitElephant\Command\LsTreeCommand::class, 'listAll')]
final class LsTreeCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\LsTreeCommand
     */
    private $lsTreeCommand;

    /**
     * setUp function
     */
    public function setUp(): void
    {
        $this->lsTreeCommand = new LsTreeCommand();
    }

    /**
     * fullTree test
     */
    public function testFullTree(): void
    {
        $this->assertEquals("ls-tree '-r' '-t' '-l' 'HEAD'", $this->lsTreeCommand->fullTree(), 'ls-tree command test');
    }

    /**
     * tree test
     */
    public function testTree(): void
    {
        $this->assertEquals("ls-tree '-l' 'HEAD'", $this->lsTreeCommand->tree(), 'ls-tree command test');
    }

    /**
     * listAll test
     */
    public function testListAll(): void
    {
        $this->assertEquals("ls-tree 'HEAD'", $this->lsTreeCommand->listAll(), 'ls-tree command test');
    }
}
