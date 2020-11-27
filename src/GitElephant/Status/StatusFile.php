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

namespace GitElephant\Status;

/**
 * Class StatusFile
 *
 * @package GitElephant\Status
 */
class StatusFile
{
    public const UNTRACKED = '?';
    public const IGNORED = '!';
    public const UNMODIFIED = '';
    public const MODIFIED = 'M';
    public const ADDED = 'A';
    public const DELETED = 'D';
    public const RENAMED = 'R';
    public const COPIED = 'C';
    public const UPDATED_BUT_UNMERGED = 'U';

    /**
     * @var string
     */
    private $x;

    /**
     * @var string
     */
    private $y;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $renamed;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $x       X section of the status --porcelain output
     * @param string $y       Y section of the status --porcelain output
     * @param string $name    file name
     * @param string $renamed new file name (if renamed)
     */
    private function __construct(string $x, string $y, string $name, string $renamed = null)
    {
        $this->x = ' ' === $x ? null : $x;
        $this->y = ' ' === $y ? null : $y;
        $this->name = $name;
        $this->renamed = $renamed;
    }

    /**
     * @param string $x       X section of the status --porcelain output
     * @param string $y       Y section of the status --porcelain output
     * @param string $name    file name
     * @param string $renamed new file name (if renamed)
     *
     * @return StatusFile
     */
    public static function create(
        string $x,
        string $y,
        string $name,
        string $renamed = null
    ): \GitElephant\Status\StatusFile {
        return new self($x, $y, $name, $renamed);
    }

    /**
     * @return bool
     */
    public function isRenamed(): bool
    {
        return $this->renamed !== null;
    }

    /**
     * Get the file name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the renamed
     *
     * @return string|null
     */
    public function getRenamed(): ?string
    {
        return $this->renamed;
    }

    /**
     * Get the status of the index
     *
     * @return string
     */
    public function getIndexStatus(): ?string
    {
        return $this->x;
    }

    /**
     * Get the status of the working tree
     *
     * @return string|null
     */
    public function getWorkingTreeStatus(): ?string
    {
        return $this->y;
    }

    /**
     * description of the status
     *
     * @return void
     */
    public function calculateDescription(): void
    {
        $status = $this->x . $this->y;
        $matching = [
            '/ [MD]/' => 'not updated',
            '/M[MD]/' => 'updated in index',
            '/A[MD]/' => 'added to index',
            '/D[M]/' => 'deleted from index',
            '/R[MD]/' => 'renamed in index',
            '/C[MD]/' => 'copied in index',
            '/[MARC] /' => 'index and work tree matches',
            '/[MARC]M/' => 'work tree changed since index',
            '/[MARC]D/' => 'deleted in work tree',
            '/DD/' => 'unmerged, both deleted',
            '/AU/' => 'unmerged, added by us',
            '/UD/' => 'unmerged, deleted by them',
            '/UA/' => 'unmerged, added by them',
            '/DU/' => 'unmerged, deleted by us',
            '/AA/' => 'unmerged, both added',
            '/UU/' => 'unmerged, both modified',
            '/\?\?/' => 'untracked',
            '/!!/' => 'ignored',
        ];
        $out = [];
        foreach ($matching as $pattern => $label) {
            if (preg_match($pattern, $status)) {
                $out[] = $label;
            }
        }

        $this->description = implode(', ', $out);
    }

    /**
     * Set Description
     *
     * @param string $description the description variable
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get Description.
     * Note that in certain environments, git might
     * format the output differently, leading to the description
     * being an empty string. Use setDescription(string) to set it yourself.
     *
     * @see #calulcateDescription()
     * @see #setDescription($description)
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->description === null) {
            $this->calculateDescription();
        }

        return $this->description;
    }

    /**
     * Set Type
     *
     * @param string $type the type variable
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get the Type of status/change.
     * Please note that this type might not be set by default.
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
