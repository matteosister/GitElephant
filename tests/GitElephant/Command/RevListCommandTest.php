<?php

declare(strict_types=1);

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GitElephant\Command;

use GitElephant\Objects\Branch;
use GitElephant\TestCase;

final class RevListCommandTest extends TestCase
{
    public function testCountBetween(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('test');
        $this->getRepository()->commit('test', true);

        $branch1 = Branch::create($this->getRepository(), 'branch1');
        $branch2 = Branch::create($this->getRepository(), 'branch2');

        $c = RevListCommand::getInstance();
        $this->assertEquals("rev-list '--left-right' '--count' 'origin/master...master'", $c->countBetween('origin/master', 'master'));
        $this->assertEquals("rev-list '--left-right' '--count' 'branch1...branch2'", $c->countBetween($branch1, $branch2));
    }
}
