<?php
/**
 * User: matteo
 * Date: 28/10/12
 * Time: 16.16
 *
 * Just for fun...
 */

namespace GitElephant\Objects;

use GitElephant\TestCase;
use GitElephant\Objects\TreeTag;

class TreeTagTest extends TestCase
{
    public function testTag()
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit1', true);
        $this->getRepository()->createTag('test-tag');
        $tag = new TreeTag($this->getRepository(), 'test-tag');
        $this->assertInstanceOf('GitElephant\Objects\TreeTag', $tag);
        $this->assertEquals('test-tag', $tag->getName());
        $this->assertEquals('refs/tags/test-tag', $tag->getFullRef());
        $this->assertEquals($this->getRepository()->getCommit()->getSha(), $tag->getSha());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonExistentTag()
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit1', true);
        $this->getRepository()->createTag('test-tag');
        $tag = new TreeTag($this->getRepository(), 'test-tag-non-existent');
    }
}
