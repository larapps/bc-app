<?php

namespace Larapps\BigcommerceApp;

use Larapps\BigcommerceApp\Interfaces\App;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Larapps\BigcommerceApp\Models\BCStore;
use Illuminate\Support\Facades\Log;
use Session;

class BigcommerceApp implements App
{
    private $bcStore;

    public function construct(BCStore $bcStore)
    {
        $this->bcStore = $bcStore;

    }

    public function install(Request $request)
    {

        /** APP BASE URL, IF COMES IN HTTP:// */
        $appRootUrl = $request->root();
        if(strpos($appRootUrl, 'http://') !== FALSE){
            $appRootUrl = str_replace('http://','https://',$appRootUrl);
        }
        /** APP BASE URL, IF COMES IN HTTP:// */

        if (!$request->has('code') || !$request->has('scope') || !$request->has('context')) {
            return ['error_message' => 'Not enough information was passed to install this app.'];
        }

        try {
            $client = new Client();
            $result = $client->request('POST', 'https://login.bigcommerce.com/oauth2/token', [
                'json' => [
                    'client_id' => env('APP_CLIENT_ID'),
                    'client_secret' => env('APP_SECRET_KEY'),
                    'redirect_uri' => $appRootUrl . '/auth/install',
                    'grant_type' => 'authorization_code',
                    'code' => $request->input('code'),
                    'scope' => $request->input('scope'),
                    'context' => $request->input('context'),
                ]
            ]);

            $statusCode = $result->getStatusCode();
            $data = json_decode($result->getBody(), true);

            if ($statusCode == 200) {
                $this->upsertStoreDetails($data['context'], $data['access_token'], $data['user']['email'],'');
                
                return [
                    "status" => "success",
                    "data" => [
                        "user_id" => $data['user']['id'],
                        "user_email" => $data['user']['email'],
                        "store_hash" => BCStore::getHashOnly(BCStore::getHashOnly($data['context'])),   
                    ]
                ];
            }

            return [
                "status" => "success",
                "message" => "App Installed Successfully."
            ];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = "An error occurred.";

            if ($e->hasResponse()) {
                if ($statusCode != 500) {
                    $errorMessage = $e->getResponse();
                }
            }

            // If the merchant installed the app via an external link, redirect back to the 
            // BC installation failure page for this app
            if ($request->has('external_install')) {
                return Redirect::to('https://login.bigcommerce.com/app/' . env('APP_CLIENT_ID') . '/install/failed');
            } else {
                return ['error_message' => $errorMessage];
            }
            return [
                "status" => "error",
                "message" => "We couldn't fulfill your request at the moment, Please try again later."
            ];
        }
    }

    public function load(Request $request)
    {
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {
                return [
                    "status" => "success",
                    "data" => [
                        "user_id" => $verifiedSignedRequestData['user']['id'],
                        "user_email" => $verifiedSignedRequestData['user']['email'],
                        "owner_id" => $verifiedSignedRequestData['owner']['id'],
                        "owner_email" => $verifiedSignedRequestData['owner']['email'],
                        "store_hash" => BCStore::getHashOnly($verifiedSignedRequestData['context']),   
                    ]
                ];

            } else {
                return ['status' => 'error', 'error_message' => 'The signed request from BigCommerce could not be validated.'];
            }
        } else {
            return ['status' => 'error', 'error_message' => 'The signed request from BigCommerce was empty.'];
        }

        $request->session()->regenerate();

        return [
            "status" => "success",
            "message" => "App Loaded Successfully."
        ];
    }

    public function uninstall(Request $request)
    {
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {

                $this->bcStore = BCStore::where('store_hash','=', $verifiedSignedRequestData['store_hash'])->get();

                if($this->bcStore->count() > 0){
                    $this->bcStore = $this->bcStore[0];
                    $this->bcStore->delete();
                    return [
                        "status" => "success",
                        "message" => "App uninstalled Successfully."
                    ];
                }
            }
        }
    }

    private function upsertStoreDetails($fullStoreHash, $accessToken, $userEmail, $ownerEmail){
        $storeHash = BCStore::getHashOnly($fullStoreHash);
        $this->bcStore = BCStore::where('store_hash','=',$storeHash)->get();

        if($this->bcStore->count() == 0){
            $this->bcStore = new BCStore();
        }else{
            $this->bcStore = $this->bcStore[0];
        }

        $this->bcStore->store_hash = $storeHash;

        if($accessToken)
            $this->bcStore->access_token = $accessToken;
        if($userEmail)
            $this->bcStore->user_email = $userEmail;
        if($ownerEmail)
            $this->bcStore->admin_email = $ownerEmail;

        $this->bcStore->is_removed = false;
        $this->bcStore->save();

        $this->bcStore = $this->bcStore;
    }

    private function verifySignedRequest($signedRequest, $appRequest)
    {
        list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);

        // decode the data
        $signature = base64_decode($encodedSignature);
        $jsonStr = base64_decode($encodedData);
        $data = json_decode($jsonStr, true);

        // confirm the signature
        $expectedSignature = hash_hmac('sha256', $jsonStr, env("APP_SECRET_KEY"), $raw = false);
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('Bad signed request from BigCommerce!');
            return null;
        }
        return $data;
    }
}
