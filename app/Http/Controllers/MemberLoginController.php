<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\MemberLogin;
use Auth;

class MemberLoginController extends Controller
{

    public function logout() {
        Auth::guard('customer')->logout();
        return redirect()->intended(url()->previous());
    }

    public function loginMember(Request $request) {
        
        // $user = new MemberLogin();
        // $user->username = 'dealers';
        // $user->customer_id = 0;
        // $user->password = Hash::make('welcome');
        // $user->save();

        if ($request->ajax()) {
            
            parse_str($request,$output);
            
            $email = $output['name'];
            $password = $output['password'];
            
            if (Auth::guard('customer')->attempt(['username'=>$email,'password'=>$password],true)) {
                
                $msg = array(
                    'status' => 'success',
                    'message' => "Login Successful",
                    'redirect' => ''
                );
                $status = 200;
            } else {
                
                $msg = array(
                    'status' => 'error',
                    'message' => "Login Failed",
                    'redirect' => ''
                );
                $status = 401;
            }

            return response()->json($msg,$status);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MemberLogin  $memberLogin
     * @return \Illuminate\Http\Response
     */
    public function show(MemberLogin $memberLogin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MemberLogin  $memberLogin
     * @return \Illuminate\Http\Response
     */
    public function edit(MemberLogin $memberLogin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MemberLogin  $memberLogin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MemberLogin $memberLogin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MemberLogin  $memberLogin
     * @return \Illuminate\Http\Response
     */
    public function destroy(MemberLogin $memberLogin)
    {
        //
    }
}
