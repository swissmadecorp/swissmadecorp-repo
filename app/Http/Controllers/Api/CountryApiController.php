<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;

class CountryApiController extends Controller
{
    public function sendResponse($result, $message)
    {
    	$response = [
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }


    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountries()
    {   
            $countries = Country::all();
            return $this->sendResponse($countries, 'Retrieved successfully.');
    }

    public function getStates() {
        $states = State::where('country_id','231')->get();
        return $this->sendResponse($states, 'Retrieved successfully.');
    }

    public function getAStateFromCountry(Request $request)
    {   
            $id = $request['id'];
            $country = Country::where('sortname',$id)->first();
            $state = State::where('country_id',$country->id)->get();
            
            if (!$state)
                return '';

            return $this->sendResponse($state, 'Retrieved successfully.');
    }
}
