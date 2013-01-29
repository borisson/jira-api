<?php
namespace Botchla\JiraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Botchla\JiraBundle\Security\User;

class DefaultController extends Controller
{
    private $JIRA_URL = ''; # jira instance location http://myjira.example.com/rest/api/2/
    private $LOGIN = ''; # username
    private $PASSWORD = ''; #password


    public function __construct()
    {
        if (file_exists('/vagrant/testfile.php')) {
          include '/vagrant/testfile.php';
          $this->JIRA_URL = $jira_url;
          $this->LOGIN    = $jira_login;
          $this->PASSWORD = $jira_pass;
        }

        if ($this->JIRA_URL == '') {
            throw $this->createNotFoundException('JIRA url is not set');
        }
    }

    /**
     * @Route("/project")
     * @Template("BotchlaJiraBundle:Projects:index.html.twig")
     */
    public function projectAction()
    {
        $projects = $this->doCurl($this->JIRA_URL.'project');

        return array(
            'name' => 'Jira-test',
            'result' => $projects
        );
    }

    /**
     * @Route("/project/{abbr}")
     * @Template("BotchlaJiraBundle:Issues:issues.html.twig")
     */
    public function projectDetailAction($abbr = null)
    {
        $issues = $this->doCurl($this->JIRA_URL.'search?jql=project="'.$abbr.'"+AND+assignee="'.$this->LOGIN.'"');

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

        $issues = $this->doCurl($this->JIRA_URL.'search?jql=updatedDate>startOfDay()+AND+updatedDate<endOfDay()+AND+(status+changed+by+"'.$this->LOGIN.'"+OR+assignee="'.$this->LOGIN.'")');

        $totaltime = 0;
        $output_issues = array();
        if (count($issues) > 0) {
            foreach( $issues->issues as $k => $issue_list) {
                // generate url
                $worklogurl = $this->JIRA_URL.'issue/'.$issue_list->key.'/worklog';
                $worklog    = $this->doCurl($worklogurl);

                $output_issues[$issue_list->key] = $issue_list;

                // loop over worklogs
                if ( isset($worklog->worklogs) && count($worklog->worklogs) > 0 ) {
                    foreach ($worklog->worklogs as $value) {
                        // only add worklog to output if this also happened today
                        if (date('dmY' , strtotime($value->updated)) == date('dmY') ) {
                            $output_issues[$issue_list->key]->IssueWorklog[] = $value;
                            $totaltime += $value->timeSpentSeconds;
                        }
                    }
                }
                if (!isset($output_issues[$issue_list->key]->IssueWorklog)) {
                    $output_issues[$issue_list->key]->IssueWorklog = null;
                }
            }
        }

        $totaltimeFormatted = $this->formatTime($totaltime);

        return array(
          'name' => 'Jira-test',
          'result' => $output_issues,
          'totaltime' => $totaltimeFormatted
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

    /**
     * private function to format time (seconds to human readable)
     * @param  int      $seconds    seconds
     * @return string               formatted version
     */
    private function formatTime($seconds) {
        $hours = 0;
        $mins = 0;

        $hours = floor($seconds / 3600);
        $mins  = floor(($seconds - ($hours*3600)) / 60);

        return $hours.':'.$mins;
    }
}
