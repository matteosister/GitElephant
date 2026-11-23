<?php

/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2013  Matteo Giachino
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see [http://www.gnu.org/licenses/].
 */

namespace GitElephant\Command;

use GitElephant\Objects\Branch;
use GitElephant\Objects\Remote;
use GitElephant\Repository;

/**
 * Class PushCommand
 */
class PushCommand extends BaseCommand
{
    public const GIT_PUSH_COMMAND = 'push';

    /**
     * Allowlist of safe git push flags permitted via the $args parameter.
     * Dangerous flags such as --exec, --receive-pack, and --repo are excluded.
     *
     * @var string[]
     */
    private const ALLOWED_PUSH_ARGS = [
        '--all',
        '--atomic',
        '--delete',
        '-d',
        '--dry-run',
        '-n',
        '--follow-tags',
        '--force',
        '-f',
        '--force-with-lease',
        '--mirror',
        '--no-atomic',
        '--no-thin',
        '--no-verify',
        '--porcelain',
        '--prune',
        '--quiet',
        '-q',
        '--set-upstream',
        '-u',
        '--tags',
        '--thin',
        '--verbose',
        '-v',
    ];

    /**
     * constructor
     *
     * @param \GitElephant\Repository $repo The repository object this command
     *                                      will interact with
     */
    public function __construct(?Repository $repo = null)
    {
        parent::__construct($repo);
    }

    /**
     * @param Remote|string|null $remote
     * @param Branch|string|null $branch
     *
     * @throws \RuntimeException
     */
    public function push($remote = 'origin', $branch = 'master', ?string $args = null, bool $tags = false): string
    {
        $this->clearAll();

        if ($remote instanceof Remote) {
            $remote = $remote->getName();
        }
        if ($branch instanceof Branch) {
            $branch = $branch->getName();
        }

        $this->assertSafeRemoteUrl($remote);
        $this->assertNotAnOption($branch);

        $this->addCommandName(self::GIT_PUSH_COMMAND);
        if ($tags) {
            $this->addCommandArgument('--tags');
        }
        if (!is_null($args)) {
            if (!in_array($args, self::ALLOWED_PUSH_ARGS, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid push argument "%s": option is not allowed.', $args)
                );
            }
            $this->addCommandArgument($args);
        }

        if (!is_null($remote)) {
            $this->addCommandSubject($remote);
        }
        if (!is_null($branch)) {
            $this->addCommandSubject2($branch);
        }

        return $this->getCommand();
    }
}
