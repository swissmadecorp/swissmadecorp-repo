<?php 

	// if ($_SERVER['SERVER_NAME'] == 'localhost')
	// 	$homepath = "";
	// else 
	$homepath = "https://www.swissmadecorp.com/images/ebaytemplate";
?>

<div class="htmlFrame">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<div class="mainBodyContainer">
		<style type="text/css">
			#production {
				width: 370px;
				/*border-collapse: separate;
				border-spacing: 4px;*/
			}
			
			.tabs input + label {border: 1px solid #ddd;}
			.tabs {font-family:Arial, Helvetica, sans-serif;}
			.tabs .tabcontent {
				display: none;
				padding: 20px;
    			border-top: 1px solid #ddd;
				border: 1px solid #ddd;
				height: 335px;
				font-size: 18px;
			}

			.tabcontent ul li {padding: 5px}
			.tabs input {
				display: none;
			}

			.tabs label {
				display: inline-block;
				margin: 0 0 -1px;
				padding: 15px 25px;
				font-weight: 600;
				text-align: center;
				color: #bbb;
				border: 1px solid transparent;
				transition: 0.3s;
			}

			.tabs label:hover {
				color: #f00;
				cursor: pointer;
			}

			.tabs input:checked + label {
				color: #000;
				border: 1px solid #c3c2c2;
				border-radius:5px 5px 0px 0px;
				border-bottom: 1px solid #fff;
			}

			.tabs #tab1:checked ~ #content1,
			.tabs #tab2:checked ~ #content2,
			.tabs #tab3:checked ~ #content3,
			.tabs #tab4:checked ~ #content4,
			.tabs #tab5:checked ~ #content5{display: block;transition: 0.3s;}

			.mainBodyContainer {
				width: 1200px;
								-webkit-box-shadow: 0px 0px 2px rgba(50, 50, 50, 0.75);
				-moz-box-shadow:    0px 0px 2px rgba(50, 50, 50, 0.75);
				box-shadow:         0px 0px 2px rgba(50, 50, 50, 0.75);
				background: url({{$homepath}}/abstract.jpg) no-repeat;
				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
				border-radius: 10px;		
				margin: 0 auto;		
			}
			
			#production td{
				width: 50%;
				padding: 8px 4px;
			}
			#production .head{
				background: #000;
				color: #fff;
				font-weight: bold;
				text-transform:uppercase;
				border-top-left-radius: 10px;
    			border-top-right-radius: 10px;
			}

			#production .contents {
				background: #fff;
			}

			.storeCategoriesHeader {
				background: #000;
				color: #fff;
				padding: 4px;
				-webkit-border-top-left-radius: 5px;
				-webkit-border-top-right-radius: 5px;
				-moz-border-top-left-radius-radius: 5px;
				-moz-border-top-right-radius-radius: 5px;
				border-top-left-radius: 5px;
    			border-top-right-radius: 5px;
			}
			.storeCategories {
				margin: 3px 5px;
				width: 200px;
				max-width: 200px;
				float: left;
				height: 535px;
				border: 1px solid #ccc;
				padding: 5px;
				-webkit-box-shadow: 0px 0px 2px rgba(50, 50, 50, 0.75);
				-moz-box-shadow:    0px 0px 2px rgba(50, 50, 50, 0.75);
				box-shadow:         0px 0px 2px rgba(50, 50, 50, 0.75);
				border-radius: 8px;
			}
			
			.storeCategories h3{
				padding: 0;
				margin: 5px 0 0;
			}
			
			.storeCategories ul{
				padding: 5px 0 5px;
				margin: 0 5px;
				list-style-type:none;
			}
			
			.storeCategories ul li a:hover{
				background: #000;
				outer-border: 1px solid #ccc;
				color: #fff
			}
			
			.storeCategories ul li a{
				text-decoration: none;
				padding: 5px;
				display: block;
				transition: background .5s ease-out;
				color: #000
			}
			
			.thumbcontainer {
				margin: 1px 0;
				min-height: 102px;
			}
			.thumbcontainer ul li img{
				-webkit-box-shadow: 0px 0px 2px rgba(50, 50, 50, 0.75);
				-moz-box-shadow:    0px 0px 2px rgba(50, 50, 50, 0.75);
				box-shadow:         0px 0px 2px rgba(50, 50, 50, 0.75);
				border-radius: 8px;
				width: 100px;
				height: 100px;
			}
			.thumbcontainer ul{
				list-style-type:none;
				margin:0;
				padding:0;
			}
			
			.thumbcontainer ul li{
				padding: 0 1px;
				margin: 0 6px;
				float: left;
				display: inline;
				cursor: pointer;
			}
			.mainImage {
				padding-top: 3px;
				width: 410px;
				/* height: 435px; */
				text-align: center;
				/* background: #fff; */
				float: left;
			}
			
			.mainImage div {
				padding: 15px 0;
				width: 430px;

			}
			.mainImage img{
				max-height: 530px;
				/* width: 324px; */
				/* -webkit-box-shadow: 0px 0px 2px rgba(50, 50, 50, 0.75);
				-moz-box-shadow:    0px 0px 2px rgba(50, 50, 50, 0.75);
				box-shadow:         0px 0px 2px rgba(50, 50, 50, 0.75); */
				max-width: 100%;
				border-radius: 8px;
			}
			#prodtitle {
				text-align: center;
				font-weight: bold;
				font-size: 25px;
				padding: 13px 1px;
				color: #fff;
				-webkit-border-bottom-right-radius: 10px;
				-webkit-border-bottom-left-radius: 10px;
				-moz-border-radius-bottomright: 10px;
				-moz-border-radius-bottomleft: 10px;
				border-bottom-right-radius: 10px;
				border-bottom-left-radius: 10px;
			}
			.menus,
			.menus ul,
			.menus li,
			.menus a {
				margin: 0;
				padding: 0;
				border: none;
				outline: none;
			}
			.menus img {
				border: 0;
			}
			.menus {
				height: 40px;
				width: 1000px;
			}

			#disclaimer-input {
				color: #afafaf;
				margin: 0 0 5px;
			}

			.menus ul li{
				
				position: relative;
				float:left;
				display: block;
				height: 40px;
				list-style: none;
			}
			
			.tab {
				padding: 15px; 
				font-size: 20px;
				border: 1px solid #ccc; 
				display: none;
				background: #eee;
				height: 350px;
			}
			
			.tab .tab-child {
				background: white; padding: 10px;
				height: 92%;
			}
			.tab div div:first-child{
				font-weight: bold;
				color: #277ec8;
				width: 100%;
				border-bottom: 1px solid #ccc;
			}
		</style>
		
		<div style="margin: 0 auto; width: 1000px">
			<img src="{{$homepath}}/logo.png" style="padding-bottom: 4px "/>
			<div style="background: url('{{$homepath}}/top-banner.png') no-repeat; height: 65px">
				
			</div>
			<div id="prodtitle" style="background: #277ec8"><!-- prodtitle --></div>
			<?php 
				if (! function_exists('searchCriteria')) {
					function searchCriteria ($title) {
						$link = "https://www.ebay.com/dsc/i.html?_saslop=1&_ex_kw=&_sasl=swissmadecorp&_nkw=".strtolower($title);
						return "<li><a href='$link'>$title</a></li>";
					} 
				}
			?>
			
			<div style="width: 1000px; height: 585px; overflow: hidden">	
				<div class="storeCategories">
					<div class="storeCategoriesHeader">Watch Brands</div>
						<ul>
						<?php echo searchCriteria ('Rolex') ?>
						<?php echo searchCriteria ('Breitling') ?>
						<?php echo searchCriteria ('Cartier') ?>
						<?php echo searchCriteria ('Chanel') ?>
						<?php echo searchCriteria ('Hublot') ?>
						<?php echo searchCriteria ('IWC') ?>
						<?php echo searchCriteria ('Omega') ?>
						<?php echo searchCriteria ('Panerai') ?>
						<?php echo searchCriteria ('Ulysse Nardin') ?>
						<?php echo "<li><a href='https://www.ebay.com/dsc/i.html?_saslop=1&_ex_kw=&_sasl=swissmadecorp'>All Watches</a></li>" ?>
						</ul>
				</div>
				<div class="mainImage">
					<img src="%mainImage%" />
					
					<div class="thumbcontainer">
						<ul>

						</ul>
					</div>
				</div>
				
				<div style="width: 30%; float: left">
					<table id="production" cellpadding="2">
						<!-- production -->
					</table>
				</div>
			</div>
			
			<div id="descriptionContainer"><!-- Description goes here -->
				<div id="description">
					<!-- description -->
					<!-- Description goes here -->
				</div>
			</div>

			<br style="clear: both">
			<div id="disclaimer-input">
				* SwissMade Corp. is neither affiliated with nor a factory authorized 
				dealer or repair center for the above mentioned brand, watch maker or 
				any other watch brand. SwissMade Corp. warranties all items sold for one year for pre-owned watches
				and three years for some new watches from the date of the original sale. Warranty only covers manufacturer's parts which
				existed on the watch at the time of the sale and shall be void if altered subsequent to the sale. 
			</div>
			<br style="clear: both">
			
			<div class="tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1">Payment</label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2">Return Policy</label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3">Shipping &amp; Handling</label>
				<input id="tab4" type="radio" name="tabs">
				<label for="tab4">About Us</label>
				<input id="tab5" type="radio" name="tabs">
				<label for="tab5">Contact</label>

				<div id="content1" class="tabcontent ">
					<div class="w3-container w3-animate-opacity">
						<ul>
							<li>We accept all major Credit Cards</li>
							<li>US Residents are required to pay Sales Tax regardless of their location.</li>
							<li>All International shipments are subject to customs and duties.</li>
						</ul>
						<img src="{{$homepath}}/payment_cards.png" />
					</div>
				</div>
				
				<div id="content2" class="tabcontent">
					<div class="w3-container w3-animate-opacity">
						<br>
						Swiss Made Corp. should meet or exceed your expectations. However, 
						if you are not completely satisfied or happy with your purchase, 
						we guarantee to either exchange your purchase or provide you with a 
						full refund (excluding shipping fee if any) within a 14 days of purchase. 
						However, you will still be responsible for return shipping fee. 
						We inspect all items before issuing credit. 
						<br><br>Item which has been worn, damaged and/or altered in any way, will not be refunded nor 
						exchanged. Restocking fee may apply to those items which don't meet 
						our return policy.
					</div>
				</div>
				
				<div id="content3" class="tabcontent">
					<div class="w3-container w3-animate-opacity">
						<ul>
							<li>Shipping and Handling for this listing within the USA is $79 flat for overnight and $125 for International Priority.</li>
							<li>We use FedEx to ship all our murchandise with full insurance and signature confirmation. </li>
							<li>We ship world wide. Additional charges may apply for the international mail.</li>
						</ul>
						<p>Buyers - Please Note:</p>
					
						Import duties, taxes and charges are not included in the item price or shipping charges. These charges are the buyer's responsibility.<br>
						Please check with your country's customs to determine what the<br>additional costs will be prior to bidding or buying.<br>
						
						<!-- <div style="position: relative; top: -65px; background: url('{{$homepath}}/freeshipping.png') no-repeat right; display:block; height: 150px"></div> -->
					</div>
				</div>

				<div id="content4" class="tabcontent">
					<div class="w3-container w3-animate-opacity">
						<ul>
							<li>Our goal is simple - customer satisfaction! We offer 14 day money back guaranteed. 
								Our prices are low compared to most competitors. So why not give us a 
								try and help us build the name and the new customer relationship. 
								We will exceed your expectations.</li>
							<li>We carry Brand New and Pre-Owned luxury watches.</li>
							<li>All our watches are tested and are Guaranteed to work upon arrival
								which should give you the ultimate peace of mind and confidence to purchasing from Swiss Made 
								Corp.</li>
						</ul>
					</div>
				</div>

				<div id="content5" class="tabcontent">
					<div class="w3-container w3-animate-opacity">
						<img src="{{$homepath}}/logo.png" style="padding-bottom: 4px "/>
						<ul>
							<li>Please feel free to email us with any questions.</li>
							<li>Email us through eBay and we will assist you.</li>
						</ul>
					</div>		
				</div>
			</div>

			<br>
			<br>
			
		</div>
	</div>
</div>