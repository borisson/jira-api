<?php
namespace Botchla\JiraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;


class LoginController extends Controller
{
    /**
     * @Route("/login")
     * @Template("BotchlaJiraBundle:Login:login.html.twig")
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        $jiralocation = null;

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        $error = false;

        return $this->render(
            'BotchlaJiraBundle:Login:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
                'jiralocation'  => $jiralocation
            )
        );
    }

    /**
     * @Route("login_check")
     * @Template("BotchlaJiraBundle:Login:login.html.twig")
     */
    public function loginCheckAction()
    {

    }
}