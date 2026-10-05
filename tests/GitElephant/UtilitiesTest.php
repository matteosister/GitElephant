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

declare(strict_types=1);

namespace GitElephant;

/**
 * @author Matteo Giachino <matteog@gmail.com>
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\GitElephant\Utilities::class, 'pregSplitArray')]
final class UtilitiesTest extends TestCase
{
    /**
     * An array containing chars
     *
     * @var array<string>
     */
    private static array $arr = [
        'a',
        'b',
        'c',
        '1',
        'd',
        'b',
        'e',
    ];

    /**
     *
     *
     * @param array<string> $expected
     * @param array<string> $list
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('pregSplitArrayProvider')]
    public function testPregSplitArray(array $expected, array $list, string $pattern): void
    {
        $this->assertEquals(
            $expected,
            Utilities::pregSplitArray(
                $list,
                $pattern
            )
        );
    }

    public function testPregSplitFlatArray(): void
    {
        $this->assertEquals(
            [
                ['a'],
                ['b', 'c', '1', 'd'],
                ['b', 'e'],
            ],
            Utilities::pregSplitFlatArray(self::$arr, '/^b$/')
        );
    }

    /**
     * Get the array test contents
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function pregSplitArrayProvider(): \Iterator
    {
        yield [
            [
                ['b', 'c', '1', 'd'],
                ['b', 'e'],
            ],
            self::$arr,
            '/^b$/',
        ];
        yield [
            [
                ['1', 'd', 'b', 'e'],
            ],
            self::$arr,
            '/^\d$/',
        ];
    }
}
