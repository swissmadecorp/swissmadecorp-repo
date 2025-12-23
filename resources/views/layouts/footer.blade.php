<div class="footer">
    <!-- Footer -->
    <div class="col-lg-12">
        <footer>
            <div class="row">
                <div class="col-lg-3 infom col-sm-4 col-xs-12">
                    <h5 class="tt_uppercase m_bottom_13">Information</h5>
                    <hr class="divider_bg m_bottom_25">
                    <ul>
                        <li class="m_bottom_14"><a href="{{ URL::to('/contact-us') }}">Contact Us</a></li>
                        <li class="m_bottom_14"><a href="{{ URL::to('/about-us') }}">About Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 infom col-sm-4 col-xs-12">
                    <h5 class="tt_uppercase m_bottom_13">Policy</h5>
                    <hr class="divider_bg m_bottom_25">
                    <ul>
                        <li class="m_bottom_14"><a href="/privacy-policy">Privacy policy</a></li>
                        <li class="m_bottom_14"><a href="/terms-conditions">Terms and Conditions</a></li>
                    </ul>
                </div>

                <div class="col-lg-5 infom col-sm-4 col-xs-12">
                    <h5 class="tt_uppercase m_bottom_13">Trusted Seller</h5>
                    <hr class="divider_bg ">
                    <a target="_blank" href="https://www.chrono24.com/dealer/212swissmade/index.htm" style="">
                        <img width="70" alt="Chrono24 Trusted Seller" src="/images/trusted-seller-icon.png"></a>
                    <a target="_blank" href="https://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&amp;userid=swissmadecorp" style="">
                        <img width="50" alt="Chrono24 Trusted Seller" src="/images/ebay_logo.png"></a>
                    <a target="_blank" href="http://www.iwjg.com/" style="">
                        <img height="100" width="100" alt="Chrono24 Trusted Seller" src="/images/iwjg.jpg"></a>
                        
                    <!-- <a target="_blank" href="https://www.bbb.org/us/ny/new-york/profile/watch-dealers/swiss-made-corp-0121-87143904/#sealclick" target="_blank" rel="nofollow">
                        <img src="https://seal-newyork.bbb.org/seals/blue-seal-250-52-whitetxt-bbb-87143904.png" style="border: 0;" alt="Swiss Made Corp. BBB Business Review" /></a> -->

                </div>
            </div>
            
            <hr style="margin-top: 10px">
            <div class="col-lg-12" style="text-align: center">
                <p>Copyright &copy; Swiss Made Corp. 2017 - {{ date('Y') }}</p>
            </div>

            <script>
                window.addEventListener('load', function() {
                    var set = setInterval(function() {
                    if (jQuery('.fancybox-slide.fancybox-slide--html.fancybox-slide--current.fancybox-slide--complete:contains("Your")').is(':visible')) {
                        gtag('event', 'conversion', {'send_to': 'AW-716379641/t7l5CJvo_LQBEPmrzNUC'});        
                        clearInterval(set);
                    }
                    }, 1000)
                })
                </script>

        </footer>
    </div>
</div>