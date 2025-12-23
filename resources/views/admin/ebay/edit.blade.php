@extends('layouts.admin-default')

@section ('header')
<link href="/css/ebaystyle.css" rel="stylesheet">
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section ('content')


	<div style="float: right"><a href="" id="settings" class="btn btn-success">Settings</a></div>
	<div style="clear: right" class="tabcontainer">
		<div class="tab">Select your eBay primary category</div>
		<div class="tabs">
		
			Primary Category:<br>
			<div id="categories">
				<select multiple="multiple" size="8" class="primary_category">
					<option selected value="281" >Jewelry &amp; Watches</option>
				</select>

				@if (isset($categories))
					{!! $categories !!}
				@endif
			</div>
			
			<div class="store_category_container">
			@if ($settings['has_store'])
				<br>
				Store Category:<br>
				<div id="store_categories" style="margin-left: 6px">
					<select size="8">
						
					</select><br>
					<button id="create_store_category" class="btn btn-sm btn-success">Create Category</button>
					<button id="update_store_categories" class="btn btn-sm btn-success">Refresh Categories</button>
				</div>
			@endif
			</div>
		</div>
    </div>
	
	<div id='dropDown'></div>
    <div class="tabcontainer collapsed">
		<div class="tab">Listing Type and Duration</div>
		<div class="tabs">
			Listing Type: 
			<input type="radio" id="Chinese" style="margin: 1px 4px;vertical-align: middle" name="ListingType" value="Chinese">
			<label for="Chinese">Auction</label>
			<input type="radio" checked style="margin: 1px 4px;vertical-align: middle" id="FixedPriceItem" name="ListingType" value="FixedPriceItem">
			<label for="FixedPriceItem">Buy Now</label>
			<input type="checkbox" id="Offer" checked style="margin: 1px 4px;vertical-align: middle" name="Offer" value="Offer">
			<label for="offer">Best Offer</label>
			<br>
			<br>
			<table style="border-collapse: separate; border-spacing: 3px; width: 450px">
				<tr>
					<td>Duration:</td>
					<td>
					<select id="duration">
						<option value="Days_3">3 Days</option>
						<option value="Days_5">5 Days</option>
						<option value="Days_7">7 Days</option>
						<option value="Days_10">10 Days</option>
						<option value="Days_30">30 Days</option>
						<option selected value="GTC">Good 'Til Cancelled</option>
					</select>
					</td>
				<tr>
					<td>Condition:</td>
					<td>
						<select id="conditions">
						</select>
					</td>
				</tr>
				<tr style="display: none">
					<td>Condition Description:</td>
					<td><textarea class="form-control" type="text" id="conditionDescription" style="width: 250px"></textarea></td>
				</tr>
				<tr>
					<td>Schedule Listing:</td>
					<td><input class="form-control" type="text" id="schedule" style="width: 250px"/></td>
				</tr>
			</table>
			
			<div class="localThumbcontainer" style="display: none">
				<ul>
					
				</ul>
			</div>
		</div>
    </div>
    
    <div class="tabcontainer">
		<div class="tab">Item Specifics</div>
		<div class="tabs">
			Use this to help you load most common item specifics that you can use when you list items in certain categories. Item Specifics are name-value pairs that describe typical characteristics of items in a particular category. To load item specifics for a particular category, press Load button.<br>
			<div id="specificsContainer">
				<input style="float: left;margin: 0 4px" type="button" id="itemSpecifics" class="btn btn-success" value="Load from Ebay " />
				<input style="float: left;" type="button" id="itemSaveSpecifics" class="btn btn-success" value="Save to database" />
				<input style="float: left;margin: 0 4px" type="button" id="itemLoadTemplate" class="btn btn-success" value="Load template" />

				<!-- <input style="float: left" type="button" id="itemSpecificsFromURL" class="btn btn-success" value="Specifics from URL" /> -->
			</div><br>
			<div id="specifics"></div>
		</div>
    </div>
	
	<div class="tabcontainer">
		<div class="tab">eBay Description</div>
		<div class="tabs">

			<div class="tamplateContainer">
				<table class="tableTemplates">
					<tr>
						<th colspan="2">Template:</th>
					</tr>
					<tr>
						<td>
							<select class="form-control" id="bs_templates">
								<?php
									$templates = glob(base_path().'/resources/views/admin/ebay/templates/*.txt');
									if ($templates) {
										$i = 0;
										echo '<option></option>';
										foreach ($templates as $filename) {
											$file = pathinfo ($filename);
											echo '<option>'.$file['filename'].'</option>';
											$i++;
										}
									}
								?>
							</select>
						</td>
						<td><input class="btn btn-outline-primary" type="button" value="Load Template" id="load_template" ></td>
						<td><input class="btn btn-outline-success" type="button" value="Save Template" id="save_template" ></td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</div>
			
			
			<div class="col-md-12">
				<div class="form-group row">
					<label for="title" class="col-2 col-form-label">Title</label>
					<input class="form-control" type="text" maxlength="80" id="title" value="{{ $item->Title }}" />
				</div>

				
				<div class="form-group row">
					<label for="description-input" class="col-2 col-form-label">Description</label>
					<textarea class="form-control" type="text" style="height: 120px;" id="description">{{ $description }}</textarea>
				</div>
			</div>
		

			<br><br>
			<div id="template">
				<form id="the-form">
				<div id="elementContainer">
				<table>
					
				</table>
				</div>
				</form>
				<br style="clear: both">
			</div>
			
		</div>
	</div>
	
	<div class="tabcontainer">
		<div class="tab">Live Preview</div>
		
		<div class="tabs">
			
			<input type="button" class="btn btn-primary" id="generateHtml" value="Copy Source" /><br><br>
			<!-- Pruduction Preview and Post -->
			<iframe src="/admin/ebay/templates/template" id="prodHtml">{{ $item->Description }} </iframe>
				
			
		</div>
	
	</div>
	
	<button style="float: right" class="btn btn-primary" name="submit" id="submit">Submit to eBay</button>

	<div id="msgInfo" style="display: none"></div>

	<div id="inputBox" style="display: none" title="Template name.">
		Enter a desired template name and click save. If left unchanged, it will update instead.<br>
		<input class="form-control" type="text" name="template_name" id="template_name" style="width: 100%" />
	</div>	

	<div id="settingsDialog" style="display: none" title="Settings">
		<form id="formSettings">
		<table style="border-collapse: separate; border-spacing: 4px; width: 100%">
			<tr>
				<td style="width: 190px">3nd Day Shipping Fee</td>
				<td><input class="form-control" type="text" name="shipping" /></td>
			</tr>	
			<tr>
				<td>2rd Day Shipping Fee</td>
				<td><input class="form-control" type="text" name="shipping1" /></td>
			</tr>
			<tr>
				<td>Next Day Air Fee</td>
				<td><input class="form-control" class="form-control" type="text" name="shipping2" /></td>
			</tr>	
			<tr>
				<td>Return Details</td>
				<td><textarea class="form-control" name="return_details" ></textarea></td>
			</tr>
			<tr>
				<td>Return Days</td>
				<td><input class="form-control" class="form-control" type="text" name="return_days" /></td>
			</tr>
			<tr>
				<td>Restocking fee</td>
				<td>
					<select class="form-control" id="restocking" name="restocking_fee" style="width: 250px">
						<option value="none">None</option>
						<option value="Percent_10">10%</option>
						<option value="Percent_15">15%</option>
						<option value="Percent_20">20%</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Handling Time</td>
				<td><input class="form-control" type="text" name="handle_time" /></td>
			</tr>
			<tr>
				<td>PayPal Email</td>
				<td><input class="form-control" type="text" name="paypal_email" /></td>
			</tr>
			<tr>
				<td>Sales Tax</td>
				<td><input class="form-control" type="text" name="sales_tax" /></td>
			</tr>
			<tr>
				<td>State Sales Tax</td>
				<td><input class="form-control" type="text" name="state_sales_tax" /></td>
			</tr>			
			<tr>
				<td>Templates</td>
				<td>
					<select class="form-control" id="settTemplates" style="width: 270px;display: inline"></select>				
					<input type="button" class="btn btn-danger" id="deleteTemplate" name="deleteTemplate" value="Delete" />
				</td>
			</tr>
			<tr>
				<td>Do you have a Store?</td>
				<td><input type="checkbox" name="has_store" style="margin: -1px 1px">
			</td>
			</tr>
			<tr>
				<td colspan="2">Sign in to request an Autherization from EBay to list the products.</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="button" value="Launch Auth & Auth" id="getToken" class="btn btn-success"> 
					<input type="button" value="Fetch Token" id="fetchToken" class="btn btn-success">
					<input type="button" value="Clear Token" id="clearToken" class="btn btn-danger"> 
				</td>
			</tr>		
		</table>
		</form>
	</div>
@endsection

@section ("footer")
<script src="/js/ebaytemplate/jquery.iframe-transport.js"></script>
<script src="/js/ebaytemplate/jquery.cookie.js"></script>
<script src="/js/ebaytemplate/template.js"></script>
<script src="/js/ebaytemplate/ZeroClipboard.js"></script>
<script src="/js/ebaytemplate/jquery-ui-timepicker-addon.js"></script>
@endsection

@section ('jquery')
<script>
	var has_store = "{{$settings['has_store']}}";
	var csrf_token = "{{csrf_token()}}";
	var product_id = "{{$product->id}}";

	var itemLoadTemplate = "{{action('EbayController@itemLoadTemplate')}}";
	var getSpecificForCategory = "{{action('EbayController@getSpecificForCategory')}}";
	var linkSpecifics = "{{action('EbayController@linkSpecifics')}}";
	var deleteImage = "{{action('EbayController@deleteImage')}}";
	var saveSpecificField = "{{action('EbayController@saveSpecificField')}}";
	var loadImages="{{action('EbayController@loadImages')}}";
	var listItems = "{{action('EbayController@loadItems')}}";
	var loadTemplate = "{{action('EbayController@loadTemplate')}}";
	var saveTemplate = "{{action('EbayController@saveTemplate')}}";
	var addItem = "{{action('EbayController@addItem')}}";
	var GetItemSpecifics = "{{action('EbayController@GetItemSpecifics')}}";
	var GetItemSpecificsURL = "{{action('EbayController@getSpecificsFromURL')}}";
	var deleteTemplate = "{{action('EbayController@deleteTemplate')}}";
	var getSettings = "{{action('EbayController@getSettings')}}";
	var saveSettings = "{{action('EbayController@saveSettings')}}";
	var getCategoryNode = "{{action('EbayController@getCategoryNode')}}";
	var setStoreCategories = "{{action('EbayController@SetStoreCategories')}}";
	var storeCategories = "{{action('EbayController@getStoreCategories')}}";
	var loadItemSpecifics = "{{action('EbayController@loadItemSpecificsFromDB')}}";
	var get_eBayToken = "{{action('EbayController@get_eBayToken')}}";
	var fetchToken = "{{action('EbayController@fetchToken')}}";
	var loadOriginalTemplate = "{{action('EbayController@loadOriginalTemplate')}}";
	var itemId = "{{ $item->ItemID }}"
	var FLAG_PRODUCTION = "{{$settings['flag_production']}}";
	$(document).ready( function() {

	})

</script>

@endsection