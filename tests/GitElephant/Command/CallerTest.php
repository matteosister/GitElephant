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

use GitElephant\Command\Caller\Caller;
use GitElephant\TestCase;

/**
 * CallerTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class CallerTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->initRepository();
    }

    /**
     * @covers GitElephant\Command\Caller\Caller::__construct
     */
    public function testConstructor(): void
    {
        $caller = new Caller(null, $this->getRepository()->getPath());
        $this->assertNotEmpty($caller->execute('--version'));
    }

    /**
     * testGetBinaryPath
     */
    public function testGetBinaryPath(): void
    {
        $c = new Caller(null, $this->repository->getPath());
        $this->assertEquals(exec('which git'), $c->getBinaryPath());
    }

    /**
     * testGetBinaryVersion
     */
    public function testGetBinaryVersion(): void
    {
        $c = new Caller(null, $this->repository->getPath());
        $this->assertIsString($c->getBinaryVersion());
    }

    /**
     * testGetError
     */
    public function testGetError(): void
    {
        $this->expectException(\RuntimeException::class);
        $caller = new Caller(null, $this->getRepository()->getPath());
        $caller->execute('foo');
    }

    /**
     * get output test
     */
    public function testGetOutput(): void
    {
        $caller = new Caller(null, $this->getRepository()->getPath());
        $mainCommand = new MainCommand();
        $caller->execute($mainCommand->init());
        $this->myAssertMatchesRegularExpression(
            sprintf('/^(.*)%s/', str_replace('/', '\/', $this->getRepository()->getPath())),
            $caller->getOutput()
        );
    }

    /**
     * testOutputLines
     */
    public function testOutputLines(): void
    {
        $caller = new Caller(null, $this->getRepository()->getPath());
        $this->getRepository()->init(false, 'master');
        for ($i = 1; $i <= 50; $i++) {
            $this->addFile('test' . $i, null, 'this is the content');
        }
        $this->getRepository()->commit('first commit', true);
        $command = new LsTreeCommand();
        $outputLines = $caller->execute($command->fullTree($this->getRepository()->getMainBranch()))->getOutputLines();
        $this->assertTrue(is_array($outputLines));
        $this->assertEquals(range(0, count($outputLines) - 1), array_keys($outputLines));
    }

    /**
     * testGetRawOutput
     */
    public function testGetRawOutput(): void
    {
        $this->getRepository()->init(false, 'master');
        $caller = new Caller(null, $this->getRepository()->getPath());
        $caller->execute('status');
        $this->myAssertMatchesRegularExpression('/master|main/', $caller->getRawOutput());
    }

    /**
     * testRepositoryValidation
     */
    public function testRepositoryValidation(): void
    {
        $this->expectException(\GitElephant\Exception\InvalidRepositoryPathException::class);
        new Caller(null, 'someinvalidpath');
    }
}
