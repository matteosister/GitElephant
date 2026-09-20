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
namespace GitElephant\Objects;

use GitElephant\TestCase;

/**
 * AuthorTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

final class AuthorTest extends TestCase
{
    /**
     * testAuthor
     */
    public function testAuthor(): void
    {
        $author = new Author();
        $author->setEmail('foo@bar.com');
        $author->setName('foo');
        $this->assertSame('foo@bar.com', $author->getEmail());
        $this->assertSame('foo', $author->getName());
        $this->assertEquals('foo <foo@bar.com>', $author);
    }
}
