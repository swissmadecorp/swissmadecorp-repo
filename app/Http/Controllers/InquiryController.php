<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Inquiry;
use Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellEmail;
use App\Mail\InquiryEmail; 
use App\Models\Newsletter;
use App\Mail\GMailer;
//use App\Mail\GmailCustomer; 

class InquiryController extends Controller
{
    public function index() {
        $inquiries = Inquiry::latest()->get();
        return view('admin.inquiries',['pagename' => 'Inquiry Page','inquiries' => $inquiries]);
    }

    public function show($id) {
        $inquiry = Inquiry::find($id);
        $product = Product::find($inquiry->product_id);
        return view('admin.inquiries.show',['pagename' => 'View Inquiry','inquiry' => $inquiry,'product'=>$product]);
    }

    public function SellYourWatch(Request $request) {
        //parse_str($request['inquiry'],$inputs);
        //$filename=$request->file('thefile_1')->getClientOriginalName();
        if ($request->ajax()) {
            $data = array(
                'contact_name'=>strip_tags($request['sell_contact_name']),
                'email' => strip_tags($request['sell_email']),
                'phone' => strip_tags($request['sell_phone']),
                'notes'=>strip_tags($request['sell_notes']),
                'filename' => strip_tags($request['filename']),
                'subject'=>'Swissmade - I want to sell my watch',
                'template' => 'emails.sellwatch',
            );

            
            // $gmail = new GmailCustomer($data);
            // $gmail->send();
            $gmailer = new GMailer($data);
            $gmailer->send();

            return $data;
            
        }
        //Mail::to('info@swissmadecorp.com')->queue(new SellEmail($data));

        //return $data;
    }

    private function saveEmailForNewsLetter($email) {
        if (getClientIP()=='107.164.78.179') return ;
        $count = Newsletter::select('email')->where('email',$email)->count();
        if (!$count)
            Newsletter::create([
                'email' => $email,
                'subscribed' => 1
            ]);
    
    }

    public function priceOffer(Request $request) {
        if (getClientIP()=='107.164.78.179') return ;
        // return response()->json(array('error'=>'spam'));
        if ($request->ajax()) {
            
            parse_str($request['priceoffer'],$inputs);
            $validator = \Validator::make($inputs, [
                'offer_full_name' => 'required',
                'offer_email' => 'required',
                'offer_amount' => 'required|numeric|min:3',
                'g-recaptcha-response' => 'required|captcha'
            ]);

            \Validator::extend('captcha', function($attribute, $value, $parameters, $validator) use($inputs){ 
                return captcha_check($value); 
            });
            
            //return "Hello";
            $response = $inputs["g-recaptcha-response"];
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret' => config('recapcha.secret_v2'),
                'response' => $response
            );

            $options = array(
                'http' => array (
                    'method' => 'POST',
                    'content' => http_build_query($data),
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                    "User-Agent:MyAgent/1.0\r\n",
                )
            );
            $context  = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $captcha_success=json_decode($verify);
            \Log::debug(print_r($captcha_success,true));

            if ($captcha_success->success == false) 
                return response()->json(array('error'=>$captcha_success->error-codes));

            $errors = array();
            
            // if ($validator->fails()) {
            //     foreach ($validator->errors()->all() as $error){
            //         $errors[] = $error;
            //     }
            //     return response()->json(array('error'=>$errors));
            // }

            $product_id=$inputs['product_offer_id'];
            $email = strip_tags($inputs['offer_email']);
            $amount = strip_tags($inputs['offer_amount']);

            if (!is_numeric($amount)) {
                return response()->json(array('error'=>'nonumeric'));
            }

            if ($product_id != 0) {
                // Inquiry::create([
                //     'product_id' => $product_id,
                //     'contact_name' => allFirstWordsToUpper(strip_tags($inputs['offer_full_name'])),
                //     'email' => $email,
                //     'phone' => $phone,
                //     'notes' => $notes
                // ]);

                if ($email)
                    $this->saveEmailForNewsLetter($email);

                $product = Product::find($product_id);
                
                // if (number_format($amount,2, '.', '') <= $product->p_newprice)  {
                //     return response()->json(array('error'=>'nomatch'));
                // }

                $data = array(
                    'to' => 'info@swissmadecorp.com',
                    'replyTo' => $email,
                    'item' => $product->title . " (" . $product_id . ")",
                    'fullname'=>strip_tags($inputs['offer_full_name']),
                    'image' => count($product->images)>0 ? $product->images->first()->location : 0,
                    'email' => $email,
                    'price' => number_format($amount,2),
                    'phone' => "",
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'subject'=>'Price offer',
                    'template' => 'emails.priceoffer',
                );
                //return response()->json($data);
                // Mail::to('info@swissmadecorp.com')->queue(new InquiryEmail($data));
                
                // $gmail = new GmailCustomer($data);
                // $gmail->send();
                $gmailer = new GMailer($data);
                $gmailer->send();

                return response()->json(array('error'=>'success'));
            } else {
                return response()->json(array('error'=>'spam'));
            }
        }
    }

    public function store(Request $request) {
        if (getClientIP()=='107.164.78.179') return ;
        if ($request->ajax()) {
            
            parse_str($request['inquiry'],$inputs);

            $validator = \Validator::make($inputs, [
                'company_name' => 'required',
                'phone' => 'required',
                'g-recaptcha-response' => 'required|captcha'
            ]);

            \Validator::extend('captcha', function($attribute, $value, $parameters, $validator) use($inputs){ 
                return captcha_check($value); 
            });

            $response = $inputs["g-recaptcha-response"];
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret' => config('recapcha.secret_v2'),
                'response' => $response
            );

            $options = array(
                'http' => array (
                    'method' => 'POST',
                    'content' => http_build_query($data),
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                    "User-Agent:MyAgent/1.0\r\n",
                )
            );
            $context  = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $captcha_success=json_decode($verify);
            
            if ($captcha_success->success == false) {
                \Log::debug($captcha_success);
                return response()->json(array('error'=>$captcha_success->error-codes));
            }
            // $errors = array();
            // if ($validator->fails()) {
            //     foreach ($validator->errors()->all() as $error){
            //         $errors[] = $error;
            //     }
            //     return response()->json(array('error'=>$errors));
            // }

            $product_id=$inputs['product_id'];
            $email = strip_tags($inputs['email']);
            $phone = strip_tags($inputs['phone']);
            $notes = strip_tags($inputs['notes']);

            if ($product_id != 0) {
                Inquiry::create([
                    'product_id' => $product_id,
                    'contact_name' => allFirstWordsToUpper(strip_tags($inputs['contact_name'])),
                    'company_name' => allFirstWordsToUpper(strip_tags($inputs['company_name'])),
                    'email' => $email,
                    'phone' => $phone,
                    'notes' => $notes
                ]);

                if ($email)
                    $this->saveEmailForNewsLetter($email);

                $product = Product::find($product_id);
                
                $data = array(
                    'to' => 'info@swissmadecorp.com',
                    'replyTo' => $email,
                    'product' => $product->title,
                    'product_id' => $product_id,
                    'fullname'=>strip_tags($inputs['contact_name']),
                    'image' => count($product->images)>0 ? $product->images->first()->location : 0,
                    'email' => $email,
                    'phone' => $phone,
                    'notes'=>$notes,
                    'subject'=>'You have a new inquiry',
                    'template' => 'emails.test',
                );
                
                //return response()->json($data);
                // Mail::to('info@swissmadecorp.com')->queue(new InquiryEmail($data));
                
                // $gmail = new GmailCustomer($data);
                // $gmail->send();
                $gmailer = new GMailer($data);
                $gmailer->send();

                return response()->json(array('error'=>'success'));
            } else {
                return response()->json(array('error'=>'spam'));
            }
        }
    }

    public function destroy($id)
    {
        
        $inquiry = Inquiry::find($id);
        $inquiry->delete();

        Session::flash('message', "Successfully deleted inquiry!");
        return redirect('admin/inquiries');
    }
}
