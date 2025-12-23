<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;

class CountriesController extends Controller
{
    public function getStateFromCountry(Request $request) {
        $id = $request['id'];
        $content = "";
        
        if (is_numeric($id))
            $country =Country::with('states')->where('id',$id)->get();
        else $country =Country::with('states')->where('sortname',$id)->get();
        
        //Log::debug($id);
        $content .= '<option value="0"></option>';
        foreach ($country->first()->states as $state) {
            $content .= '<option value="'. $state->id . '">'. $state->name .'</option>';
        }
        
        //dd($country->first()->states->toArray());
        return \Response::json($content,200);
    }
}
