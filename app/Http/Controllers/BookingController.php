<?php

namespace App\Http\Controllers;

use App\Models\Booking;
// use App\Http\Requests\StoreBookingRequest;
use Illuminate\Http\Request;
use App\Mail\GMailer; 
use App\Models\Product;
use Carbon\Carbon;

class BookingController extends Controller
{
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
     * @param  \App\Http\Requests\StoreBookingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            parse_str($request['contact'],$booking);

            if (!isset($booking['book_date'])) return "There was an error scheduling your appointment.";
            $bookStart = Carbon::parse($booking['book_date'].' '.date("H:i", strtotime($booking['book_time'])), 'UTC');

            $validator = \Validator::make($booking, [
                'contactname' => "required",
                'phone' => "required",
                'email' => "required",
            ]);

            if ($validator->fails()) {
                return back()
                    ->withInputs($booking->all())
                    ->withErrors($validator);
            }

            $bookStart = Carbon::parse($booking['book_date'].' '.$booking['book_time'], 'UTC');

            $product = Product::find($request['product_id']);
            Booking::create([
                'contact_name' => $booking['contactname'],
                'phone' => $booking['phone'],
                'email' => $booking['email'],
                'book_date' => $bookStart,
                'product_id' => $product->id
            ]);


            $data = array(
                'template' => 'emails.booking-1',
                'to' =>'info@swissmadecorp.com',
                'subject' => "Scheduled for " . date("m-d-Y", strtotime($booking['book_date'])).', '.$booking['book_time'] . ' with Swiss Made Corp.',
                'contactname' => $booking['contactname'],
                'book_date' => date("l jS \of F Y", strtotime($booking['book_date'])),
                'book_time' => $booking['book_time'],
                'phone' => $booking['phone'],
                'email' => $booking['email'],
                'wristwatch' => $product->title,
                'product_id'=>$product->id
            );

            $gmail = new GMailer($data);
            $gmail->send();

            $data = array(
                'template' => 'emails.booking',
                'to' =>$booking['email'],
                'subject' => "Scheduled for " . date("m-d-Y", strtotime($booking['book_date'])).', '.$booking['book_time'] . ' with Swiss Made Corp.',
                'contactname' => $booking['contactname'],
                'book_date' => date("l jS \of F Y", strtotime($booking['book_date'])),
                'book_time' => $booking['book_time'],
                'wristwatch' => $product->title,
                'product_id'=>$product->id
            );

            $gmail = new GMailer($data);
            $gmail->send();

            return "Your appointment has been scheduled. You will receive a confirmation email shortly.";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Request $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
    //  *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
    //  */
    public function update(Request $booking, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $booking)
    {
        //
    }
}
