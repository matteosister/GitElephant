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
namespace GitElephant\Objects\Commit;

/**
 * Commit message tests
 *
 * @author Mathias Geat <mathias@ailoo.net>
 */
final class MessageTest extends \PHPUnit\Framework\TestCase
{
    protected $shortMsg;
    protected $fullMsg;

    /**
     * @var Message
     */
    protected $msg;

    protected function setUp(): void
    {
        $this->shortMsg = 'This is the short message';
        $longMsg = <<<HDOC
Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
sed diam nonumy eirmod tempor invidunt ut labore et dolore
magna aliquyam erat, sed diam voluptua. At vero eos et accusam
et justo duo dolores et ea rebum.
HDOC;

        $this->fullMsg = $this->shortMsg . PHP_EOL . PHP_EOL . $longMsg;
        $this->msg = new Message(explode(PHP_EOL, $this->fullMsg));
    }

    public function testGetShortMessage(): void
    {
        $this->assertEquals($this->shortMsg, $this->msg->getShortMessage());
    }

    public function testGetFullMessage(): void
    {
        $this->assertEquals($this->fullMsg, $this->msg->getFullMessage());
    }

    public function testToString(): void
    {
        $this->assertEquals($this->shortMsg, $this->msg->toString());
        $this->assertEquals($this->shortMsg, $this->msg->__toString());
        $this->assertEquals($this->shortMsg, (string) $this->msg);

        $this->assertEquals($this->fullMsg, $this->msg->toString(true));
    }
}
