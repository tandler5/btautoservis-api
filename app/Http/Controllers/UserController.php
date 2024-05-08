<?php
 
namespace App\Http\Controllers;

use App\Models\CustomerNotificationTokens;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function getStatusMessage($status)
    {
        $newObject = new \stdClass();

        switch ($status) {
            case 'cancelled':
                $newObject->title =  "Vaše zakázka byla zrušena";
                $newObject->message =  "";
                break;
            case 'přijato':
                $newObject->title =  "Vaše zakázka byla přijata";
                $newObject->message =  "";
                break;

            case 'Čeká_se_na_díly':
                $newObject->title =  "Vaše zakázka čeká na díly";
                $newObject->message =  "";
                break;

            case 'vyřizuje_se':
                $newObject->title =  "Vaši zakázku právě vyřizujeme";
                $newObject->message =  "";
                break;

            case 'k_vyzvednutí':
                $newObject->title =  "Vaše zakázka je připravena k vyzvednutí";
                $newObject->message =  "";
                break;

            case 'approved':
                $newObject->title =  "Vaše zakázka je dokončena";
                $newObject->message =  "";
                break;

            case 'reklamace':
                $newObject->title =  "Přijali jsme Vaši zakázku k reklamaci";
                $newObject->message =  "";
                break;

            case 'vyřízená_reklamace':
                $newObject->title =  "Vyše reklamace byla vyřízena";
                $newObject->message =  "";
                break;
            default:
                $responseMessage = "Špatný stav zakázky: $status";
                $responseData = response()->json($responseMessage, 500, [], JSON_UNESCAPED_UNICODE);
                abort($responseData);
        }
        return $newObject;
    }
    
    // Routy jsou nasměrovány na tuto funkci a podobné
    public function notify(Request $request)
    {
        $postData = $request->all();
        $customerId = $request->input('customer.id');
        $customerId = 168;
        if(!isset($customerId)){
            throw new \Exception("No customer found", 404);
        }

        // Získání tokenů z databáze
        $userTokens = CustomerNotificationTokens::where('object',$customerId)->get();

        // Kontrola, zda $userTokens obsahuje data
        if($userTokens->isEmpty()) {
            throw new \Exception("No tokens found for the customer", 404);
        }

        // Přemapování tokenů
        $status = $postData['status'];

        if ($status) {
            $mappedUserTokens =  $userTokens->map(function ($object) use ($status) {
                $newObject = new \stdClass();
                // Naplňte nový objekt daty z původního objektu
                $informations = $this->getStatusMessage($status);
                $newObject->title = $informations->title;
                $newObject->message = "s";
                $newObject->to = $object->token;
                return $newObject;
            });
            $body = urldecode($mappedUserTokens);
            
            $url = "https://exp.host/--/api/v2/push/send";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 1);

            $headers = array(
            "Content-Type: application/json",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $data = <<<DATA
            $body
            DATA;

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $responseContent = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = substr($responseContent, 0, $header_size);

            curl_close($curl);

            $parts = explode("\r\n\r\nHTTP/", $responseContent);
            $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
            list($headersString, $body) = explode("\r\n\r\n", $parts, 2);
            
            $headersArray = explode("\n", $headersString);
            
            
            $headers = [];
            
            foreach ($headersArray as $header) {
                // Ignoruj prázdné řádky
                if (!empty($header)) {
                    // Rozdělení řádku na název a hodnotu
                    $parts = explode(': ', $header, 2);
                    $name = trim($parts[0]);
                    $value = trim($parts[1] ?? '');
                    
                    // Přidání hlavičky do pole
                    $headers[$name] = $value;
                }
            }

            $dataArray = json_decode($body)->data;

            foreach ($dataArray as $key=>$value) {
                $notification = $dataArray[$key];
                $message = isset($notification->message) ? $notification->message : null;
                NotificationLog::create(['customer_token' => $userTokens[$key]->id, 'status' => $notification->status, 'message' => $message ]);
            }
            $response = new Response($body);
            $response->withHeaders($headers);
            

            return $response;
        
        }
    }
}
