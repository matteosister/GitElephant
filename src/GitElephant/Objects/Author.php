<?php

namespace GitElephant\Objects;

/**
 * Represent a git author
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class Author
{
    /**
     * Author name
     *
     * @var string
     */
    private $name;

    /**
     * Author email
     *
     * @var string
     */
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
