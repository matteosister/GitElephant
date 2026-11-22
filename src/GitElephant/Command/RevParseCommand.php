<?php

/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2014  John Schlick John_Schlick@hotmail.com
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
use GitElephant\Repository;

/**
 * Class RevParseCommand
 */
class RevParseCommand extends BaseCommand
{
    public const GIT_REV_PARSE_COMMAND = 'rev-parse';

    public const OPTION_ALL = '--all';
    public const OPTION_KEEP_DASHDASH = '--keep-dashdash';
    public const OPTION_STOP_AT_NON_OPTION = '--stop-at-non-option';
    public const OPTION_SQ_QUOTE = '--sq-quote';
    public const OPTION_REVS_ONLY = '--revs-only';
    public const OPTION_NO_REVS = '--no-revs';
    public const OPTION_FLAGS = '--flags';
    public const OPTION_NO_FLAGS = '--no-flags';
    public const OPTION_DEFAULT = '--default';
    public const OPTION_VERIFY = '--verify';
    public const OPTION_QUIET = '--quiet';
    public const OPTION_SQ = '--sq';
    public const OPTION_NOT = '--not';
    public const OPTION_SYMBOLIC = '--symbolic';
    public const OPTION_SYMBOLIC_FULL_NAME = '--symbolic-full-name';
    public const OPTION_ABBREV_REF = '--abbrev-ref';
    public const OPTION_DISAMBIGUATE = '--disambiguate';
    public const OPTION_BRANCHES = '--branches';
    public const OPTION_TAGS = '--tags';
    public const OPTION_REMOTES = '--remotes';
    public const OPTION_GLOB = '--glob';
    public const OPTION_SHOW_TOPLEVEL = '--show-toplevel';
    public const OPTION_SHOW_PREFIX = '--show-prefix';
    public const OPTION_SHOW_CDUP = '--show-cdup';
    public const OPTION_GIT_DIR = '--git-dir';
    public const OPTION_IS_INSIDE_GIT_DIR = '--is-inside-git-dir';
    public const OPTION_IS_INSIDE_WORK_TREE = '--is-inside-work-tree';
    public const OPTION_IS_BARE_REPOSIORY = '--is-bare-repository';
    public const OPTION_LCOAL_ENV_VARS = '--local-env-vars';
    public const OPTION_SHORT = '--short';
    public const OPTION_SINCE = '--since';
    public const OPTION_AFTER = '--after';
    public const OPTION_UNTIL = '--until';
    public const OPTION_BEFORE = '--before';
    public const OPTION_RESOLVE_GIT_DIR = '--resolve-git-dir';

    public const TAG_HEAD = "HEAD";

    /**
     * @var string[]
     */
    private const ALLOWED_SWITCH_OPTIONS = [
        self::OPTION_ALL,
        self::OPTION_KEEP_DASHDASH,
        self::OPTION_STOP_AT_NON_OPTION,
        self::OPTION_SQ_QUOTE,
        self::OPTION_REVS_ONLY,
        self::OPTION_NO_REVS,
        self::OPTION_FLAGS,
        self::OPTION_NO_FLAGS,
        self::OPTION_VERIFY,
        self::OPTION_QUIET,
        self::OPTION_SQ,
        self::OPTION_NOT,
        self::OPTION_SYMBOLIC,
        self::OPTION_SYMBOLIC_FULL_NAME,
        self::OPTION_ABBREV_REF,
        self::OPTION_BRANCHES,
        self::OPTION_TAGS,
        self::OPTION_REMOTES,
        self::OPTION_SHOW_TOPLEVEL,
        self::OPTION_SHOW_PREFIX,
        self::OPTION_SHOW_CDUP,
        self::OPTION_GIT_DIR,
        self::OPTION_IS_INSIDE_GIT_DIR,
        self::OPTION_IS_INSIDE_WORK_TREE,
        self::OPTION_IS_BARE_REPOSIORY,
        self::OPTION_LCOAL_ENV_VARS,
        self::OPTION_SHORT,
    ];

    /**
     * @var string[]
     */
    private const ALLOWED_VALUE_PREFIXES = [
        self::OPTION_DEFAULT . '=',
        self::OPTION_ABBREV_REF . '=',
        self::OPTION_DISAMBIGUATE . '=',
        self::OPTION_BRANCHES . '=',
        self::OPTION_TAGS . '=',
        self::OPTION_REMOTES . '=',
        self::OPTION_GLOB . '=',
        self::OPTION_SHORT . '=',
        self::OPTION_SINCE . '=',
        self::OPTION_AFTER . '=',
        self::OPTION_UNTIL . '=',
        self::OPTION_BEFORE . '=',
        self::OPTION_RESOLVE_GIT_DIR . '=',
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
     * @param Branch|string $arg
     * @throws \RuntimeException
     */
    public function revParse($arg = null, array $options = []): string
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_REV_PARSE_COMMAND);
        // if there are options add them.
        foreach ($options as $option) {
            if (!is_string($option) || !$this->isAllowedOption($option)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid rev-parse option "%s": option is not allowed.', is_scalar($option) ? (string) $option : gettype($option))
                );
            }
            $this->addCommandArgument($option);
        }

        if (!is_null($arg)) {
            $this->assertNotAnOption($arg);
            $this->addCommandSubject2($arg);
        }

        return $this->getCommand();
    }

    private function isAllowedOption(string $option): bool
    {
        if (str_contains($option, "\0") || str_contains($option, "\r") || str_contains($option, "\n")) {
            return false;
        }

        if (in_array($option, self::ALLOWED_SWITCH_OPTIONS, true)) {
            return true;
        }

        foreach (self::ALLOWED_VALUE_PREFIXES as $prefix) {
            if (str_starts_with($option, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
