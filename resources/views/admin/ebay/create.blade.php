@extends('layouts.admin-default')

@section ('header')
<link href="/css/ebaystyle.css" rel="stylesheet">
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section ('content')
	@if ($isListed)
	<h2 style="color: red">This item has already been listed on eBay!</h2>
	<h4>You must end it first and then try again.</h4>
	
	@else

	<div style="float: right"><a href="" id="settings" class="btn btn-primary">Settings</a></div>
	<div style="clear: right" class="tabcontainer">
		<div class="tab">Select your eBay primary category</div>
		<div class="tabs">
		
			Primary Category:<br>
			<div id="categories">
				<select multiple="multiple" size="8" class="primary_category">
					<option value="281" >Jewelry &amp; Watches</option>
				</select>
			</div>
			
			<div class="store_category_container">
			@if ($settings['has_store'])
				<br>
				Store Category:<br>
				<div id="store_categories" style="margin-left: 6px">
					<select size="8">
						
					</select><br>
					<button id="create_store_category" class="btn btn-sm btn-primary">Create Category</button>
					<button id="update_store_categories" class="btn btn-sm btn-primary">Refresh Categories</button>
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
				<input style="float: left;margin: 0 4px" type="button" id="itemSpecifics" class="btn btn-primary" value="Load from Ebay " />
				<input style="float: left;" type="button" id="itemSaveSpecifics" class="btn btn-primary" value="Save to database" />
				<input style="float: left;margin: 0 4px" type="button" id="itemLoadTemplate" class="btn btn-primary" value="Load template" />

				<!-- <input style="float: left" type="button" id="itemSpecificsFromURL" class="btn btn-primary" value="Specifics from URL" /> -->
			</div><br>
			<div id="specifics"></div>
			<div id='ddparent'></div>
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
					@if (e($product->group_id == 1))
						<?php 
							$material = MetalMaterial()->get($product->p_material); 
							$type = $product->jewelry_type;
						 ?>
					@else
						<?php $type=""; ?>
					@endif
					<input class="form-control" type="text" maxlength="80" id="title" value="{{ Conditions()->get($product->p_condition) . ' ' . $product->title  }} {{ $type }}" />
				</div>

				
				<div class="form-group row">
					<label for="description-input" class="col-2 col-form-label">Description</label>
					<?php 
							$box = $product->p_box;
							$papers = $product->p_papers;

              if ($box && $papers)
									$box = 'comes with box and papers';
							elseif (!$box && !$papers)
									$box = 'does not come with box and papers';
							elseif ($box && !$papers)
									$box = 'comes with box and no papers';
							elseif (!$box && $papers)
									$box = 'does not come with box but comes with papers';

						$warranty = ($product->p_condition == 1 || $product->p_condition == 1) ? ' three years' : ' one year';

					?>
					<textarea class="form-control" type="text" style="height: 120px;" id="description">{{ str_replace($product->id,'',$product->keyword_build) . " The watch $box. Swiss Made Corp. gives $warranty warranty on all mechanical issues for this watch. If you have any questions regarding this watch, please contact us via email." }}</textarea>
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
			<iframe src="/admin/ebay/templates/template" id="prodHtml"></iframe>
				
			
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
					<input type="button" value="Launch Auth & Auth" id="getToken" class="btn btn-primary"> 
					<input type="button" value="Fetch Token" id="fetchToken" class="btn btn-primary">
					<input type="button" value="Clear Token" id="clearToken" class="btn btn-danger"> 
				</td>
			</tr>		
		</table>
		</form>
	</div>
	@endif
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

	var itemLoadTemplate = "{{route('ebay.item.load.template')}}";
	var getSpecificForCategory = "{{route('ebay.specific.category')}}";
	var linkSpecifics = "{{route('ebay.link.specifics')}}";
	var deleteImage = "{{route('ebay.delete.image')}}";
	var saveSpecificField = "{{route('save.specific.field')}}";
	var loadImages="{{route('ebay.load.images')}}";
	var listItems = "{{route('ebay.load.items')}}";
	var loadTemplate = "{{route('ebay.load.template')}}";
	var saveTemplate = "{{route('ebay.save.template')}}";
	var addItem = "{{route('ebay.add.item')}}";
	var GetItemSpecifics = "{{route('ebay.item.specifics')}}";
	var GetItemSpecificsURL = "{{route('ebay.specifics.from.url')}}";
	var deleteTemplate = "{{route('ebay.delete.template')}}";
	var getSettings = "{{route('ebay.settings')}}";
	var saveSettings = "{{route('ebay.save.settings')}}";
	var getCategoryNode = "{{route('ebay.category.node')}}";
	var setStoreCategories = "{{route('ebay.set.store.categories')}}";
	var storeCategories = "{{route('ebay.get.store.categories')}}";
	var loadItemSpecifics = "{{route('ebay.item.specifics.from.database')}}";
	var get_eBayToken = "{{route('ebay.token')}}";
	var fetchToken = "{{route('ebay.fetch.token')}}";
	var loadOriginalTemplate = "{{route('load.original.template')}}";
	var itemId = "{{ $itemId }}"
	var FLAG_PRODUCTION = "{{$settings['flag_production']}}";
	$(document).ready( function() {

	})

</script>

@endsection