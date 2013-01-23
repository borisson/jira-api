<?php
namespace Botchla\JiraBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class JiraUser implements UserInterface, EquatableInterface
{
    private $username;
    private $password;
    private $jira_url;
    private $salt;
    private $roles;

    public function __construct($username, $password, $jira_url, $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->jira_url = $jira_url;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getJiraURL()
    {
        return $this->jira_url;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof JiraUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}