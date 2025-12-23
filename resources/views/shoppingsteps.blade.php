<div class="checkout_page p-2 pb-4">
    <div class="text-center">
        <div class="checkout_steps">
            <ul>
                <li>@if ($step==1)<a href="#">@endif<span>1</span>SHOPPING CART @if ($step==1)</a>@endif</li>
                <li>@if ($step==2)<a href="#">@endif<span>2</span>SHIPPING INFORMATION @if ($step==2)</a>@endif</li>
                <li>@if ($step==3)<a href="#">@endif<span>3</span>PAYMENT INFORMATION @if ($step==3)</a>@endif</li>
                <li>@if ($step==4)<a href="#">@endif<span>4</span>ORDER CONFIRMATION @if ($step==4)</a>@endif</li>
            </ul>
        </div>
    </div>
</div>
