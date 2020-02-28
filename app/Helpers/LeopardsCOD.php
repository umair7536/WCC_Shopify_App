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
        if(!isset($credentials['api_key']) || !$credentials['api_key']) {
            throw new Exception("API Key must be provided");
        }

        if(!isset($credentials['api_password']) || !$credentials['api_password']) {
            throw new Exception("API Password must be provided");
        }

        self::$_username = null;
        self::$_password = null;
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
}