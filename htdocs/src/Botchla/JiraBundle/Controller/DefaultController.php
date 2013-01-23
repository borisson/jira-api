<?php
namespace Botchla\JiraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    private $JIRA_URL = ''; # jira instance location http://myjira.example.com/rest/api/2/
    private $LOGIN = ''; # username
    private $PASSWORD = ''; #password

    /**
     * @Route("/project")
     * @Template("BotchlaJiraBundle:Projects:index.html.twig")
     */
    public function projectAction()
    {

        // start CURL action
        $projects = $this->doCurl($this->JIRA_URL.'project');

        return array(
          'name' => 'Jira-test',
          'result' => $projects
        );
    }

    /**
     * @Route("/issue/{projectAbbreviation}")
     * @Template("BotchlaJiraBundle:Issues:issues.html.twig")
     */
    public function issueAction($projectAbbreviation)
    {
        // start CURL action
        $issues = $this->doCurl($this->JIRA_URL.'search?jql=project="'.$projectAbbreviation.'"+AND+assignee="'.$this->LOGIN.'"');
        print_r($issues);
        return array(
          'name' => 'Jira-test',
          'result' => $issues->issues
        );
    }


    /**
     * @Route("/today")
     * @Template("BotchlaJiraBundle:Issues:today.html.twig")
     */
    public function todayAction()
    {

        $issues = $this->doCurl($this->JIRA_URL.'search?jql=assignee="'.$this->LOGIN.'"+AND+updatedDate>startOfDay()+AND+updatedDate<endOfDay()');

        $output_issues = array();
        foreach( $issues->issues as $k => $issue_list) {
            // generate url
            $worklogurl = $this->JIRA_URL.'issue/'.$issue_list->key.'/worklog';

            $output_issues[$issue_list->key] = $issue_list;
            $output_issues[$issue_list->key]->IssueWorklog = $this->doCurl($worklogurl);
        }

        return array(
          'name' => 'Jira-test',
          'result' => $output_issues
        );
    }

    /**
     * private function, do a curl action to JIRA
     * @param  string   $url    url to curl to
     * @return stdClass         result object
     */
    private function doCurl($url) {

        // set username | password
        $userNamePassWord = $this->LOGIN.':'.$this->PASSWORD;
        $userNamePassWord64 = base64_encode($userNamePassWord);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERPWD, $userNamePassWord);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $result = (curl_exec($curl));
        $result_object = json_decode($result);
        curl_close($curl);
        return $result_object;
    }
}
