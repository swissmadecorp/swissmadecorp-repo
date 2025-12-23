<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function unsubscribe(Request $request) {
        return view('unsubscribe',array('email'=>$request['email']));
    }

    public function success(Request $request) {
        if ($request['email']) {
            $email = Newsletter::where('email',$request['email']);
                        
            if ($email->exists()) {
                //dd($email);
                $email->update(array('subscribed' => 0));
                return view('unsubscribesuccess');
            }

            return redirect()->action('NewsletterController@unsubscribe',['email' => 'notfound']);
        }

        return redirect()->action('NewsletterController@unsubscribe',['email' => 'notfound']);
    }

    public function index() {
        
    }
}
