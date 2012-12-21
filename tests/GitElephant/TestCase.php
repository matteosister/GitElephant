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

namespace GitElephant;

use GitElephant\Repository;
use GitElephant\GitBinary;
use GitElephant\Command\Caller;
use GitElephant\Objects\Commit;
use Symfony\Component\Finder\Finder;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \GitElephant\Command\CallerInterface
     */
    protected $caller;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @return \GitElephant\Repository
     */
    protected function getRepository()
    {
        if ($this->repository == null) {
            $this->initRepository();
        }

        return $this->repository;
    }

    /**
     * @return \GitElephant\Command\Caller
     */
    protected function getCaller()
    {
        if ($this->caller == null) {
            $this->initRepository();
        }

        return $this->caller;
    }

    /**
     * @param null|string $name the folder name
     *
     * @return void
     */
    protected function initRepository()
    {
        if ($this->repository == null) {
            $tempDir = realpath(sys_get_temp_dir()).'gitelephant_'.md5(uniqid(rand(), 1));
            $tempName = tempnam($tempDir, 'gitelephant');
            $this->path = $tempName;
            unlink($this->path);
            mkdir($this->path);
            $binary = new GitBinary();
            $this->caller = new Caller($binary, $this->path);
            $this->repository = new Repository($this->path);
        }
    }

    /**
     * @param string      $name    file name
     * @param string|null $folder  folder name
     * @param null        $content content
     *
     * @return void
     */
    protected function addFile($name, $folder = null, $content = null)
    {
        $filename = $folder == null ?
                $this->path.DIRECTORY_SEPARATOR.$name :
                $this->path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$name;
        $handle = fopen($filename, 'w');
        $fileContent = $content == null ? 'test content' : $content;
        fwrite($handle, $fileContent);
        fclose($handle);
    }

    /**
     * @param string $name name
     *
     * @return void
     */
    protected function addFolder($name)
    {
        mkdir($this->path.DIRECTORY_SEPARATOR.$name);
    }

    /**
     * mock the caller
     *
     * @param string $command command
     * @param string $output  output
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCaller($command, $output)
    {
        $mock = $this->getMock('GitElephant\Command\CallerInterface');
        $mock
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mock));
        $mock
            ->expects($this->any())
            ->method('getOutputLines')
            ->will($this->returnValue($output));

        return $mock;
    }

    protected function getMockContainer()
    {
        return $this->getMock('GitElephant\Command\CommandContainer');
    }

    protected function addCommandToMockContainer(\PHPUnit_Framework_MockObject_MockObject $container, $commandName)
    {
        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($commandName))
            ->will($this->returnValue($this->getMockCommand()));
    }

    protected function addOutputToMockRepo(\PHPUnit_Framework_MockObject_MockObject $repo, $output)
    {
        $repo
            ->expects($this->any())
            ->method('getCaller')
            ->will($this->returnValue($this->getMockCaller('', $output)));
    }

    protected function getMockCommand()
    {
        $command = $this->getMock('Command', array('showCommit'));
        $command
            ->expects($this->any())
            ->method('showCommit')
            ->will($this->returnValue(''));
        return $command;
    }

    protected function getMockRepository()
    {
        return $this->getMock('GitElephant\Repository', array(), array($this->repository->getPath(), $this->getMockBinary()));
    }

    protected function getMockBinary()
    {
        return $this->getMock('GitElephant\GitBinary');
    }

    protected function doCommitTest(Commit $commit, $sha, $tree, $author, $committer, $emailAuthor, $emailCommitter, $datetimeAuthor, $datetimeCommitter, $message)
    {
        $this->assertInstanceOf('GitElephant\Objects\Commit', $commit);
        $this->assertEquals($sha, $commit->getSha());
        $this->assertEquals($tree, $commit->getTree());
        $this->assertInstanceOf('GitElephant\Objects\GitAuthor', $commit->getAuthor());
        $this->assertEquals($author, $commit->getAuthor()->getName());
        $this->assertEquals($emailAuthor, $commit->getAuthor()->getEmail());
        $this->assertInstanceOf('GitElephant\Objects\GitAuthor', $commit->getCommitter());
        $this->assertEquals($committer, $commit->getCommitter()->getName());
        $this->assertEquals($emailCommitter, $commit->getCommitter()->getEmail());
        $this->assertInstanceOf('\Datetime', $commit->getDatetimeAuthor());
        $this->assertEquals($datetimeAuthor, $commit->getDatetimeAuthor()->format('U'));
        $this->assertInstanceOf('\Datetime', $commit->getDatetimeCommitter());
        $this->assertEquals($datetimeCommitter, $commit->getDatetimeCommitter()->format('U'));
        $this->assertEquals($message, $commit->getMessage()->getShortMessage());
    }
}
