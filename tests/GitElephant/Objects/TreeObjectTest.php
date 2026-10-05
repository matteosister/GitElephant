<?php

declare(strict_types=1);

namespace GitElephant\Objects;

use GitElephant\TestCase;

final class TreeObjectTest extends TestCase
{
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFolder('test');
        $this->addFile('test-file', 'test');
        $this->getRepository()->commit('first commit', true);
    }

    public function testInstance(): void
    {
        $tree = $this->getRepository()->getTree('master', 'test');
        $this->assertInstanceOf(\GitElephant\Objects\TreeObject::class, $tree[0]);
    }
}
