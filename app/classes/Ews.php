<?php
namespace App\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

require_once($_SERVER['DOCUMENT_ROOT'] . '/ews/vendor/autoload.php');

class Ews
{
    public static function callAlmaApi($endpoint, $requesttype, $object, $lang='en', $override='false')
    {
        try {
            $api_key = env("ALMA_API_KEY", "missing");
        } catch(\Exception $e) {
            $responseobject = array(
                "status"  => "Error",
                "message" => $e->getMessage()
            );
            return response()->json($responseobject, 400);
        }
    }
}

?>