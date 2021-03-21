<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AddLoadRequest;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\User;
use App\Models\Emails;
use App\Models\Bids;
use Illuminate\Support\Facades\DB;
use Mail;
use Exception;
use Illuminate\Support\Facades\Storage;

class MailController extends Controller
{
    
    public function sendMail(Request $request) {
        $data = [];        
        $data = $request->all();        
        // print_r($data);
        foreach($data['files'] as $key => $file) {
            $image_parts = explode(";base64,", $file);
            $image_type_aux = explode("/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image = base64_decode($image_parts[1]);
            $name = time() . uniqid() . "." . $image_type;
            \File::put(public_path('files/'.$name),  $image);
            $data['file'][] = public_path('files/'.$name);                        
            //print_r($image);
        }
        
        //die('here');
        try {            

            
            Mail::send(['html' => 'emails.sendemail'], ['text' => $data],function ($message) use($data) {
                $message->from($data['senderemail'], $name = env('MAIL_FROM_NAME'));
                $message->to($data['recepientemail']);
                $message->subject($data['subject']);                     
                foreach ($data['file'] as $file){
                    $message->attach($file);
                }
                $data = array(
                    'from' => $data['senderemail'],
                    'to' => $data['recepientemail'],
                    'subject' => $data['subject'],
                    'textContent' => $data['textContent'],
                    'htmlContent' => $data['htmlContent'],
                    'file' => json_encode($data['file']),
                    'status' => 1,
                );
                $is_inserted = Emails::create($data);
                if($is_inserted) {
                    return response()->json(['message' => 'Your email sent successfully, it will appear at your inbox or spam folder', 'status' => 200], 200);
                }
                else {
                    return response()->json(['error' => 'Unable to send email. Please try again later', 'status' => 402], 402);
                }
            });
 
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 402], 402);
        }

    }

    public function getsinglemail($id)
    {        
        try {            
                $load = Emails::where(['id' => $id])
                ->first();            
            if($load) {
                return response()->json(['message' => 'Email detail loaded successfully', 'status' => 200, 'mail_detail' => $load], 200);
            }
            else {
                return response()->json(['error' => 'Unable to find any data', 'status' => 402], 402);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 402], 402);
        }
    }
    public function getmails(Request $request)
    {
        $data = array();
        try {
            $data = $request->all();            
            if($data['searchby'] == 'sender'){
                $load = Emails::where('from','LIKE', '%'.$data['searchstring'].'%')
                ->get();
            }else if($data['searchby'] == 'recepient'){
                $load = Emails::where('to','LIKE', '%'.$data['searchstring'].'%')
                ->get();
            }else if($data['searchby'] == 'subject'){
                $load = Emails::where('subject','LIKE', '%'.$data['searchstring'].'%')
                ->get();
            }
            if($load) {
                return response()->json(['message' => 'Emails loaded successfully', 'status' => 200, 'all_emails' => $load], 200);
            }
            else {
                return response()->json(['error' => 'Unable to find any data', 'status' => 402], 402);
            }
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 402], 402);
        }
    }

}
