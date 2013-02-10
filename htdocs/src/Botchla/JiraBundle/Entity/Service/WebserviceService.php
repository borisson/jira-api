<?php
namespace Botchla\JiraBundle\Entity\Service;

use Botchla\JiraBundle\Security\User;

/**
 * This class is where all webservice-requests are.
 */
class WebserviceService {
    private $JIRA_URL;
    private $LOGIN;
    private $PASSWORD;

    public function __construct() {

        if ($this->JIRA_URL == '') {
        }
    }

    /**
     *  do a curl "GET" action to JIRA
     * @param  string   $url    url to curl to
     *
     * @return stdClass         result object
     */
    public function curlGet($url) {

        // set username | password
        $userNamePassWord = $this->LOGIN.':'.$this->PASSWORD;
        $userNamePassWord64 = base64_encode($userNamePassWord);

        // start curl
        $curlHandle = curl_init();

        // set curl options
        curl_setopt($curlHandle, CURLOPT_USERPWD, $userNamePassWord);
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);

        // execute curl
        $result = curl_exec($curlHandle);
        // close connection
        curl_close($curlHandle);

        // return result
        return $result;
    }


    /**
     * do a curl "POST" action to JIRA
     * @param  string   $url            url to curl to
     * @param  mixed    $curlData       data to be posted, array or stdClass
     *
     * @return stdClass                 result object
     */
    public function curlPost($url, $curlData) {
        $data_string = json_encode($curlData);

        // set username | password
        $userNamePassWord = $this->LOGIN.':'.$this->PASSWORD;
        $userNamePassWord64 = base64_encode($userNamePassWord);

        // start curl
        $curlHandle = curl_init($url);

        // set curl options
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curlHandle, CURLOPT_USERPWD, $userNamePassWord);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data_string);

        // execute curl
        $result = curl_exec($curlHandle);
        // close connection
        curl_close($curlHandle);

        // return result
        return $result;
    }

    /**
     * do a curl "POST" action to JIRA, only used for /rest/auth/1/session
     * @param  string   $url            url to curl to
     * @param  mixed    $curlData       data to be posted, array or stdClass
     *
     * @return stdClass                 result object
     */
    public static function curlPostLogin($url, $curlData) {
        $data_string = json_encode($curlData);

        // start curl
        $curlHandle = curl_init($url);

        // set curl options
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data_string);

        // execute curl
        $result = curl_exec($curlHandle);

        // json decode
        $result = json_decode($result);

        // close connection
        curl_close($curlHandle);

        // return result
        return $result;
    }
}