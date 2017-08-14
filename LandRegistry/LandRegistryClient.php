<?php
namespace LandRegistry;

use GuzzleHttp\Client;

class LandRegistryClient
{
    private static $instance;
    private static $xmlConfigFile = __DIR__.'/config/config_api.xml';
    private $accessToken;
    private $base_url;

    private function __construct() {
        $xml = simplexml_load_file( self::$xmlConfigFile );
        $auth_client = new Client();
        $response = $auth_client->post($xml->base_url.'/oauth/v2/token', [
            'form_params' => [
                'client_id' => (string) $xml->client_id,
                'client_secret' => (string) $xml->client_secret,
                'grant_type' => (string) $xml->grant_type
            ]
        ]);
        $this->base_url = (string) $xml->base_url;
        $this->setAccessToken(json_decode($response->getBody()->getContents())->access_token);
    }

    public static function getInstance() {
        if( empty( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    private function sendRequest($method, $api_url, $params = []) {
        $client = new Client();
        $response = $client->request(
            $method,
            $this->base_url.$api_url,
            [
                'headers' =>
                [
                    'Authorization' => "Bearer {$this->getAccessToken()}"
                ],
                'form_params' => $params
            ]
        )->getBody()->getContents();
        return $response;
    }

    public function getRegions() {
        return $this->sendRequest( 'GET', '/api/get/regions/json' );
    }

    public function getCnapsList( $region_id ) {
        return $this->sendRequest('GET',  sprintf( '/api/get/cnaps/%s/json', $region_id ) );
    }

    public function getCnapDetails( $cnap_id ) {
        return $this->sendRequest( 'GET', sprintf( '/api/get/cnap/detail/%s/json', $cnap_id ) );
    }

    public function getRequestType() {
        return $this->sendRequest( 'GET', '/api/get/request_type/json' );
    }

    public function getTypeUser() {
        return $this->sendRequest( 'GET', '/api/get/type_user/json' );
    }

    public function getTransmitAsType() {
        return $this->sendRequest( 'GET', '/api/get/transmit_as_type/json' );
    }

    public function checkCadNumExist( $cadaster_number ) {
        return $this->sendRequest( 'GET', sprintf( '/api/excerpt/check/cadnum/%s/json', $cadaster_number ) );
    }

    public function orderRequest( $formData ) {
        return $this->sendRequest( 'POST', '/api/excerpt/json', $formData );
    }
}

