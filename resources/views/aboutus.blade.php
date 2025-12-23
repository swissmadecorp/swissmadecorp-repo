@extends('layouts.default-new')

@section('title', 'About Us')

@section ('header')
<style>
    ul.about {list-style: disc;margin: 15px;padding: 0 0 0 15px;}
</style>

@endsection

@section ('content')

<div>
    <div class="flex justify-center">
        <div class="container p-5">
            <h1 class="text-3xl uppercase">About us</h1>
            <hr>
            <h5 class="pt-4">Located in the bustling heart of the renowned Diamond District on 47th Street in New York,
                we are a distinguished privately owned business dedicated exclusively to the world of luxury watches. 
                With an unwavering commitment to excellence, we curate a meticulously sourced collection of high-end, 
                authentic timepieces, each exemplifying the pinnacle of craftsmanship and precision. Our discerning 
                clientele trust us for our unwavering dedication to quality and authenticity, as we strive to offer not 
                just watches, but cherished heirlooms that stand the test of time.</h5>
            <ul class="about">
                <li class="pt-2 pb-2">For a quarter-century, we have been entrenched in the wholesale watch industry, steadfastly providing our clientele with exclusively authentic timepieces.</li> 
                <li class="pt-2 pb-2">We approach our business with utmost dedication, prioritizing customer satisfaction and fostering the expansion of our clientele. </li> 
                <li class="pt-2 pb-2">For over two decades, we have proudly held membership with the prestigious International Watch and Jewelers Guild (IWJG). </li> 
                <li class="pt-2 pb-2">We are also members of <b>Chrono24, eBay, IWJG, and BBB</b>. 
                    <ol class="text-yellow-500 dark:text-yellow-500 font-medium flex items-center">
                        <li class="mr-3"><a target="_blank" href="https://www.chrono24.com/dealer/212swissmade/index.htm"><img height="120" width="100" alt="Chrono24 Trusted Seller" src="/images/trusted-seller-icon.png"></a></li>
                        <li class="mr-3"><a target="_blank" href="https://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&amp;userid=swissmadecorp"><img height="100" width="70" alt="Chrono24 Trusted Seller" src="/images/ebay_logo.jpg"></a></li>
                        <li class="mr-3"><a target="_blank" href="http://www.iwjg.com/" style=""><img height="100" width="100" alt="Chrono24 Trusted Seller" src="/images/iwjg.jpg"></a></li>
                        <!-- <li><a target="_blank" href="https://www.bbb.org/us/ny/new-york/profile/watch-dealers/swiss-made-corp-0121-87143904/#sealclick"><img width="200" alt="BBB" src="https://seal-newyork.bbb.org/seals/blue-seal-200-42-bbb-87143904.png"></a></li> -->
                    </ol>
                </li> 
                <li>We stand behind the products we sell.</li> 
            </ul>

            <h4 class="text-2xl text-yellow-500 uppercase">Authenticity</h4>
            <p>In addition to our unwavering dedication to authenticity, there are several compelling reasons why choosing our timepieces is a decision you can make with confidence. Our selection is curated with meticulous care, encompassing a diverse range of styles and brands to cater to a variety of tastes and preferences. Whether you seek a classic elegance or a bold, contemporary statement piece, our collection is designed to meet your distinctive needs.</p>
            <p class="pt-2">Moreover, we place a premium on the quality and craftsmanship of our watches. Each timepiece undergoes rigorous inspection and testing to ensure it meets our exacting standards. Our commitment to excellence extends to our customer service as well. Our knowledgeable and friendly team is always on hand to assist you, providing expert guidance and answering any questions you may have about our products.</p>
            <p class="pt-2">Furthermore, we offer competitive pricing without compromising on the authenticity or quality of our watches. We understand the value of a sound investment, and we strive to provide timepieces that not only stand the test of time but also represent an exceptional value proposition.</p>
            <p class="pt-2">As a testament to our confidence in the durability of our products, we offer a comprehensive warranty on every watch in our collection. This serves as a guarantee of our commitment to your satisfaction and ensures that you can enjoy your purchase with peace of mind.</p>
            <p class="pt-2">In choosing our timepieces, you're not only acquiring a beautiful and authentic watch, but you're also becoming a part of a community that values genuine craftsmanship and exceptional service. We invite you to explore our collection and experience the difference for yourself. Thank you for considering us for your timekeeping needs.</p>

            <h4 class="text-2xl text-yellow-500 uppercase pt-2 pt-4">Pre-Owned Watches Warranty</h4>
            <p>Pre-owned watches have garnered a dedicated following for several compelling reasons. Firstly, they carry a historical and collectible value that brand new watches simply can't replicate. Many pre-owned timepieces boast a unique story, having been worn and cherished by previous owners. Some models may even be discontinued or part of a limited production run, making them highly sought-after by collectors.</p>
            <p class="pt-2">One of the most significant advantages of pre-owned watches lies in their affordability. Even watches that are only a few years old can be found at a fraction of their original cost. This allows individuals to own a high-quality timepiece without breaking the bank. Moreover, pre-owned watches often retain their value well over time, with certain models even appreciating in value. This makes them not only a stylish accessory but also a potential investment.</p>
            <p class="pt-2">The variety and selection available in the pre-owned market are staggering. Enthusiasts can explore a vast array of watches from different brands, styles, and eras. This diversity ensures that buyers can find models that may not be readily available in the current market. Additionally, reputable pre-owned dealers and platforms uphold stringent quality standards. They conduct thorough inspections, cleanings, and, in some cases, offer warranties for their pre-owned watches, providing buyers with assurance in their purchase.</p>
            <p class="pt-2">Furthermore, vintage watches exude a charm and aesthetic that is often unmatched by modern designs. They possess distinctive dial layouts, hands, and case shapes that reflect the style of their era. For those seeking a watch with character and personality, vintage timepieces offer a unique allure. In essence, pre-owned watches offer a blend of history, craftsmanship, and value that make them an attractive choice for discerning buyers.</p>

            <h4 class="text-2xl text-yellow-500 uppercase pt-2 pt-4">Shipping</h4>
            <p>At our esteemed establishment, we take great pride in offering a seamless and secure shipping experience for all our exquisite timepieces. To ensure utmost peace of mind, we extend the privilege of Insured Overnight shipping to all destinations within the United States, guaranteeing the safe arrival of your cherished timepiece. This service is provided at a fixed and reasonable price, designed to accommodate our valued clientele.</p>
            <p class="pt-2">For those who prefer a more personal touch, we are delighted to offer the option of in-person pick-ups. However, we kindly request that appointments be scheduled in advance, allowing us to make the necessary arrangements to welcome you to our store with the utmost attention and care.</p>
            <p class="pt-2">This dedication to providing multiple avenues of service is a testament to our commitment to exceeding your expectations and ensuring that your experience with us is nothing short of exceptional. Rest assured, whether you choose the convenience of our insured overnight shipping or the warmth of an in-person visit, your satisfaction and the safety of your treasured timepiece remain our top priorities.</p>
            
            <h4 class="text-2xl text-yellow-500 uppercase pt-2 pt-4">Return Policy for Watches and Jewelry:</h4>
            <p class="font-bold pb-2">15-Day Return Policy</p>

            We offer a <span class="font-bold">15-day return policy</span> for watches and jewelry, provided they are returned in their original condition and packaging. This includes all original accessories such as stickers, tags, plastic wraps, and any accompanying documents or papers.

            Please note that the following items are not eligible for return:
            <ul>
                <li class="ml-10 p-2 list-disc">Customized pieces, including those with engravings.</li>
                <li class="ml-10 p-2 list-disc">Items that have been resized.</li>
                <li class="ml-10 p-2 list-disc">Products showing signs of wear, such as damage or scratches.</li>
            </ul>
            
            <p class="pt-2">Please understand that if an item is found to be incomplete or has been altered in any way, we regret to inform you that it <i>CANNOT</i> be accepted for return. We believe this policy ensures that every piece leaving our care is in the best possible condition for our valued customers.</p>
            <p class="pt-2">Furthermore, we kindly request that all shipping charges be handled by the customer. In addition, there may be a nominal restocking fee of 5%, which we hope you appreciate is necessary to cover the costs associated with processing and ensuring the quality of returned items. It's important to note that each watch will be subjected to a thorough inspection prior to the issuance of any refunds. We believe this meticulous process is a testament to our commitment to quality and customer satisfaction.</p>
            <p class="pt-2"><b>Special Note for NEW ROLEX Sales:</b></p>
            <p>In consideration of the unique circumstances surrounding <i>NEW ROLEX</i> sales, we would like to bring to your attention that such transactions are considered final. Regrettably, these particular items are not eligible for returns. We appreciate your understanding in this matter and thank you for your trust in us.</p>
            
        </div>
    </div>
</div>
@endsection

@section ('footer')
    <script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    <script src="/fancybox/jquery.fancybox.min.js"></script>
    <script src="/js/parsley.js"></script>
@endsection
