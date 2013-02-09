<?php
namespace Botchla\JiraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Botchla\JiraBundle\Security\User;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    private $JIRA_URL = ''; # jira instance location http://myjira.example.com/rest/api/2/
    private $LOGIN = ''; # username
    private $PASSWORD = ''; #password

    private $session;


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

        // create session
        $session = new Session();
        $session->start();
        $this->session = $session;
    }

    /**
     * @Route("/project", name="project")
     * @Template("BotchlaJiraBundle:Projects:index.html.twig")
     */
    public function projectAction()
    {
        $projects = $this->curlGet($this->JIRA_URL.'project');

        return array(
            'name' => 'Jira-test',
            'result' => $projects
        );
    }

    /**
     * @Route("/project/{abbr}")
     * @Template("BotchlaJiraBundle:Projects:issues.html.twig")
     */
    public function projectDetailAction($abbr = null)
    {
        $issues = $this->curlGet($this->JIRA_URL.'search?jql=project="'.$abbr.'"+AND+assignee="'.$this->LOGIN.'"');

        return array(
          'result' => $issues->issues
        );
    }


    /**
     * @Route("/worklog/create")
     * @Template("BotchlaJiraBundle:Issues:createlog.html.twig")
     */
    public function createWorklogAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            echo 'true';
        } else {
            echo 'test';
            print $this->session->get('LastTimer');
        }
        $newWorklog = array();
        $newWorklog['author']['name'] = $this->LOGIN;
        $newWorklog['updateAuthor']['name'] = $this->LOGIN;
        $newWorklog['comment'] = 'API comment';
        $newWorklog['timeSpentSeconds'] = 120;

        $curlUrl = $this->JIRA_URL . 'issue/'. "TEAMB-82" .'/worklog';

        // $curlResult = $this->curlPost($curlUrl, $newWorklog);

        return array(
          'name' => 'Jira-test'
        );
    }


    /**
     * @Route("/issue/create")
     * @Template("BotchlaJiraBundle:Issues:createissue.html.twig")
     */
    public function createIssueAction()
    {
        $newIssue = array();
        $newIssue['fields']['project']['key'] = 'TEAMB';
        $newIssue['fields']['summary'] = 'This is a test.';
        $newIssue['fields']['assignee']['name'] = $this->LOGIN;
        $newIssue['fields']['reporter']['name'] = $this->LOGIN;
        $newIssue['fields']['issuetype']['id'] = 7;

        $curlUrl = $this->JIRA_URL . 'issue/';

        // $curlResult = $this->curlPost($curlUrl, $newIssue);

        return array(
          'name' => 'Jira-test'
        );
    }


    /**
     * @Route("/today", name="today")
     * @Template("BotchlaJiraBundle:Issues:today.html.twig")
     */
    public function todayAction()
    {

        $issues = $this->curlGet($this->JIRA_URL.'search?jql=updatedDate>startOfDay()+AND+updatedDate<endOfDay()+AND+(status+changed+by+"'.$this->LOGIN.'"+OR+assignee="'.$this->LOGIN.'")');

        $totaltime = 0;
        $output_issues = array();
        if (count($issues) > 0) {
            foreach( $issues->issues as $k => $issue_list) {
                // generate url
                $worklogurl = $this->JIRA_URL.'issue/'.$issue_list->key.'/worklog';
                $worklog    = $this->curlGet($worklogurl);

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
     * @Route("/project/export/{abbr}")
     * @Template("BotchlaJiraBundle:Projects:issuesdetails.html.twig")
     */
    public function projectExportAction($abbr = null)
    {
        $issues = $this->curlGet($this->JIRA_URL.'search?jql=project="'.$abbr.'"');
        $project = $this->curlGet($this->JIRA_URL.'project/'.$abbr);

        $totaltime = 0;
        $output_issues = array();
        if (count($issues) > 0) {
            foreach( $issues->issues as $k => $issue_list) {
                // generate url
                $worklogurl = $this->JIRA_URL.'issue/'.$issue_list->key.'/worklog';
                $worklog    = $this->curlGet($worklogurl);

                $output_issues[$issue_list->key] = $issue_list;

                // loop over worklogs
                if ( isset($worklog->worklogs) && count($worklog->worklogs) > 0 ) {
                    foreach ($worklog->worklogs as $value) {
                        $output_issues[$issue_list->key]->IssueWorklog[] = $value;
                        $totaltime += $value->timeSpentSeconds;
                    }
                }
                if (!isset($output_issues[$issue_list->key]->IssueWorklog)) {
                    $output_issues[$issue_list->key]->IssueWorklog = null;
                }
            }
        }

        $totaltimeFormatted = $this->formatTime($totaltime);

        return array(
          'result' => $output_issues,
          'project' => $project,
          'totaltime' => $totaltimeFormatted
        );
    }


    /**
     * @Route("/timer", name="timer")
     * @Template("BotchlaJiraBundle:Timer:timerpage.html.twig")
     */
    public function timerAction($abbr = null)
    {
        $start_time = $this->session->get('timer');

        // if timer is set & time is positive
        if ($start_time > 0) {
            $current_time   = date('U');
            $diff_time      = ( $current_time - $start_time );

            $timerFormatted = $this->formatTime($diff_time);
        } else {
            $timerFormatted = '00:00';
        }

        return array(
            'time_timer' => $timerFormatted
        );
    }


    /**
     * @Route("/timer/start/")
     */
    public function timerStartAction($abbr = null)
    {
        // set new timer
        $newTimerStart = date('U');

        // set timer in session
        $this->session->set('timer', $newTimerStart);

        // return redirect
        return $this->redirect($this->generateUrl('botchla_jira_default_timer'));
    }


    /**
     * @Route("/timer/stop/")
     * @Template("BotchlaJiraBundle:Timer:timerpage.html.twig")
     */
    public function timerStopAction($abbr = null)
    {
        // calculate diff time
        $current_time = date('U');
        $start_time   = $this->session->get('timer');
        $diff_time    = ( $current_time - $start_time );

        // reset timer
        $this->session->set('timer' , 0);
        print $diff_time;

        // set timespent in session
        $this->session->set('LastTimer', $diff_time);

        // return redirect
        return $this->redirect($this->generateUrl('botchla_jira_default_createworklog'));
    }


    /**
     * private function, do a curl action to JIRA
     * @param  string   $url    url to curl to
     * @return stdClass         result object
     */
    private function curlGet($url) {

        // set username | password
        $userNamePassWord = $this->LOGIN.':'.$this->PASSWORD;
        $userNamePassWord64 = base64_encode($userNamePassWord);

        // start curl
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_USERPWD, $userNamePassWord);
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);

        $result = (curl_exec($curlHandle));
        $result_object = json_decode($result);
        curl_close($curlHandle);
        return $result_object;
    }


    /**
     * private function, do a curl POST action to JIRA
     * @param  string   $url            url to curl to
     * @param  mixed    $curlData       data to be posted, array or stdClass
     * @return stdClass                 result object
     */
    private function curlPost($url, $curlData) {
        $data_string = json_encode($curlData);

        // set username | password
        $userNamePassWord = $this->LOGIN.':'.$this->PASSWORD;
        $userNamePassWord64 = base64_encode($userNamePassWord);

        // start curl
        $curlHandle = curl_init($url);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curlHandle, CURLOPT_USERPWD, $userNamePassWord);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data_string);
        $result = curl_exec($curlHandle);
        curl_close($curlHandle);

        return $result;
    }

    /**
     * private function to format time (seconds to human readable)
     * @param  int      $seconds    seconds
     * @return string               formatted version
     */
    private function formatTime($seconds) {
        $hours = 0;
        $mins = 0;

        // split up time
        $hours = floor($seconds / 3600);
        $mins  = floor(($seconds - ($hours*3600)) / 60);

        // correctly format it
        $hours = sprintf('%02d' , $hours);
        $mins = sprintf('%02d' , $mins);

        return $hours.':'.$mins;
    }
}
