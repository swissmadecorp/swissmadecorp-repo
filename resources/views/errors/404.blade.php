@extends ("layouts.default-new")

@section("content")
    <div class="row">
        <div class="col-md-12" style="text-align:center">
            <div class="box-main-404 flex justify-center items-center py-20">
                <div class="outer-box w-[200px] h-[290px] shadow-[0px_0px_9px_#a09d9d] p-[10px] inline-block mr-[60px] -rotate-7">
                    <div class="inner-box w-full h-full bg-dark flex items-center justify-center">
                        <div class="text-[170px] text-white font-sans">4</div>
                    </div>
                </div>

                <div class="outer-box w-[200px] h-[290px] shadow-[0px_0px_9px_#a09d9d] p-[10px] inline-block mr-[60px] rotate-3">
                    <div class="inner-box w-full h-full bg-dark flex items-center justify-center">
                        <div class="text-[170px] text-white font-sans">0</div>
                    </div>
                </div>

                <div class="outer-box w-[200px] h-[290px] shadow-[0px_0px_9px_#a09d9d] p-[10px] inline-block mr-[60px] rotate-[14deg]">
                    <div class="inner-box w-full h-full bg-dark flex items-center justify-center">
                        <div class="text-[170px] text-white font-sans">4</div>
                    </div>
                </div>
            </div>

            <h2 style="font-size: 40px;font-weight: bold">We're sorry.</h2>
            <h3>The page you're looking for cannot be found.</h3>
            <p class="p-2">If you typed the URL directly, please make sure the spelling is correct. If you clicked on a link to get here, the link is outdated.
            </p>
            <p class="p-2">If you're not sure how you got here, <a href="javascript:history.back(-1)">go back</a> to the previous page or return to our <a href="/">homepage</a>.</p>

        </div>

    </div>
@endsection