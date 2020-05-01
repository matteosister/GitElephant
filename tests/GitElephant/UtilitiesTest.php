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
final class UtilitiesTest extends TestCase
{
    /**
     * An array containing chars
     *
     * @var array<string>
     */
    private $arr = [
        'a',
        'b',
        'c',
        '1',
        'd',
        'b',
        'e',
    ];

    /**
     * @dataProvider pregSplitArrayProvider()
     *
     * @covers \GitElephant\Utilities::pregSplitArray
     *
     * @param array<string> $expected
     * @param array<string> $list
     * @param string $pattern
     * @return void
     */
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

    /**
     *
     *
     * @dataProvider
     *
     * @return void
     */
    public function testPregSplitFlatArray(): void
    {
        $this->assertEquals(
            [
                ['a'],
                ['b', 'c', '1', 'd'],
                ['b', 'e'],
            ],
            Utilities::pregSplitFlatArray($this->arr, '/^b$/')
        );
    }

    /**
     * Get the array test contents
     *
     * @return array
     */
    public function pregSplitArrayProvider(): array
    {
        return [
            [
                [
                    ['b', 'c', '1', 'd'],
                    ['b', 'e'],
                ],
                $this->arr,
                '/^b$/',
            ],
            [
                [
                    ['1', 'd', 'b', 'e'],
                ],
                $this->arr,
                '/^\d$/',
            ],
        ];
    }
}
