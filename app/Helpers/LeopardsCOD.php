<?php

namespace App\Helpers;

use Carbon\Carbon;
use Mockery\Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Goutte\Client;

use File;

/*
 * To use this class use following packages
 * composer require fabpot/goutte
 */

class LeopardsCOD
{
    static protected $_username; // Leopard Username Handler
    static protected $_password; // Leopard Password Handler
    static protected $_api_key; // Leopard API Key Handler
    static protected $_api_password; // Leopard API Password Handler

    public function __construct($credentials = array())
    {
        if(!isset($credentials['username']) || !$credentials['username']) {
            throw new Exception("Username must be provided");
        }

        if(!isset($credentials['password']) || !$credentials['password']) {
            throw new Exception("Password must be provided");
        }

        if(!isset($credentials['api_key']) || !$credentials['api_key']) {
            throw new Exception("API Key must be provided");
        }

        if(!isset($credentials['api_password']) || !$credentials['api_password']) {
            throw new Exception("API Password must be provided");
        }

        self::$_username = $credentials['username'];
        self::$_password = $credentials['password'];
        self::$_api_key = $credentials['api_key'];
        self::$_api_password = $credentials['api_password'];
    }

    /*
     * Function to Get All Cities
     * @param: (void) null
     * @return: (array) $response
     */


    public function bookPacket($book_packet= array()) {
        $client = new GuzzleClient();
        $response = $client->post('http://new.leopardscod.com/webservice/bookPacketTest/format/json/', [
            RequestOptions::JSON => array(
                'api_key' => self::$_api_key,
                'api_password' => self::$_api_password,
                'booked_packet_weight' => $book_packet['booked_packet_weight'],
                'booked_packet_vol_weight_w' =>  $book_packet['booked_packet_vol_weight_w'],
                'booked_packet_vol_weight_l' => $book_packet['booked_packet_vol_weight_l'],
                'booked_packet_vol_weight_h' => $book_packet['booked_packet_vol_weight_h'],
                'booked_packet_no_piece' =>  $book_packet['booked_packet_no_piece'],
                'booked_packet_collect_amount' =>   $book_packet['booked_packet_collect_amount'],
                'booked_packet_order_id' =>  isset($book_packet['booked_packet_order_id']) ? $book_packet['booked_packet_order_id'] : null,
                'origin_city' => $book_packet['origin_city'],
                'destination_city' => $book_packet['destination_city'],
                'consignment_name_eng' => $book_packet['consignment_name_eng'],
                'consignment_email' =>  $book_packet['consignment_email'],
                'consignment_phone' =>  $book_packet['consignment_phone'],
                'consignment_phone_two' => isset($book_packet['consignment_phone_two']) ? $book_packet['consignment_phone_two'] : null,
                'consignment_phone_three' => isset($book_packet['consignment_phone_three']) ? $book_packet['consignment_phone_three'] : null,
                'consignment_address' => $book_packet['consignment_address'],
                'shipment_name_eng' =>  $book_packet['shipment_name_eng'],
                'shipment_email' =>  $book_packet['shipment_email'],
                'shipment_phone' =>  $book_packet['shipment_phone'],
                'shipment_address' =>  $book_packet['shipment_address'],
                'special_instructions' =>   $book_packet['special_instructions']

            )
        ]);

        if($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);

        } else {
            return array(
                'status'=>0,
                'error'=>"string",
                'track_number'=>null,
                'slip_link'=>null
            );
        }
    }



    public function getAllCities() {
        // Get All Cities List
        $client = new GuzzleClient();
        $response = $client->post('http://new.leopardscod.com/webservice/getAllCitiesTest/format/json/', [
            RequestOptions::JSON => array(
                'api_key' => self::$_api_key,
                'api_password' => self::$_api_password,
            )
        ]);
        $cityList = array();

        if($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);

        } else {
            return array(
                'status' => 0,
                'error' => 'Something went wrong with your request.',
                'city_list' => array(),
            );
        }
    }

    /*
     * Function to Get Customer ID
     * @param: (void) null
     * @return: (array) $response
     */
    public function getCompanyCode()
    {
        $response = array(
            'status' => 0,
            'companyId' => 0,
            'message' => '',
        );

        try {
            $client = new Client();
            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password

            ));

            // Grab CompanyID from returned HTML
            $company_profile = $client->request('GET', 'http://new.leopardscod.com/company/edit');
            $pattern = '/<input type=\"hidden\" name=\"companyId\" id=\"companyId\" value=\"(.*?)\"/i';
            preg_match($pattern, $company_profile->html(), $matches);

            if(isset($matches) && count($matches) == 2) {
                $response = array(
                    'status' => 1,
                    'companyId' => $matches[1],
                    'message' => '',
                );
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /*
     * Function to All Shippers
     * @param: (int) $companyId
     * @return: (array) $response
     */
    public function getAllShippers($companyId)
    {
        $response = array(
            'status' => 0,
            'shipperList' => array(),
            'message' => '',
        );

        try {
            $client = new Client();
            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));
            $shipment_result = $client->request('POST', 'http://new.leopardscod.com/shipment', array(
                'perPage' => 10,
                'searchString' => null,
                'company_id' => $companyId,
                'city_id' => -1,
                'orderBy' => 'date_created',
                'order' => 'desc',
                'status' => '-1',
                'ajax' => 1,
            ));

            $shipperList = array();

            $DOM = new \DOMDocument();
            $DOM->loadHTML($shipment_result->html());
            $rows = $DOM->getElementsByTagName("tr");
            if($rows->length) {
                for ($i = 0; $i < $rows->length; $i++) {
                    $cols = $rows->item($i)->getElementsbyTagName("td");
                    $count = 0;
                    $group = array();
                    for ($j = 0; $j < $cols->length; $j++) {
                        $count++;
                        if($count == 1 || $count == 2 || $count == 9) {
                            if($count == 9) {
                                $response['shipperList'][] = $group;
                                $count = 0;
                            }
                            continue;
                        }
                        $group[($count - 3)] = $cols->item($j)->textContent;
                    }
                }
            }

            $response['status'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /*
     * Function to All Consignees
     * @param: (int) $companyId
     * @return: (array) $response
     */
    public function getAllConsignees($companyId) {

        $response = array(
            'status' => 0,
            'consigneeList' => array(),
            'message' => '',
        );

        try {
            $client = new Client();

            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));
            $consignee_result = $client->request('POST', 'http://new.leopardscod.com/consignment', array(
                'perPage' => 10,
                'searchString' => null,
                'company_id' => $companyId,
                'city_id' => -1,
                'orderBy' => 'date_created',
                'order' => 'desc',
                'status' => '-1',
                'ajax' => 1,
            ));
//
            $DOM = new \DOMDocument();
            $DOM->loadHTML($consignee_result->html());
            $rows = $DOM->getElementsByTagName("tr");
            if($rows->length) {
                for ($i = 0; $i < $rows->length; $i++) {
                    $cols = $rows->item($i)->getElementsbyTagName("td");
                    $count = 0;
                    $group = array();
                    for ($j = 0; $j < $cols->length; $j++) {
                        $count++;
                        if($count == 1 || $count == 2 || $count == 9) {
                            if($count == 9) {
                                $response['consigneeList'][] = $group;
                                $count = 0;
                            }
                            continue;
                        }
                        $group[($count - 3)] = $cols->item($j)->textContent;
                    }
                }
            }
            $response['status'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
    /*
     * Function to create new Shipper
     * @param: (array) $Shipper
     * @return: (array) $response
     */
    public function createShipper($Shipper = array())
    {
        $response = array(
            'status' => 0,
            'message' => '',
        );

        try {
            $client = new Client();

            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));

            // Submit Form to Create Shipper
            $crawler = $client->request('GET', 'http://new.leopardscod.com/shipment/add');
            $form = $crawler->selectButton('Submit')->form();
            $client->submit($form, array(
                'cityId' => $Shipper['cityId'],
                'shipment_name_eng' => $Shipper['shipment_name_eng'],
                'shipment_email' => $Shipper['shipment_email'],
                'shipment_phone' => $Shipper['shipment_phone'],
                'shipment_address' => $Shipper['shipment_address'],
            ));

            $response['status'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
    /*
     * Function to create new Consignee
     * @param: (array) $Consignee
     * @return: (array) $response
     */
    public function createConsginee($Consignee = array()) {
        $response = array(
            'status' => 0,
            'message' => '',
        );

        try {
            $client = new Client();

            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));

            // Submit Form to Create Shipper
            $crawler = $client->request('GET', 'http://new.leopardscod.com/consignment/add');
            $form = $crawler->selectButton('Submit')->form();
            $client->submit($form, array(
                'cityId' => $Consignee['cityId'],
                'consignment_name_eng' => $Consignee['consignment_name_eng'],
                'consignment_email' => $Consignee['consignment_email'],
                'consignment_phone' => $Consignee['consignment_phone'],
                'consignment_phone_two' => $Consignee['consignment_phone_two'], // Optional Field
                'consignment_phone_three' => $Consignee['consignment_phone_three'], // Optional Field
                'consignment_address' => $Consignee['consignment_address'],
            ));

            $response['status'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
    /*
     * Function to get Shipper by Shipper ID
     * @param: (int) $ShipperId
     * @return: (array) $response
     */
    public function getShipperByID($ShipperId) {
        $response = array(
            'status' => 0,
            'shipperObj' => array(
                'city_id' => '',
                'shipper_id' => $ShipperId,
                'shipment_name_eng' => '',
                'shipment_email' => '',
                'shipment_phone' => '',
                'shipment_address' => '',
            ),
            'message' => '',
        );
        try {
            $client = new Client();
            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));

            // Submit Form to Create Shipper
            $crawler = $client->request('GET', 'http://new.leopardscod.com/shipment/edit/' . $ShipperId);
            $shipperHTML = $crawler->html();
            // Get Shipper City
            $pattern = '/<option  selected=\"selected\"  value=\"(.*?)\">(.*?)<\/option>/i';
            preg_match($pattern, $shipperHTML, $cities);
            if(isset($cities[1])) {
                $response['shipperObj']['city_id'] = $cities[1];
            } else {
                $pattern = '/<option selected value=\"(.*?)\">(.*?)<\/option>/i';
                preg_match($pattern, $shipperHTML, $cities);
                if(isset($cities[1])) {
                    $response['shipperObj']['city_id'] = $cities[1];
                }
            }
            // Get Shipper Name
            $pattern = '/<input value=\"(.*?)\" name=\"shipment_name_eng\"/i';
            preg_match($pattern, $shipperHTML, $names);
            if(isset($names[1])) {
                $response['shipperObj']['shipment_name_eng'] = $names[1];
            }
            // Get Shipper Email
            $pattern = '/<input value=\"(.*?)\" type=\"text\" name=\"shipment_email\"/i';
            preg_match($pattern, $shipperHTML, $emails);
            if(isset($emails[1])) {
                $response['shipperObj']['shipment_email'] = $emails[1];
            }

            // Get Shipper Phone
            $pattern = '/<input value=\"(.*?)\" type=\"text\" name=\"shipment_phone\" id=\"shipment_phone\"/i';
            preg_match($pattern, $shipperHTML, $phones);
            if(isset($phones[1])) {
                $response['shipperObj']['shipment_phone'] = $phones[1];
            }
            // Get Shipper Address
            $pattern = '/<textarea id=\"shipment_address\" name=\"shipment_address\" style=\"(.*?)\">(.*?)<\/textarea>/i';
            preg_match($pattern, $shipperHTML, $addresses);
            if(isset($addresses[2])) {
                $response['shipperObj']['shipment_address'] = $phones[1];
            }
            $response['status'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /*
     * Function to get Consginee by Consignee ID
     * @param: (int) $ConsigneeId
     * @return: (array) $response
     */
    public function getConsigneeByID($ConsigneeId) {

        $response = array(
            'status' => 0,
            'consigneeObj' => array(
                'city_id' => '',
                'consignment_id' => $ConsigneeId,
                'consignment_name_eng' => '',
                'consignment_email' => '',
                'consignment_phone' => '',
                'consignment_phone_two' => '', // Optional Field
                'consignment_phone_three' => '', // Optional Field
                'consignment_address' => '',
            ),
            'message' => '',
        );

        try {
            $client = new Client();

            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));
            // Submit Form to Create Consignee
            $crawler = $client->request('GET', 'http://new.leopardscod.com/consignment/edit/' . $ConsigneeId);
            $consigneeHTML = $crawler->html();
            // Get Consignee City
            $pattern = '/<option  selected=\"selected\"  value=\"(.*?)\">(.*?)<\/option>/i';
            preg_match($pattern, $consigneeHTML, $cities);
            if(isset($cities[1])) {
                $response['consigneeObj']['city_id'] = $cities[1];
            } else {
                $pattern = '/<option selected value=\"(.*?)\">(.*?)<\/option>/i';
                preg_match($pattern, $consigneeHTML, $cities);
                if(isset($cities[1])) {
                    $response['consigneeObj']['city_id'] = $cities[1];
                }
            }

            // Get Consignee Name
            $pattern = '/<input value=\"(.*?)\" name=\"consignment_name_eng\"/i';
            preg_match($pattern, $consigneeHTML, $names);
            if(isset($names[1])) {
                $response['consigneeObj']['consignment_name_eng'] = $names[1];
            }

            // Get Consignee Email
            $pattern = '/<input value=\"(.*?)\" type=\"text\" name=\"consignment_email\"/i';
            preg_match($pattern, $consigneeHTML, $emails);
            if(isset($emails[1])) {
                $response['consigneeObj']['consignment_email'] = $emails[1];
            }

            // Get Consignee Phone
            $pattern = '/<input maxlength=\"15\" value=\"(.*?)\" type=\"text\" name=\"consignment_phone\" id=\"consignment_phone\"/i';
            preg_match($pattern, $consigneeHTML, $phones);
            if(isset($phones[1])) {
                $response['consigneeObj']['consignment_phone'] = $phones[1];
            }

            // Get Consignee Phone Two
            $pattern = '/<input maxlength=\"15\" value=\"(.*?)\" type=\"text\" name=\"consignment_phone_two\" id=\"consignment_phone_two\"/i';
            preg_match($pattern, $consigneeHTML, $phones);
            if(isset($phones[1])) {
                $response['consigneeObj']['consignment_phone_two'] = $phones[1];
            }

            // Get Consignee Phone
            $pattern = '/<input maxlength=\"15\" value=\"(.*?)\" type=\"text\" name=\"consignment_phone_three\" id=\"consignment_phone_three\"/i';
            preg_match($pattern, $consigneeHTML, $phones);
            if(isset($phones[1])) {
                $response['consigneeObj']['consignment_phone_three'] = $phones[1];
            }

            // Get Consignee Address
            $pattern = '/<textarea id=\"consignment_address\" name=\"consignment_address\" style=\"(.*?)\">(.*?)<\/textarea>/i';
            preg_match($pattern, $consigneeHTML, $addresses);
            if(isset($addresses[2])) {
                $response['consigneeObj']['consignment_address'] = $addresses[2];
            }

            $response['status'] = true;
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    static private $_client;

    /*
     * Get Logged In Session
     * @param: (int) $companyId
     * @return: (array) $response
     */

    public function getSession()
    {
        $response = array(
            'status' => 0,
        );

        try {
            $client = new Client();
            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));


            if (strpos($login_result->text(),'Change Password') !== false) {
                $response['status'] = true;
                self::$_client = $client;
            }

            return $response;
        } catch (Exception $e) {
            return $response;
        }
    }

    /*
     * Get Single Company Information
     * @param: (int) $companyId
     * @return: (array) $response
     */
    public function getSingleCompany($companyId)
    {
        $response = array(
            'status' => 0,
            'companyData' => array(
                'id' => $companyId,
                'name' => '',
                'address' => '',
                'email' => '',
                'phone' => '',
            ),
            'message' => '',
        );

        try {

            $company_result = self::$_client->request('GET', 'http://new.leopardscod.com/company/companyDetails/' . $companyId);

            $DOM = new \DOMDocument();
            $DOM->loadHTML($company_result->html());
            $rows = $DOM->getElementsByTagName("h1");



            if($rows->length) {
                for ($i = 0; $i < $rows->length; $i++) {

                    if($i == 0) {
                        $response['companyData']['name'] = trim($rows->item($i)->textContent);
                        continue;
                    }

                    if($i == 1) {
                        $response['companyData']['address'] = trim(str_replace('Address:', '', trim($rows->item($i)->textContent)));
                        continue;
                    }

                    if($i == 2) {
                        $response['companyData']['phone'] = trim(str_replace('Phone:', '', trim($rows->item($i)->textContent)));
                        continue;
                    }

                    if($i == 3) {
                        $response['companyData']['email'] = trim(str_replace('Email:', '', trim($rows->item($i)->textContent)));
                        continue;
                    }

                    break;
                }
            }

            $response['status'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /*
     * Function to All Companies
     * @return: (array) $response
     */
    public function getAllCompanies($page = 0, $perPage = 100)
    {
        $response = array(
            'status' => 0,
            'companyList' => array(),
            'message' => '',
        );

        try {
            $client = new Client();
            $crawler = $client->request('GET', 'http://new.leopardscod.com/login');
            $form = $crawler->selectButton('Login')->form();
            $login_result = $client->submit($form, array(
                'username' => self::$_username,
                'password' => self::$_password
            ));

            if (strpos($login_result->text(),'Change Password') !== false) {
                $company_result = $client->request('POST', 'http://new.leopardscod.com/company/index/'.$page, array(
                    'perPage' => $perPage,
                    'searchString' => '',
                    'orderBy' => 'company_id',
                    'order' => 'ASC',
                    'status' => '-1',
                    'country_id' => '-1',
                    'date_created' => '',
                    'city_id' => '-1',
                    'ajax' => 1,
                ));

                $DOM = new \DOMDocument();
                $DOM->loadHTML($company_result->html());
                $rows = $DOM->getElementsByTagName("tr");
                if($rows->length) {
                    for ($i = 0; $i < $rows->length; $i++) {
                        $cols = $rows->item($i)->getElementsbyTagName("td");
                        $count = 0;
                        $group = array();
                        for ($j = 0; $j < $cols->length; $j++) {
                            $count++;
                            if($count == 1 || $count == 2 || $count == 9 || $count == 10) {
                                if($count == 10) {
                                    $response['companyList'][] = $group;
                                    $count = 0;
                                }
                                continue;
                            }
                            $group[($count - 3)] = trim(preg_replace('/\s+/', ' ', $cols->item($j)->textContent));
                        }
                    }
                }

                $response['status'] = true;
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

}