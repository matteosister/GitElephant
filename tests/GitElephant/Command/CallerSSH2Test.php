<?php

// Mock global PHP functions that are used in CallerSSH2
namespace GitElephant\Command\Caller {
    function ssh2_exec($resource, $tmpCmd) {
        return $tmpCmd;
    }

    function stream_set_blocking($stream, $blocking) {
        return $stream;
    }

    function stream_get_contents($stream) {
        if (strpos($stream, 'baz') !== false) {
            return "Foo\nBar\n\n\n\n\nBaz";
        } else {
            return $stream;
        }
    }

    function fclose($resource) {
        return true;
    }
}

namespace GitElephant\Command {

    use GitElephant\Command\Caller\CallerSSH2;
    use GitElephant\TestCase;

    class CallerSSH2Test extends TestCase {

        public function setUp()
        {
            $this->initRepository();
        }

        public function testCallerSSH2WithGitBinary()
        {
            $gitBin = 'git';
            $caller = new CallerSSH2('fakeResource', $gitBin);
            $caller->setRepository($this->repository);
            $caller->execute('foobar');
            $expectedCommand = 'cd ' . escapeshellarg($this->path) . ' && ' . $gitBin . ' foobar';
            $this->assertEquals($expectedCommand, $caller->getRawOutput());

            $lines = $caller->getOutputLines();
            $this->assertEquals($expectedCommand, $lines[0]);
        }

        public function testCallerSSH2WithoutGitBinary()
        {
            $gitBin = '/usr/bin/git';
            $caller = new CallerSSH2('fakeResource', $gitBin);
            $caller->setRepository($this->repository);
            $caller->execute('foobar');
            $expectedCommand = 'cd ' . escapeshellarg($this->path) . ' && ' . $gitBin . ' foobar';
            $this->assertEquals($expectedCommand, $caller->getRawOutput());

            $lines = $caller->getOutputLines();
            $this->assertEquals($expectedCommand, $lines[0]);
        }

        public function testCallerWithNonGitCommand()
        {
            $caller = new CallerSSH2('fakeResource');
            $caller->setRepository($this->repository);
            $caller->execute('foobar', false);
            $expectedCommand = 'cd ' . escapeshellarg($this->path) . ' && foobar';
            $this->assertEquals($expectedCommand, $caller->getRawOutput());
        }

        public function testEmptyLineStrip()
        {
            $caller = new CallerSSH2('fakeResource');
            $caller->setRepository($this->repository);
            $caller->execute('baz');

            $lines = $caller->getOutputLines(true);
            $this->assertEquals(count($lines), 3);
        }


    }
}
