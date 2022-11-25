<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Session extends Model
{
    use HasFactory;

    public function charge()
    {
        return $this->hasOne(Charge::class, 'shop', 'shop');
    }

    public function graph($body, $customHeaders = [])
    {
        // Log::info(json_encode($body));
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->shop . '/admin/api/2022-10/graphql.json');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'X-Shopify-Access-Token: ' . $this->access_token . '';

            if (count($customHeaders) > 0) {
                foreach ($customHeaders as $key => $value) {
                    $headers[] = $key . ": " . $value;
                }
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            $response = json_decode($result, true);

            return $response;
        } catch (Exception $e) {
            Log::error("Error of $this->shop ==> " . $e->getMessage());
            return null;
        }
    }
}
