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

use \GitElephant\Command\Caller\Caller;
use GitElephant\Command\Caller\CallerInterface;
use \GitElephant\Command\MvCommand;
use \GitElephant\Objects\Commit;
use \GitElephant\Repository;
use \Mockery as m;
use \Symfony\Component\Filesystem\Filesystem;
use \Symfony\Component\Finder\Finder;

/**
 * Class TestCase
 *
 * @package GitElephant
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \GitElephant\Command\Caller\CallerInterface
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
     * @param null $name
     *
     * @return \GitElephant\Repository
     */
    protected function getRepository($name = null)
    {
        if ($this->repository == null) {
            $this->initRepository($name);
        }
        if (is_null($name)) {
            return $this->repository;
        } else {
            return $this->repository[$name];
        }
    }

    /**
     * @return \GitElephant\Command\Caller\Caller
     */
    protected function getCaller(): \GitElephant\Command\Caller\CallerInterface
    {
        if ($this->caller == null) {
            $this->initRepository();
        }

        return $this->caller;
    }

    /**
     * @param null|string $name  the folder name
     * @param int         $index the repository index (for getting them back)
     *
     * @return void
     */
    protected function initRepository($name = null, $index = null): void
    {
        $tempDir = realpath(sys_get_temp_dir());
        $tempName = null === $name ? tempnam($tempDir, 'gitelephant') : $tempDir . DIRECTORY_SEPARATOR . $name;
        $this->path = $tempName;
        @unlink($this->path);
        $fs = new Filesystem();
        $fs->mkdir($this->path);
        $this->caller = new Caller(null, $this->path);
        if (is_null($index)) {
            $this->repository = Repository::open($this->path);
            $this->assertInstanceOf('GitElephant\Repository', $this->repository);
        } else {
            if (!is_array($this->repository)) {
                $this->repository = array();
            }
            $this->repository[$index] = Repository::open($this->path);
            $this->assertInstanceOf('GitElephant\Repository', $this->repository[$index]);
        }
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        if (is_array($this->repository)) {
            array_map(function (Repository $repo) use ($fs) {
                $fs->remove($repo->getPath());
            }, $this->repository);
        } else {
            $fs->remove($this->path);
        }
        m::close();
    }

    /**
     * @param string      $name       file name
     * @param string|null $folder     folder name
     * @param null        $content    content
     * @param Repository  $repository repository to add file to
     *
     * @return void
     */
    protected function addFile($name, $folder = null, $content = null, $repository = null): void
    {
        $path = is_null($repository) ? $this->path : $repository->getPath();
        $filename = $folder == null ?
            $path . DIRECTORY_SEPARATOR . $name : $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name;
        $handle = fopen($filename, 'w');
        $fileContent = $content === null ? 'test content' : $content;
        $this->assertTrue(false !== fwrite($handle, $fileContent), sprintf('unable to write the file %s', $name));
        fclose($handle);
    }

    /**
     * remove file from repo
     *
     * @param string $name
     */
    protected function removeFile($name): void
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $name;
        $this->assertTrue(unlink($filename));
    }

    /**
     * update a file in the repository
     *
     * @param string $name    file name
     * @param string $content content
     */
    protected function updateFile($name, $content): void
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $name;
        $this->assertTrue(false !== file_put_contents($filename, $content));
    }

    /**
     * rename a file in the repository
     *
     * @param string $originName file name
     * @param string $targetName new file name
     * @param bool   $gitMv      use git mv, otherwise uses php rename function (with the Filesystem component)
     */
    protected function renameFile($originName, $targetName, $gitMv = true): void
    {
        if ($gitMv) {
            $this->getRepository()->getCaller()->execute(MvCommand::getInstance()->rename($originName, $targetName));

            return;
        }
        $origin = $this->path . DIRECTORY_SEPARATOR . $originName;
        $target = $this->path . DIRECTORY_SEPARATOR . $targetName;
        $fs = new Filesystem();
        $fs->rename($origin, $target);
    }

    /**
     * @param string $name name
     *
     * @return void
     */
    protected function addFolder($name): void
    {
        $fs = new Filesystem();
        $fs->mkdir($this->path . DIRECTORY_SEPARATOR . $name);
    }

    protected function addSubmodule($url, $path): void
    {
        $this->getRepository()->addSubmodule($url, $path);
    }

    /**
     * @param $classname
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMock($classname): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this
            ->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * mock the caller
     *
     * @param string $command command
     * @param string $output  output
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockCaller($command, $output): \PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(CallerInterface::class);
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

    protected function getMockContainer(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->getMock('GitElephant\Command\CommandContainer');
    }

    protected function addCommandToMockContainer(\PHPUnit\Framework\MockObject\MockObject $container, $commandName): void
    {
        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($commandName))
            ->will($this->returnValue($this->getMockCommand()));
    }

    protected function addOutputToMockRepo(\PHPUnit\Framework\MockObject\MockObject $repo, $output): void
    {
        $repo
            ->expects($this->any())
            ->method('getCaller')
            ->will($this->returnValue($this->getMockCaller('', $output)));
    }

    protected function getMockCommand(): \PHPUnit\Framework\MockObject\MockObject
    {
        $command = $this->getMock('Command', array('showCommit'));
        $command
            ->expects($this->any())
            ->method('showCommit')
            ->will($this->returnValue(''));

        return $command;
    }

    protected function getMockRepository(): \PHPUnit\Framework\MockObject\MockObject
    {
        $mockRepo = $this->getMock(
            Repository::class,
            array(),
            array(
                $this->repository->getPath(),
                null,
            )
        );

        $mockRepo->expects($this->any())->method('getCaller')->willReturn($this->getMockCaller('', ''));
        return $mockRepo;
    }

    protected function doCommitTest(
        Commit $commit,
        $sha,
        $tree,
        $author,
        $committer,
        $emailAuthor,
        $emailCommitter,
        $datetimeAuthor,
        $datetimeCommitter,
        $message
    ): void {
        $this->assertInstanceOf('GitElephant\Objects\Commit', $commit);
        $this->assertEquals($sha, $commit->getSha());
        $this->assertEquals($tree, $commit->getTree());
        $this->assertInstanceOf('GitElephant\Objects\Author', $commit->getAuthor());
        $this->assertEquals($author, $commit->getAuthor()->getName());
        $this->assertEquals($emailAuthor, $commit->getAuthor()->getEmail());
        $this->assertInstanceOf('GitElephant\Objects\Author', $commit->getCommitter());
        $this->assertEquals($committer, $commit->getCommitter()->getName());
        $this->assertEquals($emailCommitter, $commit->getCommitter()->getEmail());
        $this->assertInstanceOf('\Datetime', $commit->getDatetimeAuthor());
        $this->assertEquals($datetimeAuthor, $commit->getDatetimeAuthor()->format('U'));
        $this->assertInstanceOf('\Datetime', $commit->getDatetimeCommitter());
        $this->assertEquals($datetimeCommitter, $commit->getDatetimeCommitter()->format('U'));
        $this->assertEquals($message, $commit->getMessage()->getShortMessage());
    }
}
