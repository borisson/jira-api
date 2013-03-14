<?php
namespace Botchla\JiraBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Botchla\JiraBundle\Entity\Service\WebserviceService;

class JiraUserProvider implements UserProviderInterface
{

    private $username;
    private $password;
    private $jiraLocation;

    public function __construct($username = null, $password = null, $jiraLocation = 'http://localhost') {
        $this->username = $username;
        $this->password = $password;
        $this->jiraLocation = $jiraLocation;
    }


    public function loadUserByUsername($username)
    {
        $username = ($username == null ? $this->username : $username);
        // make a call to your webservice here
        // $userData = true;
        $user_array = array(
            'username' => $username,
            'password' => $this->password
        );
        $response = WebserviceService::curlPostLogin($this->jiraLocation . '/rest/auth/1/session', $user_array);

        // pretend it returns an array on success, false if there is no user
        if ( isset($response->session->value) ) {
            $password = $this->password;
            $username = $this->username;
            $jira_url = $this->jiraLocation.'/';
            $salt     = '';
            $roles    = array('ROLE_USER');

            return new JiraUser($username, $password, $jira_url, $salt, $roles);
        }

        if (isset( $response->errorMessages )) {
            throw new \Exception(sprintf("Error Processing Request, JIRA has thrown: %s" , $response->errorMessages[0] ), 1);
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