<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleRecapchaController extends Controller
{

    public function verify() {
        return response()->json('hello');
    }
}