<?php

namespace App\Traits;

trait NotificationTrait {

    // Flatten nested array
    public function flattenArray($data1) {
        $result = [];

        foreach ($data1 as $key => $value) {
            if (is_array($value)) {
                // Recursively flatten the nested array
                $result = array_merge($result, $this->flattenArray($value));
            } else {
                // Add the non-array value to the result
                $result[$key] = $value;
            }
        }

        return $result;
    }

    // Send Notification
    public function sendNotification($title, $notification_data, $data, $registrationIdsArray)
    {

        try {

                // Flatten the data array
                $output = $this->flattenArray($data);

                // Firebase credentials and service account
                $serviceAccountFile = storage_path('firebase/fleet-transport-firebase-adminsdk-me8q2-54328fe689.json');

                // Load the service account credentials
                $serviceAccount = json_decode(file_get_contents($serviceAccountFile), true);

                // Create JWT
                $now_seconds = time();
                $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                $jwtClaimSet = json_encode([
                    'iss' => $serviceAccount['client_email'],  // Issuer
                    'sub' => $serviceAccount['client_email'],  // Subject
                    'aud' => 'https://oauth2.googleapis.com/token',  // Audience
                    'iat' => $now_seconds,  // Issued at
                    'exp' => $now_seconds + 3600,  // Expiration (1 hour)
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',  // Scope
                ]);

                // Encode the header and claims
                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($jwtHeader));
                $base64UrlClaimSet = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($jwtClaimSet));

                // Create the signature using the private key
                $signatureInput = $base64UrlHeader . '.' . $base64UrlClaimSet;
                openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'SHA256');
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

                // Create the complete JWT
                $jwt = $base64UrlHeader . '.' . $base64UrlClaimSet . '.' . $base64UrlSignature;

                // Get the OAuth 2.0 token using the JWT
                $tokenUrl = 'https://oauth2.googleapis.com/token';
                $postFields = http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

                // Send cURL request to get the access token
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $tokenUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                $tokenResponse = curl_exec($ch);

                if ($tokenResponse === FALSE) {
                    die('Error obtaining access token: ' . curl_error($ch));
                }

                $tokenData = json_decode($tokenResponse, true);
                $accessToken = $tokenData['access_token'];
                curl_close($ch);

                $notify = array_map('strval', $output);

                $message = [
                    'message' => [
                        'notification' => [
                            'title' => $title,
                            'body'  => 'By ' . $notification_data['name'],
                        ],
                        'data' => $notify,
                        'token' => $registrationIdsArray,
                    ],
                ];

                if(isset($notification_data['image']) && $notification_data['image'] !== null)
                {
                    $message['message']['notification']['image'] = $notification_data['image'];
                }

                // Send the notification via Firebase Cloud Messaging
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/fleet-transport/messages:send');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer $accessToken",
                    "Content-Type: application/json",
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

                $response = curl_exec($ch);

                if ($response === FALSE) {
                    die('FCM request failed: ' . curl_error($ch));
                }

                $responseDecoded = json_decode($response, true);

                

                curl_close($ch);

                // Check the response for success
                if (isset($responseDecoded['name'])) {
            

                    return 1; // Notification sent successfully
                } elseif (isset($responseDecoded['error'])) {
                

                    return 0; // Error occurred in sending notification
                } else {

                    return 0; // Unknown error
                }
        }
        catch (\Exception $e) {
            return 0; 
        }
    }
}
