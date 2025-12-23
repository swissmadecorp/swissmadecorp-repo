<div> 
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt --> 

    <div wire:ignore.self id="slideover-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div onclick="toggleSlideover()" id="slideover-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div id="slideover" class="absolute w-1/2 duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full">
            <div onclick="toggleSlideover()" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-200 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="p-4 relative mt-10">
                
                @if (isset($order))
                @inject('countries','App\Libs\Countries')
                <div class="customer_address mb-3">
                <?php 
                        $address2 = '';
                        
                        if (in_array($order->b_company,['Website','eBay','Chrono24'])) {
                            $state_s = $countries->getStateCodeFromCountry($order->s_state);
                            $country = $countries->getCountry($order->s_country);

                            echo $order->s_company.'<br>';
                            echo !empty($order->s_address1) ? $order->s_address1 .'<br>' : '';
                            echo !empty($order->s_address2) ? $order->s_address2 .'<br>' : '';
                            echo !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': '';
                            
                            echo !empty($order->b_phone) ? $order->b_phone . '<br>' : '';
                            echo !empty($order->po) ? 'PO #: '.$order->po . '<br>' : '';
                        } else {
                        $state_b = $countries->getStateCodeFromCountry($order->b_state);
                        $country = $countries->getCountry($order->b_country);

                        echo $order->b_company.'<br>';
                        echo !empty($order->b_address1) ? $order->b_address1 .'<br>' : '';
                        echo !empty($order->b_address2) ? $order->b_address2 .'<br>' : '';
                        echo !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.'<br>': '';
                        
                        echo !empty($order->b_phone) ? $order->b_phone . '<br>' : '';
                        echo !empty($order->po) ? 'PO #: '.$order->po . '<br>' : '';
                        }
                            //die($order->b_company);
                    ?>
                </div>
                
                <table x-ref="table" class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-3 py-3">Amount</th>
                            <th scope="col" class="px-3 py-3">Ref #</th>
                            <th scope="col" class="px-3 py-3">Payment</th>
                            <th scope="col" class="px-3 py-3">Date</th>
                            <th scope="col" class="px-3 py-3">Action</th>
                        </tr>
                    </head>
                    <tbody>
                        
                        <?php $totalLeft = $order->total ?>
                        <?php $calc = $order->total ?>
                        
                        @if (count($order->payments))
                        @foreach ($order->payments as $payment)
                        <?php $totalLeft = $totalLeft - $payment->amount ?>
                        <tr class="odd:bg-white hover:bg-gray-50 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                            <td>${{ number_format($calc,2) }}</td> 
                            <td>{{ $payment->ref }}</td>
                            <td>${{ number_format($payment->amount,2) }}</td>
                            <td class="px-3 py-2 text-center">{{ $payment->created_at->format('m/d/Y') }}</td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="rounded-full hover:bg-red-600 bg-red-500 p-2 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php $calc = $calc - $payment->amount ?>
                        @endforeach
                        @if ($totalLeft > 0)
                        <tr>
                        <td></td>
                        <td>
                            <input type="hidden" name="totalLeft" value="{{$totalLeft}}">
                            </td>
                        <td>
                            ${{ number_format($totalLeft,2) }}
                            <input type="hidden" value="{{$totalLeft}}" name="fullamount" class="fullamount">
                        </td>
                        <td colspan='2'>
                            <input type="text" style="width: 140px" wire:model="paymentAmount" class="payment" placeholder="$ Amount" required>
                            @error('paymentAmount')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                            <input type="text" style="width: 120px" wire:model="paymentRef" class="payment_option" placeholder="Reference" required>
                            @error('paymentRef')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                            <button tooltip="Full amount" style="padding: 3px 5px" class="btn btn-primary copyamount" aria-label="Left Align">
                                <i class="far fa-copy" tooltip="Copy full amount"  aria-hidden="true"></i>
                            </button>
                            <button style="padding: 3px 5px" class="btn btn-primary additem" wire:click="savePayment" aria-label="Left Align">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                            </button>
                        </td>
                        
                    </tr>
                    @else
                    <td colspan="6" style="text-align: center; color: green">Order has been paid fully</td>
                    @endif
                    @else 
                @if ($order->status == 0)
                <tr>
                    <td>
                        ${{ number_format($order->total,2) }}
                        <input type="hidden" value="{{$order->total}}" name="fullamount" class="fullamount">
                    </td>
                    <td>
                        <input type="text" style="width: 140px" wire:model="paymentAmount" class="payment" placeholder="$ Amount" required>
                        @error('paymentAmount')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                        <input type="text" style="width: 120px" wire:model="paymentRef" placeholder="Reference" required>
                        @error('paymentRef')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                        <button tooltip="Full amount" style="padding: 3px 5px" class="btn btn-primary copyamount" aria-label="Left Align">
                            <i class="far fa-copy" tooltip="Copy full amount"  aria-hidden="true"></i>
                        </button>
                        <button style="padding: 3px 5px" class="btn btn-primary" wire:click="savePayment" aria-label="Left Align">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @else
                    <td colspan="5" style="text-align: center; color: green">Order has been paid fully</td>
                @endif
                @endif
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
    
</div>

<script> 
  
    function toggleSlideover(id) {
        if (document.getElementById('slideover-container').classList.contains('invisible')) {
            setTimeout(() => {
                document.getElementById('slideover-container').classList.toggle('invisible')
                document.getElementById('slideover-bg').classList.toggle('opacity-0')
                document.getElementById('slideover-bg').classList.toggle('opacity-50')
                document.getElementById('slideover').classList.toggle('translate-x-full')
            }, "400")
        } else {
            document.getElementById('slideover-container').classList.toggle('invisible')
            document.getElementById('slideover-bg').classList.toggle('opacity-0')
            document.getElementById('slideover-bg').classList.toggle('opacity-50')
            document.getElementById('slideover').classList.toggle('translate-x-full')
        }
    }
    
</script>
