<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Post;
use App\Models\RepairProduct;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\InquiryController;
use App\Mail\GMailer; 
use App\Mail\SellEmail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Image;
use Imagick;
use Input;

class DropzoneController extends Controller
{
    public function deleteImage(Request $request) {
        
        if ($request->ajax()) {
            if (isset($request['isduplicate']))
                return \Response::json('duplicate', 200);

            $imageLocation='';

            //return response()->json($productImage->count());

            if (isset($request['imageId'])) {
                $imageId = $request['imageId'];
                $id= $request['id'];

                $productImage = Image::find($imageId);
                $totalImages = \App\Models\ProductImage::where('image_id',$imageId)->get()->count();

                $product = Product::find($id);
                $product->images()->detach($imageId);

                if($productImage) {
                    if ($totalImages==1) {
                        $productImage->delete();
                    } else 
                        $imageLocation = $productImage->location;
                }
            } elseif (isset($request['job_id'])) {
                $repair = RepairProduct::where('job_id',$request['job_id'])->first();
                $imageLocation = $repair->image_path;

                $imageThumbLocation = base_path().$request['filename'];
                $imageThumbLocation = str_replace('/images','/public/images/thumbs',$imageThumbLocation);
                
                if (file_exists($imageThumbLocation))
                    unlink($imageThumbLocation);

                $imagelocation = str_replace('thumbs','',$imageThumbLocation);
                if (file_exists($imagelocation))
                    unlink($imagelocation);
                    
                $repair->image_path = '';
                $repair->update();
            }  elseif (isset($request['blade']) && $request['blade']=="categories") {
                $category = Category::where('id',$request['_id'])->first();
                $imageLocation = $category->image_name;

                $imageThumbLocation = base_path()."/public/images/categories/thumbs/$imageLocation";
                
                if (file_exists($imageThumbLocation))
                    unlink($imageThumbLocation);

                $imagelocation = str_replace('thumbs/','',$imageThumbLocation);
                if (file_exists($imagelocation))
                    unlink($imagelocation);
                    
                $category->image_name = null;
                $category->update();
            }

            if ($imageLocation) {
                if (Image::where('location',$imageLocation)->exists()) {
                    if ($totalImages==1) {
                        $imageThumbLocation = base_path().$request['filename'];
                        if (file_exists($imageThumbLocation))
                            unlink($imageThumbLocation);

                        $imagelocation = str_replace('thumbs/','',$imageThumbLocation);
                        if (file_exists($imagelocation))
                            unlink($imagelocation);
                    }
                }
            }
            return \Response::json('success', 200);
        }
    }

    public function deleteImageFromPost(Request $request) {
        if ($request->ajax()) {
            $id= $request['post_id'];
            
            $post = Post::find($id);
            if (file_exists(base_path().'/public/images/posts/'.$post->image)) 
                unlink(base_path().'/public/images/posts/'.$post->image);

            if (file_exists(base_path().'/public/images/posts/thumbs/'.$post->image)) 
                unlink(base_path().'/public/images/posts/thumbs/'.$post->image);

            $post->update(['image'=>NULL]);
            return \Response::json('success', 200);
        }
    }
    
    public function deleteCustomerImage(Request $request) {
        if ($request->ajax()) {
            $id= $request['customer_id'];
            
            $customer = Customer::find($id);
            unlink(base_path().'/public/images/logo/'.$customer->logo);
            unlink(base_path().'public/images/logo/thumbs/'.$customer->logo);

            $customer->update(['logo'=>NULL]);
            return \Response::json('success', 200);
        }
    }

    public function capturedImage(Request $request) {
        if ($request->ajax()) {
            $captured_image = $request['captured_image'];
            $id=0;

            if (isset($request['_form'])) {
                parse_str($request['_form'],$output);
                if (isset($output['_id']))
                    $id=$output['_id'];

                $validator = array(
                    'p_category' => 'required',
                );
                
                $validation = \Validator::make($output, $validator);
                
                if ($validation->fails()) {
                    return \Response::json(array('error'=>true,'message'=>$validation->messages()->first(),'code'=>400), 200);
                }  
            }

            $content='';$content2='';
            if (isset($output['title']) && !$output['title']) {
                $category = \App\Models\Category::find($output['category_selected']);
                if (isset($output['p_model']))
                    $model = $output['p_model'];
                else $model = '';

                if (isset($output['p_reference']))
                    $reference = $output['p_reference'];
                else $reference = '';

                $orgTitle = $category->category_name.' '.$model.' '.$reference;
            } elseif (isset($output['title'])) {
                $orgTitle = $output['title'];
            } else $orgTitle = $request['title'];

            $title = strtolower(str_replace([' ','-','.','/','&',"'"],'-',$orgTitle));
            $fileName = rand(11111, 99999) . '.jpg'; // renaming image
            $fileName = $title.'-snapshot-'.$fileName;
            
            $img = str_replace('data:image/jpeg;base64,', '', $captured_image);
            $img = str_replace(' ', '+', $img);
            $img = base64_decode($img);

            $blade='';
            if (isset($request['blade'])) {
                $blade='repair';
            }

            if ($blade!='repair') {
            ob_start();
            ?>        
                
                <div class="col-md-6 loadedimages">
                <label for="reference-input" class="col-form-label">Images</label>
                
                <div class="image-container">
                    <div class="image">
                        <div class="delete-image">X</div>
                        <img alt="<?= $orgTitle ?>" src="<?=  '/images/thumbs/' . $fileName  ?>" title="<?=  $orgTitle  ?>" >
                        <div class="position"><input type="text" value="0" placeholder="image position" class="position-input" /></div>
                        <input type="hidden" name="filename[]" value="<?= $fileName ?>" />
                    </div>
                </div>
            
                
            <?php
            $content = ob_get_clean();
            ob_start();
            ?>   
                <div class="image">
                    <div class="delete-image">X</div>
                    <img alt="<?= $orgTitle ?>" src="<?=  '/images/thumbs/' . $fileName  ?>" title="<?=  $orgTitle  ?>" >
                    <div class="position"><input type="text" value="0" placeholder="image position" class="position-input" /></div>
                    <input type="hidden" name="filename[]" value="<?= $fileName ?>" />
                </div>
            <?php
            $content2 = ob_get_clean();
            }
            file_put_contents(public_path("/images/").$fileName,$img);

            if (!file_exists(base_path()."/public/images/thumbs/")) {
                mkdir(base_path()."/public/images/thumbs/");
            }

            $imagelocation = base_path()."/public/images/$fileName" ;
            $newimagelocation = base_path()."/public/images/thumbs/".$fileName ;
            
            list($width, $height, $type, $attr) = getimagesize($imagelocation);
            $img = new Imagick($imagelocation);

            $img->scaleImage(320,240,true);    
           
            $img->setImageBackgroundColor('white');
            $w = $img->getImageWidth();
            $h = $img->getImageHeight();
            // if ($h < 500)
            //     $img->cropImage($w, 500, 0, 0);

            $img->extentImage(450,450,($w-450)/2,($h-450)/2);
            $img->writeImage($newimagelocation);

            return \Response::json(array('error'=>false,'content'=>$content,'content2'=>$content2,'message'=>'success','title'=>$title,'filename'=>$fileName), 200);
        }
    }

    private function backgroundMasking($image,$toImageFile) {
        //Load the image
        $imagick = new \Imagick($image);

        $imagick->thumbnailImage(450, 450,true,true);
        $backgroundColor = "rgb(255, 255, 255)";
        $fuzzFactor = 0.1;

        // Create a copy of the image, and paint all the pixels that
        // are the background color to be transparent
        $outlineImagick = clone $imagick;
        $outlineImagick->transparentPaintImage(
            $backgroundColor, 0, $fuzzFactor * \Imagick::getQuantum(), false
        );
        
        // Copy the input image
        $mask = clone $imagick;
        // Deactivate the alpha channel if the image has one, as later in the process
        // we want the mask alpha to be copied from the colour channel to the src
        // alpha channel. If the mask image has an alpha channel, it would be copied
        // from that instead of from the colour channel.
        $mask->setImageAlphaChannel(\Imagick::ALPHACHANNEL_DEACTIVATE);
        //Convert to gray scale to make life simpler
        $mask->transformImageColorSpace(\Imagick::COLORSPACE_GRAY);

        // DstOut does a "cookie-cutter" it leaves the shape remaining after the
        // outlineImagick image, is cut out of the mask.
        $mask->compositeImage(
            $outlineImagick,
            \Imagick::COMPOSITE_DSTOUT,
            0, 0
        );
        
        // The mask is now black where the objects are in the image and white
        // where the background is.
        // Negate the image, to have white where the objects are and black for
        // the background
        $mask->negateImage(false);

        $fillPixelHoles = false;
        
        if ($fillPixelHoles == true) {
            // If your image has pixel sized holes in it, you will want to fill them
            // in. This will however also make any acute corners in the image not be
            // transparent.
            
            // Fill holes - any black pixel that is surrounded by white will become
            // white
            $mask->blurimage(2, 1);
            $mask->whiteThresholdImage("rgb(10, 10, 10)");

            // Thinning - because the previous step made the outline thicker, we
            // attempt to make it thinner by an equivalent amount.
            $mask->blurimage(2, 1);
            $mask->blackThresholdImage("rgb(255, 255, 255)");
        }

        //Soften the edge of the mask to prevent jaggies on the outline.
        $mask->blurimage(2, 2);

        // We want the mask to go from full opaque to fully transparent quite quickly to
        // avoid having too many semi-transparent pixels. sigmoidalContrastImage does this
        // for us. Values to use were determined empirically.
        $contrast = 15;
        $midpoint = 0.7 * \Imagick::getQuantum();
        $mask->sigmoidalContrastImage(true, $contrast, $midpoint);

        // Copy the mask into the opacity channel of the original image.
        // You are probably done here if you just want the background removed.
        $imagick->compositeimage(
            $mask,
            \Imagick::COMPOSITE_COPYOPACITY,
            0, 0
        );

        // To show that the background has been removed (which is difficult to see
        // against a plain white webpage) we paste the image over a checkboard
        // so that the edges can be seen.
        
        // Create the check canvas
        $canvas = new \Imagick();
        $canvas->newPseudoImage(
            $imagick->getImageWidth(),
            $imagick->getImageHeight(),
            "xc:white"
            //"pattern:checkerboard"
        );

        // Copy the image with the background removed over it.
        $canvas->compositeimage($imagick, \Imagick::COMPOSITE_OVER, 0, 0);
        
        //Output the final image
        $canvas->setImageFormat('jpg');
        //header("Content-Type: image/png");
        $blob = $canvas->getImageBlob();
        file_put_contents($toImageFile, $blob);
    }

    public function uploadFiles(Request $request) {
        if ($request->ajax()) {

            if ( isset($request['_form']))
                parse_str($request['_form'],$output);
            else $output = $request;

            $rules = array(
                'file' => 'image|max:10000',
            );

            $position=0;
            if (isset($output['_id']))
                $id = $output['_id'];
            else $id=0;
            //return response()->json($output);
            if (!isset($output['title'])) {
                $orgTitle = str_replace([' ','-','.','/','&',"'"],'-',strtolower($output['sell_contact_name']));
            } else {
                if (!$output['title']) {
                    $category = \App\Models\Category::find($output['category_selected']);
                    if ($category)
                        $orgTitle = $category->category_name.' '.$output['p_model'].' '.$output['p_reference'];
                    else $orgTitle = $output['p_model'].' '.$output['p_reference'];

                } else $orgTitle = $output['title'];
            }

            $title=$orgTitle;

            $bladeName='';
            if (!empty($output['blade']))
                $bladeName = $output['blade'];
            else {
                // $imageposition =  \App\Image::where('product_id',$id)
                //     ->orderByRaw('position desc')
                //     ->first();   
                $imageDB = \DB::table('products')->select('position')
                    ->join('product_image','product_id','products.id')
                    ->join('images','images.id','image_id')
                    ->where('product_id','=',$id)
                    ->orderByRaw('position desc')
                    ->first();
                
                if ($imageDB) {
                    $position = $imageDB->position+1;
                    //$isWatermark=false;
                } 
                    // else
                    //     $isWatermark=true;
            }

            $content='';
            foreach($request->file('file') as $file) {   
                $image = array('file' => $file);  

                $validation = \Validator::make($image, $rules);

                if ($validation->fails()) {
                    return \Response::json([
                        'error' => true,
                        'message' => $validation->messages()->first(),
                        'code' =>400
                    ], 400);
                }  
                $new_id=0;
                
                $title = Str::slug($title);
                $extension = 'jpg'; //$file->getClientOriginalExtension(); // getting file extension
                $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
                $fileName = $title.'-'.$fileName;
               
                ob_start();
                if ($bladeName != 'amazon') {
                ?>        
                    <div class="form-group row multi-image-container">
                        <label for="reference-input" class="col-3 col-form-label">Images</label>
                        <div class="col-9">
                            <div class="image-container">
                                <div class="image">
                                    <div class="delete-image">X</div>
                                    <img alt="<?= $orgTitle ?>" src="<?= '/images/thumbs/' . $fileName  ?>" title="<?=  $orgTitle  ?>" >
                                    <div class="position"><input type="text" value="0" placeholder="image position" class="position-input" /></div>
                                    <input type="hidden" name="filename[]" value="<?= $fileName ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                } else { ?>
                    <input type="hidden" name="filename[]" value="<?= $fileName ?>" />
                <?php }
                $content .= ob_get_clean();
                
                if (!$bladeName) {
                    if ($id) {
                         $new_id=\App\Models\Image::create([
                            'title' => $orgTitle,
                            'location' => $fileName,
                            'position' => $position
                        ]);
                        $imageDB = \App\Models\Product::find($id);
                        $imageDB->images()->attach($new_id->id);
                    } 

                    //\Log::info('From Dropzone Controller: ');
                    $upload_success = $file->move(public_path("/images"), $fileName); // uploading file to given path
                    $folderPath = public_path('/images');
                    
                    if (!file_exists("$folderPath/thumbs/")) {
                        mkdir("$folderPath/thumbs",0777,true);
                        chmod("$folderPath/thumbs", 0777);
                    }
        
                    $imagelocation = "$folderPath/$fileName" ;
                    $newimagelocation = "$folderPath/thumbs/".$fileName ;
                    
                    list($width, $height, $type, $attr) = getimagesize($imagelocation);
                    //$this->backgroundMasking($imagelocation,$newimagelocation);
                    $img = new Imagick($imagelocation);
                    $img->setImageFormat ("jpg");
                    if ($width > 450) {
                        $img->thumbnailImage(450, 450,true,true);
                    } else {
                        $img->thumbnailImage(450, 450,true,true);
                    }

                        // if ($isWatermark) {
                        //     $img2 = new Imagick($imagelocation);
                        //     $watermark = new Imagick(base_path()."/images/swissmadeweb.png");
                        //     $watermark->scaleImage(250, 84);

                        //     // Overlay the watermark on the original image
                        //     $img2->compositeImage($watermark, imagick::COMPOSITE_OVER, 0, 0);
                        //     $img2->writeImage($imagelocation);
                        // }

                    //$isWatermark=false;
                    $img->writeImage($newimagelocation);

                    if ($height < 500) {
                        $img = new Imagick($imagelocation);
                        $img->setImageFormat ("jpg");
                        // \Log::debug ($width);
                        $img->extentImage(
                            $width,
                            500,
                            0,
                            ($height-500)/2);
                        $img->writeImage($imagelocation);
                        
                    }

                    $position = $position + 1;
                    
                } elseif ($bladeName=='imagecollection' || 
                                    $bladeName=='categories' || 
                                    $bladeName=='posts' || 
                                    $bladeName=='customer' ||
                                    $bladeName == 'sell-watch-form'||
                                    $bladeName == 'amazon') {
                    
                    if ($bladeName=='imagecollection') {
                        $new_id=ImageCollection::create([
                            'title' => $output['title'],
                            'location' => $fileName
                        ]);
                        $folderName = "collections";
                        $folderNameThumb = "thumbs";
                    } elseif ($bladeName=='amazon') {
                        $folderName = "amazon";
                        $folderNameThumb = "";
                        
                    } elseif ($bladeName=='customer') {
                        if ($id==0) {
                            $new_id=CustomersController::saveCustomer($output);
                        } else {
                            $output['logo'] = $fileName;
                            $new_id=CustomersController::updateCustomer($output,$id);
                            
                        }
                        $folderName = "logo";
                        $folderNameThumb = "thumbs";
                    } elseif ($bladeName=='sell-watch-form') {
                        if (!isset($output['filename'])) {
                            $output['filename']='';
                            $folderName = "sellwatches";
                            $folderNameThumb = "";
                        }
                        
                        $output['filename'].="<a href='https://www.swissmadecorp.com/public/images/sellwatches/$fileName'><img src='https://www.swissmadecorp.com/public/images/sellwatches/$fileName' width='350' height='350'/></a><br>";
                    } elseif ($bladeName=='categories') {
                        
                        if ($id==0) {
                            $new_id=Category::create([
                                'category_name' => $output['category_name'],
                                'category_title' => $output['category_title'],
                                'category_description' => $output['category_description'],
                                'image_name' => $fileName
                            ]);
                        } else {
                            $output['filename'] = $fileName;
                            $new_id=CategoriesController::updateCategory($output,$id);
                            //$new_id=Category::find($id);
                            // $new_id->category_name = $output['category_name'];
                            // $new_id->category_title = $output['category_title'];
                            // $new_id->category_description = $output['category_description'];
                            // $new_id->image_name = $fileName;
                            // if (!$new_id->location) {
                            //     $new_id->location = str_replace([' ','-','.','/','&',"'"],'-',strtolower($output['title']));
                            // }
                            // $new_id->save();
                        }
                        $folderName = "categories";
                        $folderNameThumb = "thumbs";
                    } else {
                        if ($id==0) {
                            $new_id=Post::create([
                                'title' => $output['title'],
                                'post' => $output['posts'],
                                'image' => $fileName
                            ]);
                        } else {
                            $new_id=Post::find($id);
                            $new_id->update([
                                'title' => $output['title'],
                                'post' => $output['posts'],
                                'image' => $fileName
                            ]);
                            
                        }
                        $folderName = "posts";
                        $folderNameThumb = "thumbs";
                    }
                    
                    $upload_success = $file->move(public_path("/images/$folderName"), $fileName); // uploading file to given path
                    
                    if ($folderNameThumb) {
                        if (!file_exists(public_path("/images/$folderName/$folderNameThumb/"))) {
                            mkdir(public_path("/images/$folderName/$folderNameThumb/"));
                        }
                    }

                    $imagelocation = public_path("/images/$folderName/$fileName") ;
                    if ($folderNameThumb) {
                        $newimagelocation = public_path("/images/$folderName/$folderNameThumb/$fileName") ;
                    }

                    list($width, $height, $type, $attr) = getimagesize($imagelocation);
                    
                    $img = new Imagick($imagelocation);
                    $img->setImageBackgroundColor('#ffffff');
                    $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                    $img = $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

                    if ($width > 450) {
                        //Imagick::open($imagelocation)->thumb(450, 450)->saveTo($newimagelocation);
                        //return response()->json($newimagelocation);
                        $img->setImageFormat ("jpg");
                        $img->thumbnailImage(450, 450,true,true);
                        $img->writeImage($newimagelocation);
                    } else {
                        if ($folderNameThumb) {
                            $img->writeImage($newimagelocation);
                        }
                    }
                }
            }
            
            if ($bladeName=='sell-watch-form') {
                //$data=InquiryController::SellYourWatch($output);
                return \Response::json(array('message'=>'success','data'=>$output));

                //Mail::to('info@swissmadecorp.com')->queue(new SellEmail($output));
                //return \Response::json($output);
            }

            if ($upload_success) {
                if (is_object($new_id))
                    $new_id=$new_id->id;
                    
                    return \Response::json(array('message'=>'success','content'=>$content,'filename'=>$fileName,'title'=>$orgTitle,'id'=>$id), 200);
                } else {
                    return \Response::json(array('message'=>'error'), 400);
            }
        }
    }

    public function uploadCustomerFiles(Request $request) {
        if ($request->ajax()) {
            
            $rules = array('file' => 'image|max:10000');

            if (!isset($request['title'])) {
                $orgTitle = str_replace([' ','-','.','/','&',"'"],'-',strtolower($request['sell_contact_name']));
            } else {
                if (!$request['title']) {
                    $category = \App\Models\Category::find($request['category_selected']);
                    $orgTitle = $category->category_name.' '.$request['model'].' '.$request['reference'];
                } else $orgTitle = $request['title'];
            }

            $title=$orgTitle;
            
            foreach($request->file('file') as $file) {   
                $image = array('file' => $file);  

                $validation = \Validator::make($image, $rules);

                if ($validation->fails()) {
                    return \Response::json([
                        'error' => true,
                        'message' => $validation->messages()->first(),
                        'code' =>400
                    ], 400);
                }  
                $new_id=0;
                
                $title = strtolower(str_replace([' ','-','.','/','&'],'-',$title));
                $extension = $file->getClientOriginalExtension(); // getting file extension
                $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
                $fileName = $title.'-'.$fileName;
                
                if (!isset($request['filename'])) {
                    $request['filename']='<ul style="list-style: none;">';
                    $folderName = "sellwatches";
                    $folderNameThumb = "";
                }

                $request['filename'].="<li style='width: 250px;display:inline-block'><a href='https://www.swissmadecorp.com/public/images/sellwatches/$fileName'><img src='https://www.swissmadecorp.com/public/images/sellwatches/$fileName' width='250' ></a></li>";

                $upload_success = $file->move(public_path("/images/$folderName"), $fileName); // uploading file to given path
                $imagelocation = public_path("/images/$folderName/".$fileName) ;
                
                //list($width, $height, $type, $attr) = getimagesize($imagelocation);
                    
                
                // $img = new Imagick($imagelocation);
                // $img->thumbnailImage(450, 450,true,true);
                //Imagick::open($imagelocation)->saveTo($newimagelocation);
            
            }
            
            //$request['filename'] .= '</ul>';
            // $data = array(
            //     'contact_name'=>$request['sell_contact_name'],
            //     'email' => $request['sell_email'],
            //     'phone' => $request['sell_phone'],
            //     'notes'=>$request['sell_notes'],
            //     'filename' => $request['filename']
            // );
            
            $data = array(
                'contact_name'=>$request['sell_contact_name'],
                'email' => $request['sell_email'],
                'phone' => $request['sell_phone'],
                'notes'=>$request['sell_notes'],
                'image' => $request['filename'],
                'subject'=>'Swissmade - I want to sell my watch',
                'template' => 'emails.sellwatch',
            );

            
            $gmail = new GMailer($data);
            $gmail->send();

            //Mail::to('info@swissmadecorp.com')->queue(new SellEmail($data));
            return \Response::json(array('message'=>'success'), 200);
            
        }
    }
}
