<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Objects;


/**
 * Represent a git author
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class GitAuthor
{
    private $name;
    private $email;

    /**
     * email setter
     *
     * @param string $email the email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * email getter
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * name setter
     *
     * @param string $name the author name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * name getter
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
