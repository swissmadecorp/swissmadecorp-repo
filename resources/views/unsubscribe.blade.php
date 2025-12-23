@extends('layouts.default-new')

@section('title', 'Unsubscribe')

@section ('content')
    <div class="flex justify-center">
        <div class="container">
            <h1 class="text-3xl uppercase">Unsubscribe</h1>
            <hr>
            <form method="POST" action="{{route('unsubscribe.success')}}" accept-charset="UTF-8" id="unsubscribeForm">
                @csrf
                <div class="text-lg bg-white py-6 mb-[8rem]">
                    @if ($email != 'notfound' )
                    <h4>Enter your email address to unsubscribe from our newsletter.</h4><br>
                    <p class="pb-2">You are currently subscribed to Swiss Made Corp list with the following email {{ e($email) }}</p>
                    <p class="pb-2">Please click unsubscribe button below if you wish to be removed from our newsletter</p>

                    <br>
                    <label for="email-input">Email Address</label><br>
                    <input type="text" class="w-full" name="email" value="{{ e($email) }}">
                    
                    <br>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-red-800 transition-colors duration-200 ease-in-out hover:bg-red-600 leading-5 p-2 mt-6 rounded-md text-white transition group-hover:bg-red-500">Unsubscribe</button>
                    </div>
                    @else
                        <h5>Email specified could not be found in the system. Please check if it was typed correctly and try again.</h5>
                        <div class="col-12" style="margin-top:20px">
                            <a href="unsubscribe" class="btn btn-danger">Go Back</a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection