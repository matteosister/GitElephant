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
use GitElephant\Repository;
use GitElephant\Objects\NodeObject;

/**
 * BranchTest
 *
 * Branch test
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class SubmoduleCommandTest extends TestCase
{
  /**
   * setUp, called on every method
   */
  public function setUp(): void
  {
    $this->initRepository();
    $this->getRepository()->init();
    $this->addFile('test');
    $this->getRepository()->commit('first commit', true);
  }

  /**
   * testSubmodule
   */
  public function testSubmodule(): void
  {
    $tempDir = realpath(sys_get_temp_dir()) . 'gitelephant_' . md5(uniqid(rand(), 1));
    // horrible hack because php is beautiful.
    $tempName = @tempnam($tempDir, 'gitelephant');
    $path = $tempName;
    unlink($path);
    mkdir($path);
    $repository = new Repository($path);
    $repository->init();
    $repository->addSubmodule($this->repository->getPath());
    $repository->initSubmodule();
    $repository->updateSubmodule(true, true, true);
    $repository->commit('test', true);
    $tree = $repository->getTree();
    $this->assertContainsEquals('.gitmodules', $tree);
    $this->assertContainsEquals($this->repository->getHumanishName(), $tree);
    $submodule = $tree[0];
    $this->assertEquals(NodeObject::TYPE_LINK, $submodule->getType());
    $command = new SubmoduleCommand($repository);
    $this->assertEquals('submodule', $command->listSubmodules());
    $this->assertEquals($command->listSubmodules(), $command->lists());
  }
}
