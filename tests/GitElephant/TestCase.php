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

use GitElephant\Command\Caller\Caller;
use GitElephant\Command\Caller\CallerInterface;
use GitElephant\Command\MvCommand;
use GitElephant\Objects\Author;
use GitElephant\Objects\Commit;
use Mockery as m;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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
     * @var Repository|array<Repository>
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
     * @param string|int|null $name the name or index of the repository
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
     * @return CallerInterface the real/not mocked caller
     */
    protected function getCaller(): CallerInterface
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
    protected function initRepository(string $name = null, int $index = null): void
    {
        $tempDir = realpath(sys_get_temp_dir());
        $tempName = null === $name
            ? tempnam($tempDir, 'gitelephant')
            : $tempDir . DIRECTORY_SEPARATOR . $name;
        $this->path = $tempName;
        @unlink($this->path);
        $fs = new Filesystem();
        $fs->mkdir($this->path);
        $this->caller = new Caller(null, $this->path);
        if (is_null($index)) {
            $this->repository = Repository::open($this->path);
            $this->assertInstanceOf(Repository::class, $this->repository);
        } else {
            if (!is_array($this->repository)) {
                $this->repository = [];
            }
            $this->repository[$index] = Repository::open($this->path);
            $this->assertInstanceOf(Repository::class, $this->repository[$index]);
        }
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        if (is_array($this->repository)) {
            array_map(function (Repository $repo) use ($fs) {
                $fs->remove($repo->getPath());
            }, $this->repository);
        } else if ($this->path) {
            $fs->remove($this->path);
        }
        m::close();
    }

    /**
     * Write to file. Creates the file if not existing.
     * Overwrites content if already existing.
     *
     * @param string      $name       file name
     * @param string|null $folder     folder name
     * @param string|null        $content    content
     * @param Repository  $repository repository to add file to
     *
     * @return void
     */
    protected function addFile(
        string $name,
        string $folder = null,
        string $content = null,
        Repository $repository = null
    ): void {
        $path = is_null($repository) ? $this->path : $repository->getPath();
        $filename = $folder == null
            ? $path . DIRECTORY_SEPARATOR . $name
            : $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name;
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
    protected function removeFile(string $name): void
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
    protected function updateFile(string $name, string $content): void
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
    protected function renameFile(string $originName, string $targetName, bool $gitMv = true): void
    {
        if ($gitMv) {
            $this->getRepository()
                ->getCaller()
                ->execute(MvCommand::getInstance()->rename($originName, $targetName));

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

    protected function addSubmodule(string $url, string $path): void
    {
        $this->getRepository()->addSubmodule($url, $path);
    }

    /**
     * @param string $classname
     *
     * @return MockObject
     */
    protected function getMock(string $classname): MockObject
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
     * @return MockObject
     */
    protected function getMockCaller($command, $output): MockObject
    {
        $mock = $this->createMock(CallerInterface::class);
        $mock
            ->expects($this->any())
            ->method('execute')
            ->willReturn($mock);
        $mock
            ->expects($this->any())
            ->method('getOutputLines')
            ->willReturn($output);

        return $mock;
    }

    protected function addCommandToMockContainer(MockObject $container, string $commandName): void
    {
        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo($commandName))
            ->willReturn($this->getMockCommand());
    }

    /**
     *
     * @param MockObject $repo
     * @param string|array $output
     * @return void
     */
    protected function addOutputToMockRepo(MockObject $repo, $output): void
    {
        $repo
            ->expects($this->any())
            ->method('getCaller')
            ->willReturn($this->getMockCaller('', $output));
    }

    protected function getMockCommand(): MockObject
    {
        $command = $this->getMock('Command', ['showCommit']);
        $command
            ->expects($this->any())
            ->method('showCommit')
            ->willReturn('');

        return $command;
    }

    protected function getMockRepository(): MockObject
    {
        return $this->getMock(
            Repository::class,
            [],
            [
                $this->repository->getPath(),
                null,
            ]
        );
    }

    /**
     * Do a test on a certain commit
     *
     * @param Commit $commit the commit to test
     * @param string $sha
     * @param string $tree
     * @param string $author the name of the author
     * @param string $committer the name of the committer
     * @param string $emailAuthor
     * @param string $emailCommitter
     * @param integer $datetimeAuthor
     * @param integer $datetimeCommitter
     * @param string $message
     * @return void
     */
    protected function doCommitTest(
        Commit $commit,
        string $sha,
        string $tree,
        string $author,
        string $committer,
        string $emailAuthor,
        string $emailCommitter,
        int $datetimeAuthor,
        int $datetimeCommitter,
        string $message
    ): void {
        $this->assertInstanceOf(Commit::class, $commit);
        $this->assertEquals($sha, $commit->getSha());
        $this->assertEquals($tree, $commit->getTree());
        $this->assertInstanceOf(Author::class, $commit->getAuthor());
        $this->assertEquals($author, $commit->getAuthor()->getName());
        $this->assertEquals($emailAuthor, $commit->getAuthor()->getEmail());
        $this->assertInstanceOf(Author::class, $commit->getCommitter());
        $this->assertEquals($committer, $commit->getCommitter()->getName());
        $this->assertEquals($emailCommitter, $commit->getCommitter()->getEmail());
        $this->assertInstanceOf(\DateTime::class, $commit->getDatetimeAuthor());
        $this->assertEquals($datetimeAuthor, $commit->getDatetimeAuthor()->format('U'));
        $this->assertInstanceOf(\DateTime::class, $commit->getDatetimeCommitter());
        $this->assertEquals($datetimeCommitter, $commit->getDatetimeCommitter()->format('U'));
        $this->assertEquals($message, $commit->getMessage()->getShortMessage());
    }

    /**
     * Compatibility function for PHP 7.2:
     * PHPUnit 9. only supports PHP >= 7.3,
     * but has a different compatibility regarding the assertRegex function
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     * @return void
     */
    protected function myAssertMatchesRegularExpression($pattern, $string, $message = '')
    {
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $string, $message);
        } else {
            $this->assertRegExp($pattern, $string, $message);
        }
    }
}
