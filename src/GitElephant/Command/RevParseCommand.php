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
     * constructor
     *
     * @param \GitElephant\Repository $repo The repository object this command
     *                                      will interact with
     */
    public function __construct(Repository $repo = null)
    {
        parent::__construct($repo);
    }

    /**
     * @param array $options
     * @param Branch|string $arg
     *
     * @throws \RuntimeException
     * @return string
     */
    public function revParse($arg = null, array $options = []): string
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_REV_PARSE_COMMAND);
        // if there are options add them.
        foreach ($options as $option) {
            $this->addCommandArgument($option);
        }

        if (!is_null($arg)) {
            $this->addCommandSubject2($arg);
        }

        return $this->getCommand();
    }
}
