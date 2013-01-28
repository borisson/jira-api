<?php
namespace Botchla\JiraBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class JiraUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        // make a call to your webservice here
        $userData = true;

        // pretend it returns an array on success, false if there is no user
        if ($userData) {
            $password = '...';
            $username = 'test.test';
            $jira_url = 'http://jira.example.com/';
            $salt     = 'tests';
            $roles    = array();

            return new JiraUser($username, $password, $jira_url, $salt, $roles);
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof JiraUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Botchla\JiraBundle\Security\User\JiraUser';
    }
}