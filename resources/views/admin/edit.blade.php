@extends('layouts.admin-default')
@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/lightgallery/css/lightgallery.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link href="/multiselect/chosen.min.css" rel="stylesheet">

<!-- <link href="{{ asset('/bootstrap_tab/mdb.min.css') }}" rel="stylesheet"> -->
@endsection
@section ('content')
{{  Form::model($product, array('route' => array('products.update', $product->id), 'method' => 'PATCH', 'id' => 'productform')) }} 
    <input type="hidden" value="{{$product->title}}" name="title">
    <input type="hidden" value="{{$product->id}}" name="_id">
    <input type="hidden" value="0" name="group_id">
    <a href="{{ URL::to('admin/products/'.$product->id.'/print') }}" target="_blank" class="btn btn-primary print">Print Barcode</a>
    <button type="submit" class="btn btn-primary uploadPhoto">Update</button>
    <a style="float:right" href="{{ URL::to('admin/products/create') }}" class="btn btn-primary">Create New</a>
    <a class="float-right btn btn-primary mr-1" href="/admin/products/{{ $product->id }}/duplicate">Duplicate</a>
    <a class="float-right btn btn-primary mr-1" href="/admin/products">
        <i class="fas fa-arrow-circle-left"> Go Back</i>
    </a>
    
    @if ($product->p_return)
    <a style="float:right;margin-right: 4px" href="/admin/products/{{$product->id}}/printreturn" class="btn btn-primary">Print Return</a>
    @endif
    <hr>
    
    <div class="clearfix mb-4"></div>
    
    <p><b>Created Date: {{ $product->created_at->format('m/d/Y h:i:s a') }}</b></p>
    
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab" aria-controls="product" aria-selected="true">Product Info</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="related-tab" data-toggle="tab" href="#related" role="tab" aria-controls="related" aria-selected="false">Related Products</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" style="padding: 16px" id="product" role="tabpanel" aria-labelledby="product-tab">
        
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label for="category-name-input" class="col-3 col-form-label">Title</label>
                        <div class="col-12">
                            <input type="text" class="form-control" placeholder="Enter title for this product" value="{{ $product->title }}" name="title">
                        </div>
                    </div>
                </div>
            </div>

    <div class="row">
        <div class="col-md-5">
            <div class="form-group row">
                <label for="stock_id-input" class="col-3 col-form-label">Stock #</label>
                <div class="col-9">
                    <span class="form-control">{{$product->id }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Category Name</label>
                <div class="col-9">
                    <select class="form-control categories" name="p_category" id="category">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" <?php echo !empty($product->category_id) && $product->category_id==$category->id ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    @if ($product->categories)
                    <input type="hidden" name="category_selected" id="category_selected" value="{{ $product->categories->id }}"/>
                    @else
                    <input type="hidden" name="category_selected" id="category_selected" value=""/>
                    @endif
                </div>
            </div>
            
            <div class="form-group row">
                <label for="condition_input" class="col-3 col-form-label">Condition</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_condition" id="condition_input">
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty($product->p_condition) && $product->p_condition==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="model-name-input" class="col-3 col-form-label">Model Name</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_model) ? $product->p_model : '' ?>" type="text" placeholder="Enter new model name" name="p_model" id="model-name-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="case-size-input" class="col-3 col-form-label">Case Size</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_casesize) ? $product->p_casesize : '' ?>" type="text" placeholder="Enter new case size name" name="p_casesize" id="case_size-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_reference) ? $product->p_reference : '' ?>" type="text" placeholder="Enter reference name or number" name="p_reference" id="reference-input" >
                </div>
            </div>
            <div class="form-group row">
                
                <label for="serial-input" class="col-3 col-form-label">Serial *</label>
                <div class="col-9">
                    @if ($product->serial_code)
                        <div>
                            <input class="form-control" value="<?php echo !empty($product->p_serial) ? $product->p_serial : '' ?>" type="text" placeholder="Enter serial number" name="p_serial" id="serial-input" required>
                            <span style="position: absolute;top: 6px;right: 19px;color: #828080">{{ $product->serial_code }} </span>
                        </div>
                    @else 
                        <input class="form-control" value="<?php echo !empty($product->p_serial) ? $product->p_serial : '' ?>" type="text" placeholder="Enter serial number" name="p_serial" id="serial-input" required>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="color-input" class="col-3 col-form-label">Dial Color *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_color) ? $product->p_color : '' ?>" type="text" placeholder="Enter dial color" name="p_color" id="color-input" >
                </div>
            </div>  
            <div class="form-group row">
                <label for="gender-input" class="col-3 col-form-label">Gender</label>
                <div class="col-9">
                <select class="custom-select form-control" name="p_gender">
                    @foreach (Gender() as $key => $gender)
                        <option <?php echo !empty($product->p_gender) && $product->p_gender==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $gender }}</option>
                    @endforeach
                </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="strap-input" class="col-3 col-form-label">Strap/Band</label>
                <div class="col-9">
                <select class="custom-select form-control" name="p_strap">
                    @foreach (Strap() as $key => $strap)
                        <option <?php echo !empty($product->p_strap) && $product->p_strap==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $strap }}</option>
                    @endforeach
                </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="clasp-input" class="col-3 col-form-label">Clasp Type</label>
                <div class="col-9">
                <select class="custom-select form-control" name="p_clasp">
                    @foreach (Clasps() as $key => $clasp)
                        <option <?php echo !empty($product->p_clasp) && $product->p_clasp==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $clasp }}</option>
                    @endforeach
                </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="year-input" class="col-3 col-form-label">Production Year</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_year) ? $product->p_year : '' ?>" type="text" name="p_year" id="year-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="water_resistance-input" class="col-3 col-form-label">Water Resistance</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->water_resistance) ? $product->water_resistance : '' ?>" type="text" name="water_resistance" id="water_resistance-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="bezel-features-input" class="col-3 col-form-label">Bezel Features</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->bezel_features) ? $product->bezel_features : '' ?>" type="text" name="bezel_features" id="bezel_features-input" >
                </div>
            </div>
            <?php 
                $ranTimes = ceil(count($custom_columns)/2);
                for ($i=0; $i < count($custom_columns);$i++) {
                    $column = $custom_columns[$i];?>
                    <div class="form-group row">
                        <label for="{{$column}}-input" class="col-3 col-form-label">{{ucwords(str_replace(['-','c_'], ' ', $column))}}</label>
                        <div class="col-9">
                        <input  class="form-control" type="text" name="{{$column}}" id="{{$column}}-input" value="<?= !empty($product->$column) ? $product->$column : '' ?>" />
                        </div>
                    </div> 
                    <?php if ($ranTimes==$i+1) break; 
                } ?>
            <div class="row">
                <div class="col-md-6">
                    <label for="box-input" class="col-form-label">Box</label>
                    <input type="checkbox" name="p_box" <?php echo !empty($product->p_box) ? 'checked' : '' ?> id="box-input">
                </div>
                <div class="col-md-6">
                    <label for="papers-input" class="col-form-label">Papers</label>
                    <input type="checkbox" name="p_papers" <?php echo !empty($product->p_papers) ? 'checked' : '' ?> id="papers-input">
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="form-group row">
                <label for="material-name-input" class="col-3 col-form-label">Case&nbsp;Material&nbsp;*</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_material" id="material-name-input">
                        @foreach (Materials() as $key => $material)
                        <option <?php echo !empty($product->p_material) && $product->p_material==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="bezelmaterial-name-input" class="col-3 col-form-label">Bezel&nbsp;Material&nbsp;*</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_bezelmaterial" id="bezelmaterial-name-input" required>
                        @foreach (BezelMaterials() as $key => $bezelmaterial)
                        <option <?php echo !empty($product->p_bezelmaterial) && $product->p_bezelmaterial==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $bezelmaterial }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="price-input" class="col-3 col-form-label">Cost *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <div class="cost-container">
                        <input class="form-control" value="<?php echo !empty($product->p_price) ? $product->p_price : '' ?>" type="text" placeholder="Enter price" name="p_price" id="price-input" required >
                        <input type="hidden" value="<?php echo !empty($product->p_repair_cost) ? $product->p_repair_cost : '' ?>" name="p_repair_cost" class="p_repair_cost">
                        <span style="position: absolute;top: 6px;right: 58px;color: #828080;z-index:4;"><?php echo !empty($product->p_repair_cost) ? $product->p_repair_cost : '' ?></span>
                        <button class="repair" title="Add additional amount to the cost."><i class="fas fa-tools"></i></button>
                    </div>
                    
                </div>
            </div>
            <div class="form-group row">
                <label for="retail-input" class="col-3 col-form-label">Retail *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty($product->p_retail) ? $product->p_retail : '' ?>" type="text" placeholder="Enter retail price" name="p_retail" id="retail-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="newprice-input" class="col-3 col-form-label">Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="<?php echo !empty($product->p_newprice) ? $product->p_newprice : '' ?>" type="text" placeholder="Enter new price" name="p_newprice" id="newprice-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="webprice-input" class="col-3 col-form-label">Web Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <div class="wire-container">
                        <input class="form-control" value="<?php echo !empty($product->web_price) ? $product->web_price : '' ?>" type="text" disabled placeholder="Enter website price" name="web_price" id="webprice-input">
                        <input type="hidden" value="<?php echo !empty($product->wire_discount) ? $product->wire_discount : '' ?>" name="wire_discount" id="wire_discount">
                        <button class="wire-discount" title="Disable online wire discount">
                            @if ($product->wire_discount==1)
                                <i class="fas fa-landmark"></i>
                            @else
                                X
                            @endif
                        </button>
                    </div>
                    
                </div>
            </div>
            <div class="form-group row">
                <label for="price3P-input" class="col-3 col-form-label">3rd Party Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="<?php echo !empty($product->p_price3P) ? $product->p_price3P : '' ?>" type="text" placeholder="Enter 3rd party price" name="p_price3P" id="price3P-input">
                </div>
            </div> 
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">On Hand *</label>
                <div class="col-4">
                    <input class="form-control qty" value="<?php echo !empty($product->p_qty) ? $product->p_qty : 0 ?>" type="text" placeholder="Enter amount on hand" name="p_qty" id="quantity-input" required>
                </div>
                <label for="p_return-input" class="col-4 col-form-label">Return To Vendor</label>
                <div class="col">
                    <input class="form-control p_return" style="top: 11px;position: absolute;left: -20px;" <?php echo !empty($product->p_return) ? 'checked' : '' ?> type="checkbox" name="p_return" id="p_return-input">
                </div>
            </div>
            <div class="form-group row" style="position:relative">
                <label for="supplier-input" class="col-3 col-form-label">Supplier *</label>
                <div class="col-4">
                    <input class="form-control supplier" autocomplete="off" value="<?php echo !empty($product->supplier) ? $product->supplier : '' ?>" type="text" placeholder="Enter supplier" name="supplier" id="supplier-input" required>
                </div>
                <label for="invoice-input" class="col-2 col-form-label">Invoice#</label>
                <div class="col-3">
                    <input class="form-control invoice" autocomplete="off" value="<?php echo !empty($product->supplier_invoice) ? $product->supplier_invoice : '' ?>" type="text" placeholder="Enter Supplier Invoice #" name="supplier_invoice" id="invoice-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="slug-input" class="col-3 col-form-label">Slug</label>
                <div class="col-9">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty($product->slug) ? $product->slug : '' ?>" type="text" name="slug" id="slug-input">
                </div>
            </div>      
            <div class="form-group row">
                <label for="platform-input" class="col-3 col-form-label">Platform</label>
                <div class="col-9">
                    <select class="custom-select form-control" id="platform-input" name="platform" disabled>
                        @foreach (Platforms() as $key => $platform)
                        <option <?php echo !empty($product->platform) && $product->platform==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="status-input" class="col-3 col-form-label">Status</label>
                <div class="col-9">
                    <select class="custom-select form-control" id="status-input" name="p_status">
                        @foreach (Status() as $key => $status)
                        <option <?php echo !empty($product->p_status) && $product->p_status==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $status }}</option>
                        @endforeach
                    </select>
                    
                    @if ($product->p_status == 2) 
                        <span style="font-weight: bold">Reserved For:</span> <span style='color:red'>{{ $product->reserve_for . ' - $' . number_format($product->reserve_amount,2) }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row comments">
                <label for="comments-input" class="col-3 col-form-label">Comments</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" type="text" placeholder="Enter additional comments" name="p_comments" id="comments-input"><?php echo !empty($product->p_comments) ? $product->p_comments : '' ?></textarea>
                </div>
            </div>
            <div class="form-group row smalldescription">
                <label for="smalldescription-input" class="col-3 col-form-label">Small Description</label>
                <div class="col-9">
                <textarea rows="4" class="form-control" type="text" placeholder="Enter small description" name="p_smalldescription" id="smalldescription-input"><?php echo !empty($product->p_smalldescription) ? $product->p_smalldescription : '' ?></textarea>
                </div>
            </div>
            <div class="form-group row longdescription">
                <label for="longdescription-input" class="col-3 col-form-label">Long Description</label>
                <div class="col-9">
                <textarea rows="4" class="form-control" type="text" placeholder="Enter a long description" name="p_longdescription" id="smalldescription-input"><?php echo !empty($product->p_longdescription) ? $product->p_longdescription : '' ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="movement-name-input" class="col-3 col-form-label">Movement</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="movement" id="movement-name-input" required>
                        @foreach (Movement() as $key => $movement)
                        <option <?php echo !empty($product->movement) && $product->movement==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $movement }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="dial-style-name-input" class="col-3 col-form-label">Dial Style</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_dial_style" id="dial-style-name-input" required>
                        @foreach (DialStyle() as $key => $dialstyle)
                        <option <?php echo !empty($product->p_dial_style) && $product->p_dial_style==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $dialstyle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @for ($i=$ranTimes; $i < count($custom_columns);$i++)
                @php $column = $custom_columns[$i];  @endphp
                <div class="form-group row">
                    <label for="{{$column}}-input" class="col-3 col-form-label">{{ucwords(str_replace(['-','c_'], ' ', $column))}}</label>
                    <div class="col-9">
                    <input  class="form-control" type="text" name="{{$column}}" id="{{$column}}-input" value="<?= !empty($product->$column) ? $product->$column : '' ?>" />
                    </div>
                </div>

            @endfor
        </div>
    </div>
    <?php $i=0; ?>
    
    <div class="row multi-image-container">
        @if (@count($product->images))
        <div class="col-md-6 loadedimages">
            <label for="images-input" class="col-form-label">Images</label>
            <div class="image-container">
                <div class="add-image btn-sm btn btn-primary"><i class="fas fa-plus"></i></div>
                @foreach ($product->images as $image)
                    <div class="image" data-src="">
                        <div class="image-title">{{$image->id}}</div>
                        <div class="delete-image">X</div>
                        @if ($image->location)
                            
                            @if (strpos($image->location,'.com'))
                            <a href="" class="image-item" data-src="{{  $image->location }}">
                                <img alt="{{ $image->title }}" data-id="{{$image->id}}" data-pid="{{$product->id}}" src="{{  $image->location }}" title="{{ $image->title }}" >
                            </a>
                            @else
                            <a href="" class="image-item" data-src="{{ '/images/' . $image->location }}">
                                <img alt="{{ $image->title }}" data-id="{{$image->id}}" data-pid="{{$product->id}}" src="{{ '/images/thumbs/' . $image->location }}" title="{{ $image->title }}" >
                            </a>
                            @endif
                            
                        @endif
                        <div class="position"><input type="text" value="{{$image->position}}" placeholder="image position" name="position_{{$image->id}}" class="position-input" /></div>
                        
                    </div>
                <?php $i++ ?>
                @endforeach
            </div>
            </div>
            @endif
        @if (@count($product->images))
        <div class="col-md-6">
        @else
        <div class="col-md-12 image-holder">
        @endif
            <label for="images-input" class="col-form-label">Image Uploads</label>
            <div id="dropzoneFileUpload" class="dropzone" style="padding:62px 20px" multiple></div>
        </div>
    </div>
    <div class="form-group row snapshot mt-4" >
        <label for="captureimage-input" class="col-3 col-form-label">Capture Image</label>
        <div class="col-9">
            <div class="row">
                <div class="col-6">
                    <div style="width: 278px" id="captureimage"></div>
                </div>
                <div class="col-6">
                    <div id="results"></div>
                </div>
            </div>
            <input type=button value="Activate Snapshot" onClick="activate_snapshot()" class="activeSnapshot">
            <input type=button value="Take Snapshot" onClick="take_snapshot()" id="takesnapshot" >
        </div>
    </div>

    @if ($product->listings)
    <div class="form-group row">
        <div class="col-12">
            <label for="ebay-lising-input" class="col-form-label">eBay Listing Status</label>
            <span id="ebay-lising-input" style="height: 50px; overflow-y:auto;overflow-x:hidden" class="form-control"><a target="_blank" href="https://www.ebay.com/itm/{{ $product->listings->listitem }}">{{ $product->listings->listitem }}</a></span>
        </div>    
    </div>
    @endif

    @if (count($product->orders)>0 && $product->id != 1)
    <hr>
    <h4>Previous Orders</h4>
    <hr/>
    <div class="table-responsive">
    <table id="orders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Invoice Id</th>
                <th>Customer</th>
                <th>Invoice</th>
                <th>Date Sold</th>
                <th>Serial #</th>
                <th>Sold Amount</th>
            </tr>
        </thead>
        <tbody>      
        @foreach ($product->orders as $invoice)
        <?php 
            $returned='';$product=$invoice->products->find($product->id);
            
            if (count($invoice->returns) || $product->pivot->qty == 0)
                $returned = '-';
        ?>
        <tr style="<?= $returned=='-' ? "background: #ffecec" : "" ?>" >
            <td><a href="/admin/orders/{{ $product->pivot->order_id }}">{{ $product->pivot->order_id }}</a></td>
            <td>{{ $invoice->customers->first()->company }}</td>
            <td>{{ $invoice->method }}</td>
            <td>{{ $invoice->created_at->format('m/d/Y')}}</td>
            <td>{{ $product->pivot->serial }}</td>
            <td class="text-right"><?= $returned ?>${{ number_format($product->pivot->price,2) }}</td>   
        </tr>
        @endforeach
    </tbody>
    </table>
    </div>
    @endif

    </div>
    <div class="tab-pane fade" style="padding: 16px" id="related" role="tabpanel" aria-labelledby="related-tab">
        <select data-placeholder="Choose related products ..." class="related-select" name="related[]" multiple>
            
        </select>
    </div>

    </div>
    <hr>
    <a href="{{ URL::to('admin/products/'.$product->id.'/print') }}" target="_blank" class="btn btn-primary print">Print Barcode</a>
    <button type="submit" class="btn btn-primary uploadPhoto">Update</button>
    <button style="float:right" class="btn btn-primary newcolumn">New Column</button>
    <button style="float:right" href="{{ URL::to('admin/products/create') }}" class="btn btn-primary mr-1">Create New</button>
    
    @include('admin.errors')
    
{{  Form::close() }}  
<div id="search-customer"></div>

<div id="product-inquiry" style="width: 500px;display:none">
    <div class="popup-header">
        <h3 style="padding: 12px; text-align: left">On Hold For</h3>
    </div>

    <div class="container" style="padding: 20px 25px 0px;">
        <div class="row">
            <div class="pb-2">You may reserve this item for a specific customer.</div>
            <div class="col-md-12 form-panel">                
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="company-input" class=" col-form-label">Company Name</label>
                        <div class="">
                            <input class="form-control" autocomplete="off" value="<?php echo !empty($product->reserve_for) ? $product->reserve_for : '' ?>" type="text" name="p_company" id="company-input">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="amount-input" class="col-form-label">Reserve Amount</label>
                        <div class="">
                            <input class="form-control" autocomplete="off" value="<?php echo !empty($product->reserve_amount) ? $product->reserve_amount : '' ?>" type="text" name="p_amount" id="amount-input">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group float-right">
                            {{ Form::submit('Reserve', array('class' => 'btn btn-primary submit-onhold')) }}
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>
</div>

@endsection
@section ('footer')
<!-- <script src="/bootstrap_tab/mdb.min.js"></script> -->
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/fancybox/jquery.fancybox.min.js"></script>
<script src="/js/dropzone.js"></script>
<script src="/lightgallery/js/lightgallery-all.min.js"></script>
<script src="/lightgallery/js/lg-thumbnail.min.js"></script>
<script src="/js/webcam.min.js"></script>
<script src="/editable-select/jquery-editable-select.js"></script>
<script src="/multiselect/chosen.jquery.js"></script>

<!-- Configure a few settings and attach camera -->
<script language="JavaScript">
    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    //Webcam.attach( '#captureimage' );
</script>
<!-- Code to handle taking the snapshot and displaying it locally -->
<script language="JavaScript">
function activate_snapshot() {
    Webcam.attach( '#captureimage' );
    $('#takesnapshot').show();
    $('html, body').animate({scrollTop: $(document).height()}, 400);
}
function take_snapshot() {
    // take snapshot and get image data
    Webcam.snap( function(data_uri) {
        // display results in page
        var request = $.ajax({
            type: "POST",
            url: "{{route('capture.image')}}",
            data: { 
                _token: "{{csrf_token()}}",
                captured_image: data_uri,
                _form: $('#productform').serialize(),
            },
            success: function (result) {
                if (result.error == false) {
                    //$('.snapshot').hide();
                    if ($('.multi-image-container .col-md-6').length==0) {
                        $(result.content).insertBefore('.multi-image-container .col-md-12');
                        $('.image-holder').removeClass('col-md-12').addClass('col-md-6');
                    } else {
                        $('.image-container').append(result.content2);
                    }
                    $('input[name=_filename]').val(result.filename);
                } else
                    alert (result.message);
            }
        })
    } );
}
</script>
@endsection
@section ('jquery')
<script>
    
    $(document).ready( function() {
        Dropzone.autoDiscover = false;
        var processClick = false;

        var myDropzone = new Dropzone("div#dropzoneFileUpload", {
            url: "{{route('upload.image')}}",
            maxFilesize: 10, // MB
            maxFiles: 6,
            parallelUploads: 6,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dictDefaultMessage:'Drop files here or click to upload manually',
            addRemoveLinks: true,
            autoProcessQueue:false,
            uploadMultiple: true,
            sending: function(file, xhr, formData) {
                $("form").find("input").each(function(){
                    if ($(this).attr("name") !==undefined && $(this).attr("name")!='_method')
                        formData.append($(this).attr("name"), $(this).val());
                });
            },
            init: function() {
                var thisDropzone = this;

                // this.on("thumbnail", function(file) {
                //     if (file.width < 500 || file.height < 500) {
                //         file.rejectDimensions();
                //     } else {
                //         file.acceptDimensions();
                //     }
                // });
                // Listen to the sendingmultiple event. In this case, it's the sendingmultiple event instead
                // of the sending event because uploadMultiple is set to true.
                this.on("sendingmultiple", function() {
                    // Gets triggered when the form is actually being sent.
                    // Hide the success button or the complete form.
                });
                this.on("successmultiple", function(files, response) {
                    // Gets triggered when the files have successfully been sent.
                    // Redirect user or notify of success.
                    if(response.message!="success"){
                        alert("Faild to upload image!");
                        return false;
                    }
                    processClick = true
                    $('.uploadPhoto').click();
                });
                this.on("errormultiple", function(files, response) {
                // Gets triggered when there was an error sending the files.
                // Maybe show form again, and notify user of error
                });
            },
            // accept: function(file, done) {
            //     file.rejectDimensions = function() { 
            //         done("Please make sure the image height is at least greater than 500px."); 
            //     };
            //     file.acceptDimensions = done;
            // }
        });

        // myDropzone.on("successmultiple", function(file,resp){
        //     if(resp.message!="success"){
        //         alert("Faild to upload image!");
        //         return false;
        //     }
            
        // });
        //$('.categories option[value="'+$(".categories option:selected").val()+'"]').attr('selected', 'selected');
        $(".categories").focus().find(":selected")[0].scrollIntoView(false);
        $('.print').click( function (e) {
            e.preventDefault();
            
            var printWindow = window.open("{{ URL::to('admin/products/'.$product->id.'/print') }}", "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400");
            var printAndClose = function() {
                if (printWindow.document.readyState == 'complete') {
                    clearInterval(sched);
                    //printWindow.print();
                    //printWindow.close();
                }
            }
            var sched = setInterval(printAndClose, 1000);
        })
        
        {{--  @if ($repair) --}}
        //     $.alert({
        //         title: 'Alert!',
        //         theme: 'black',
        //         animationBounce: 1.5,
        //         content: "This item is at the repair facility. Please go back to repair page and check the box 'Mark as Paid/Complete'",
        //         columnClass: 'col-md-5 col-md-offset-5',
        //     }); 
        {{--  @endif --}}

        $('.wire-discount').click( function (e) {
            e.preventDefault();
            var wire = $('#wire_discount').val()==1 ? 0 : 1

            $.ajax ( {
                type: 'post',
                url: "{{route('wire.discount')}}",
                data: {
                    product_id: {{ $product->id }},
                    enablediscount: wire
                },
                success: function(response) {
                    $('#wire_discount').val(wire)
                    if (wire == 0) {
                        $('.wire-discount').empty()
                        $('.wire-discount').text("X")
                    } else {
                        $('.wire-discount').text("")
                        $('.wire-discount').append('<i class="fas fa-landmark"></i>')
                    }
                }
            })
        })

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");

            if ((target == '#related')) {
                if ($('.related-select option').length == 0) {
                    $.ajax ( {
                        type: 'get',
                        url: "{{route('related.products')}}",
                        data: {
                            catId: @if ($product->categories) {{$product->categories->id}} @else 0 @endif, 
                            product_id: {{ $product->id }},
                            p_model : '{{$product->p_model}}'
                        },
                        success: function(response) {
                            $('.related-select').append(response);
                            initChosen()
                        }
                    })
                }
            }
        });
        
        @if (!$product->categories)
            $.alert({
                title: 'Alert!',
                theme: 'black',
                animationBounce: 1.5,
                content: "Category name is missing for this product. Please set category and update before leaving the page.",
                columnClass: 'col-md-5 col-md-offset-5',
            }); 
        @endif

        $(".uploadPhoto").click( function(e) {
            if (processClick)
                return true
                
            if ($('.dz-image-preview').length > 0) {
                e.preventDefault();
                e.stopPropagation();
            }
            myDropzone.processQueue();
        })
        
        $('#category').editableSelect({ effects: 'fade' })
            .on('select.editable-select', function (e, li) {
                $('#category_selected').val(li.val()
            );
        });

        var mainPath = "{{route('get.customer.byID')}}";

        $('#supplier-input').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            params:{addParam: 'justCompany'}
        });

        $('#company-input').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 99993,
            params:{addParam: 'justCompany'}
        });

        $('#category').editableSelect({ effects: 'fade' })
            .on('select.editable-select', function (e, li) {
                $('#category_selected').val(li.val());
        });
        $('.image-container').lightGallery({
            selector: '.image-item',
            mode: 'lg-fade',
            mousewheel: true,
            download: false,
            share: false,
            fullScreen: false,
            thumbnail:true,
            animateThumb: false,
            showThumbByDefault: false
        })
        $('.p_return').click ( function () {
            if ($(this).prop('checked')) {
                if (confirm ('You are about to return this product back to the supplier. Are you sure you want to do this?')) {
                    $('.qty').val(0);
                }
            }
        })

        $('.cost-container button').click ( function(e) {
            e.preventDefault();
            var cost = $('.cost-container input').val();
            $.confirm({
                title: 'Repair Rate',
                content: '' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>Enter a new repair rate</label>' +
                '<input type="text" autofocus class="repair_value form-control" required />' +
                '</div>' +
                '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Update',
                        btnClass: 'btn-blue',
                        action: function () {
                            var repair_value = this.$content.find('.repair_value').val();
                            if(!repair_value){
                                $.alert('Provide repair rate.');
                                return false;
                            } else if (repair_value.charAt(0) == '0' || repair_value.charAt(0) == '.') {
                                $.alert('Enter number without zeros, decimals, or percent signs in front or end of digits.');
                                return false;
                            }
                         
                            calc = parseFloat(cost) + parseFloat(repair_value);
                            $('#price-input').val(calc);
                            $('.cost-container span').text(repair_value)
                            $('.p_repair_cost').val(repair_value)
                            
                        }
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });

        })

        $('#status-input').change( function () {
            if ($('#status-input :selected').text() == "On Hold" && $('#status-input :selected').text() == "Repair") { 
               // $('.inquiry-form')[0].reset();
                $.fancybox.open({
                    src: "#product-inquiry",
                    type: 'inline',
                    afterClose: function () {
                        //$('#status-input :selected').prop('selected',0);
                    }
                });
            }
        })

        $('.submit-onhold').click( function (e) {
            e.preventDefault();
            if ($('#company-input').val() == '' || $('#amount-input').val() == '') return false
            $.ajax ( {
                    type: 'post',
                    dataType: 'json',
                    url: "{{route('set.onhold')}}",
                    data: {
                        amount: $('#amount-input').val(),
                        company: $('#company-input').val(),
                        _id: $("input[name='_id']").val(),
                        _token: "{{csrf_token()}}"},
                    success: function(response) {
                        if ($.isEmptyObject(response.error)) {
                            $.fancybox.close();
                            $.fancybox.open({
                                src: "<div><p style='padding: 30px 20px;width: 90%'>This product has been reserved and will be held for 72 hours.</p></div>",
                                type: 'html',
                            });
                        } else {
                            alert (response.error)
                        }
                        
                    }
                })
        })

        var config = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : { allow_single_deselect: true },
            '.chosen-select-no-single' : { disable_search_threshold: 10 },
            '.chosen-select-no-results': { no_results_text: 'Oops, nothing found!' },
            '.chosen-select-rtl'       : { rtl: true },
            '.chosen-select-width'     : { width: '95%' },
            'no_results_text'          : "No result found. Press enter to add "
        }

        function initChosen() {
            $('.related-select').chosen(config['related-select']);
        }

        $('.add-image').click( function(e) {
            e.preventDefault();
            addItemById();
        })

        function addItemById() {
            $.confirm({
                title: 'Add Image',
                content: '' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label></label>' +
                '<input type="text" autofocus placeholder="Enter existing image Id" class="name form-control" required />' +
                '</div>' +
                '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function () {
                            var name = this.$content.find('.name').val();
                            if(!name){
                                $.alert('Provide a valid image Id.');
                                return false;
                            } 

                            var request = $.ajax({
                                url: "{{ route('add.image.by.id') }}",
                                method: 'POST',
                                data: {id:  this.$content.find('.name').val(),productId: {{$product->id}}},
                                success: function (result) {
                                    $('.image-container').append(result.content);
                                }
                            })
                            request.fail( function (jqXHR, textStatus) {
                                $.alert (textStatus)
                            })
                        }
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        }
        
        $('#newprice-input').on('input', function() {
            //if ($('#retail-input').val()>0) {
                //var dec = $(this).val()/($('#retail-input').val());
                //dec = parseFloat(dec).toFixed(2);
                //$('#price3P-input').val(parseInt($('#retail-input').val() - ($('#retail-input').val() * (1-dec-0.07) )))
                var additionalRolexMargin = 0;
                if ($('#condition_input option:selected').val()==2) // Unworn
                    additionalRolexMargin = 100;

                price = Math.ceil(parseInt($('#newprice-input').val())+additionalRolexMargin);

                $('#price3P-input').val(Math.ceil(price + (price * {{Chrono24Magin($product->p_price)}})));
                $('#webprice-input').val(Math.ceil($('#newprice-input').val()) + Math.ceil($('#newprice-input').val() * globals.json.webMargin ));
           // }
        })


        $.QueryString = (function(paramsArray) {
            let params = {};

            for (let i = 0; i < paramsArray.length; ++i)
            {
                let param = paramsArray[i]
                    .split('=', 2);

                if (param.length !== 2)
                    continue;

                params[param[0]] = decodeURIComponent(param[1].replace(/\+/g, " "));
            }

            return params;
        })(window.location.search.substr(1).split('&'))

        if ($.QueryString["reminder"]) {
            var reminder = $.QueryString["reminder"];
            var request = $.ajax({
                url: '{{route("load.reminder")}}',
                data: {id: reminder},
                success: function (result) {
                    $.alert({
                        title: 'Alert!',
                        theme: 'black',
                        animationBounce: 1.5,
                        content: result,
                        columnClass: 'col-md-5 col-md-offset-5',
                        buttons: {
                            Acknowledge: function(){
                                var request = $.ajax({
                                    url: "{{ route('set.read.status') }}",
                                    data: {id: reminder},
                                    success: function (result) {
                                        $.alert('System acknowledged. This message will not be shown again.')
                                    }
                                })
                                request.fail( function (jqXHR, textStatus) {
                                    
                                })
                            }
                        }
                    });        
                }
            })
            request.fail( function (jqXHR, textStatus) {
                
            })
            
        }

        $('.newcolumn').click(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Create a new column name',
                content: '' +
                '<form action="" class="formColumn">' +
                '<div class="form-group">' +
                '<label>Enter name for new column</label>' +
                '<input type="text" style="width:100%" autofocus class="column_name form-control" name="column_name" required />' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Select type of new column</label>' +
                '<select style="width:100%" class="column_type form-control" name="column_type">' +
                '<option>String</option>' +
                '<option>Money</option>' +
                '<option>Integer</option>' +
                '</select>' +
                '</div>' +
                '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function () {
                            var column_name = this.$content.find('.column_name').val();
                            if(!column_name){
                                $.alert('Provide a valid column name.');
                                return false;
                            }
                            
                            var request = $.ajax({
                                type: "POST",
                                url: "{{ route('new.column') }}",
                                data: {form:  $('.formColumn').serialize()},
                                success: function (result) {
                                    $.alert({
                                        content: result,
                                        buttons: {
                                            confirm: {
                                                text: 'Ok',
                                                btnClass: 'btn-blue',
                                                action: function () {
                                                    location.reload();
                                                }
                                            }
                                        }
                                    });
                                }
                            })
                            request.fail( function (jqXHR, textStatus) {
                                $.alert (textStatus)
                            })
                        }
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        })

        if ($.QueryString["print"]) { // This is being set in ProductsController::store
            var queryString = $.QueryString["print"];
            var printWindow = window.open("/admin/products/"+queryString+"/print", "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=0,left=500,width=600,height=800");
                var printAndClose = function() {
                    if (printWindow.document.readyState == 'complete') {
                        printWindow.print();
                        clearInterval(sched);
                    }
                }
                var sched = setInterval(printAndClose, 1000);

                window.history.pushState('data','Title','orders');
        }

        $(document).on('click','.delete-image', function() {
            var _this = $(this);
            var img = $(this).parent().find('img')
            var request = $.ajax({
                type: "POST",
                url: "{{route('delete.image')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    imageId: img.attr('data-id'), 
                    id: img.attr('data-pid'),
                    filename: img.attr('src')
                },
                success: function (result) {
                    $(_this).parent('.image').remove();
                    if ($('.delete-image').length==0) {
                        $('.snapshot').show();
                        $('.multi-image-container .loadedimages').remove();
                        $('.multi-image-container .col-md-6').addClass('col-md-12').removeClass('col-md-6');
                    }
                }
            })
            request.fail( function (jqXHR, textStatus) {
                //alert ("Requeset failed: " + textStatus)
            })
        })
        $('.activeSnapshot').click( function (e) {
            e.preventDefault();
            $('.activeSnapshot').hide();
        })
        
        if ($('.delete-image').length==0) {
                $('.snapshot').show();
            }
    })
</script>
@endsection