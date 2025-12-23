<?php

namespace App\Livewire;

use Imagick;
use App\Libs\eBayMain;
use Carbon\Carbon;
use App\Models\Image;
use Livewire\Component;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\Product;
use App\Models\Reminder;
use App\Events\ProductUpdateEvent;
use App\Events\MessagesEvent;
use App\Models\EbayListing;
use App\Jobs\eBayEndItem;
use App\SearchCriteriaTrait;
use App\Jobs\AutomateEbayPost;
use Livewire\WithPagination;
use App\Models\GlobalPrices;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use App\Jobs\AIProductDescription;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\File;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;


class ProductItem extends Component
{
    use WithFileUploads,  SearchCriteriaTrait;

    public $item;

    public $status;

    public $thumbnails = [];
    public $created_date;
    public int $category_selected_id=0;

    public ?string $category_selected_text; // ?string means it could be null

    public $categories = null;
    public $custom_columns = 0;
    public $groupId = 0;
    public int $productId = 0;
    public $product = null;
    public $newprice;
    public int $totalorders = 0;
    public int $is_duplicate = 0;
    public int $perPage = 10;
    public $orders;
    public int $loggedInUser = 0;

    public array $packageStatuses = [];

    public string $statusB = '';

    public $images;

    public function clearFields() {

        $columns = $this->clearAllFields();

        $this->category_selected_id = 0;
        $this->category_selected_text = null;
        $this->images = [];
        $this->thumbnails = [];
        $this->productId = 0;
        $this->totalorders = 0;

        // $this->item = null;
        // $this->reset('item','is_duplicate','images','newprice','status');
        $this->resetValidation();
        $this->reset($columns);

    }

    private function clearAllFields() {
        foreach ($this->custom_columns as $column) {
            $columns[] = 'item.'.$column;
        }

        array_push($columns,'item.title','item.id','item.slug','item.p_bezelmaterial','item.p_model','item.serial_code',
        'item.p_category','item.p_price', 'item.p_casesize','item.p_material','item.p_condition',
        'item.p_strap','item.p_clasp','item.bezel_features','item.p_reference','item.p_serial',
        'item.p_color','item.p_gender', 'item.supplier','item.supplier_invoice','status','is_duplicate',
        'item.p_year','item.water_resistance','item.movement','item.p_dial_style','item.p_box',
        'item.p_papers','item.p_smalldescription','item.p_longdescription','item.p_comments','images',
        'newprice','item.p_retail','item.p_additional_cost','item.p_additional_cost_notes');

        return $columns;
    }

    protected function rules() {
        // $rules = [
        //     'images.*' => ['image','max:2048'],
        //     'category_selected_text' => ['required','min:3'],
        //     'status' => ['required','not_in:-1'],
        //     // 'item.p_bezelmaterial' => ['required','not_in:0'],
        //     'item.p_material' => ['required','not_in:0'],
        //     'item.p_model' => ['required'],
        //     'item.p_condition' => ['required','not_in:0'],
        //     // 'item.p_strap' => ['required','not_in:0'],
        //     // 'item.p_dial_style' => ['required','not_in:0'],
        //     'item.supplier' => ['required'],
        //     // 'item.p_color' => ['required','not_in:0'],
        //     'item.p_price' => ['required'],
        //     // 'item.movement' => ['required','not_in:-1'],
        //     'item.p_qty' => ['required'],
        //     'item.p_gender' => ['required','not_in:-1'],
        //     // 'item.p_serial' => [
        //     //     'required',
        //     //     Rule::unique('products', 'p_serial')->where(function ($query) {
        //     //         return $query
        //     //             ->where('id', '<>' , $this->productId)
        //     //             ->where('category_id', $this->category_selected_id)
        //     //             ->where('p_qty', '=', 1)
        //     //             ->where('p_serial', '<>', 'N/A');
        //     //     }),
        //     // ],
        // ];

        // $rules['item.p_serial'] = ['required'];

        // if ($this->productCategoryName == 'Watches') {
        //     if (!empty($this->item['p_serial']) && $this->item['p_serial'] !== 'N/A') {
        //         $rules['item.p_serial'] = [
        //             'required',
        //             Rule::unique('products', 'p_serial')->where(function ($query) {
        //                 return $query
        //                     ->where('id', '<>', $this->productId)
        //                     ->where('category_id', $this->category_selected_id)
        //                     ->where('p_qty', '=', 1)
        //                     ->where('p_serial', '<>', 'N/A');
        //             }),
        //         ];
        //     }

        //     $rules['item.movement'] = ['required', 'not_in:-1'];
        //     $rules['item.p_color'] = ['required', 'not_in:0'];
        //     $rules['item.p_dial_style'] = ['required', 'not_in:0'];
        //     $rules['item.p_bezelmaterial'] = ['required', 'not_in:0'];
        //     $rules['item.p_strap'] = ['required', 'not_in:0'];
        // }

        // return $rules;

        $rules = [
            'images.*' => ['image','max:2048'],
            'category_selected_text' => ['required','min:3'],
            'status' => ['required','not_in:-1'],
            'item.p_model' => ['required'],
            'item.supplier' => ['required'],
            'item.p_price' => ['required'],
            'item.p_qty' => ['required'],
            'item.p_gender' => ['required','not_in:-1'],
        ];

        // Conditional rules based on the productCategoryName
        if ($this->groupId == 0) { // Watch group
            $rules = array_merge($rules, [
                'item.p_bezelmaterial' => ['required','not_in:0'],
                'item.p_material' => ['required','not_in:0'],
                'item.p_condition' => ['required','not_in:0'],
                'item.p_strap' => ['required','not_in:0'],
                'item.p_dial_style' => ['required','not_in:0'],
                'item.p_color' => ['required','not_in:0'],
                'item.movement' => ['required','not_in:-1'],
                'item.p_serial' => [
                    'required',
                    Rule::unique('products', 'p_serial')->where(function ($query) {
                        return $query
                            ->where('id', '<>', $this->productId)
                            ->where('category_id', $this->category_selected_id)
                            ->where('p_qty', '=', 1)
                            ->where('p_serial', '<>', 'N/A');
                    }),
                ],
            ]);
        }

        return $rules;
    }

    protected $messages = [
        'images.*' =>'Images highest maximum upload size is 2,048 MB.',
        'category_selected_text.required' => 'This field is required.',
        'category_selected_text.min' => 'The selected category must be no less than 3 character long',
        'status.required' => 'This field is required.',
        'status.not_in' => 'The selected status is invalid.',
        'item.p_bezelmaterial.required' =>'This field is required.',
        'item.p_bezelmaterial.not_in' => 'The selected bezel material is invalid.',
        'item.p_material.required' => 'This field is required.',
        'item.p_material.not_in' => 'The selected material is invalid.',
        'item.p_model.required' => 'This field is required.',
        'item.p_condition.required'=>'This field is required.',
        'item.p_condition.not_in' => 'The selected condition is invalid.',
        'item.p_strap.required'=>'This field is required.',
        'item.p_strap.not_in' => 'The selected strap is invalid.',
        'item.p_serial.required' => 'This field is required.',
        'item.p_serial.unique' => 'The serial number must be a unique number.',
        'item.p_dial_style.required'=>'This field is required.',
        'item.p_dial_style.not_in' => 'The selected dial style is invalid.',
        'item.supplier.required' => 'This field is required.',
        'item.supplier.not_in' => 'The selected supplier is invalid.',
        'item.p_color.required'=>'This field is required.',
        'item.p_color.not_in' => 'The selected color is invalid.',
        'item.p_price.required' => 'This field is required.',
        'item.movement.required' => 'This field is required.',
        'item.movement.not_in' => 'The movement color is invalid.',
        'item.p_qty.required' => 'This field is required.',
        'item.p_gender.required' => 'This field is required.',
        'item.p_gender.not_in' => 'The selected gender is invalid.',
    ];

    public function selectedStatus() {
        return $this->status;
    }

    public function removeImage($index) {
        $image = $this->thumbnails[$index];
        $totalIdenticalImages = 0;

        if (!$this->is_duplicate) {
            // Delete temporary file
            $imageLocation='';
            if (isset($image['path'])) {

                if ($image['id']) {
                    $imageId = $image['id'];

                    $productImage = Image::find($imageId);
                    $totalImages = \App\Models\ProductImage::where('image_id',$imageId)->get()->count();

                    if ($this->productId) {
                        $product = Product::find($this->productId);
                        $product->images()->detach($imageId);
                    }

                    if($productImage) {
                        if ($totalImages==1) {
                            $productImage->delete();
                        } else
                            $imageLocation = $productImage->location;
                    }

                }
                $totalIdenticalImages = Image::where('location',basename($image['path']))->count();

            }

            if ($totalIdenticalImages == 1) {
                if (is_string($image['path']))
                    File::delete([base_path().$image['path']]);
                else
                    File::delete([$image['path']->getPath()."/".$image['path']->getFilename()]);


                if ($imageLocation) {
                    if (Image::where('location',$imageLocation)->exists()) {
                        if ($totalImages==1) {
                            $imageThumbLocation = base_path().$image['path'];
                            if (file_exists($imageThumbLocation))
                                unlink($imageThumbLocation);

                            $imagelocation = str_replace('thumbs/','',$imageThumbLocation);
                            if (file_exists($imagelocation))
                                unlink($imagelocation);
                        }
                    }
                }
            }
        }

        // Refresh images after removal
        // $this->thumbnails = $this->thumbnails->values();
        // $this->item['position'] = $this->item['position']->values();
        array_splice($this->thumbnails, $index, 1);
        array_splice($this->item['position'],$index,1);

        if (count($this->thumbnails) == 0) {
            $this->reset('images');
            $this->reset('thumbnails');
            // $this->dispatch('display-message','');
        }


    }

    #[On('process-product-item-messages')]
    public function productItemMessages($data) {

        if (!empty($data['location']) && $data['location'] == "repair") {
            if ($data['id']) {
                $this->product = Product::find($data['id']);
                $this->status = $this->product->p_status;

                eBayEndItem::dispatch([$data['id']]);

            }
        }
    }

    public function insertExistingImage() {
        $this->dispatch('swalInput', ['Enter image id in the text below and click apply.']);
    }

    public function additionalCostDispatcher() {
        $additional_cost = $this->item['p_additional_cost'];
        if (!$additional_cost)
            $additional_cost = "";

        $additional_cost_notes = $this->item['p_additional_cost_notes'];
        if (!$additional_cost_notes)
            $additional_cost_notes = "";

        $this->dispatch('swalAddToCost', [
            'msg' =>'Enter additional value to the cost and a small note',
            'input1' => $additional_cost,
            'input2' => $additional_cost_notes
        ]);
    }

    #[On('additional-cost')]
    public function setAdditionalCost($input1, $input2) {
        // Get input value and do anything you want to it
        if ($input1) {
            $this->item['p_additional_cost'] = $input1;
            $this->item['p_price'] += $input1;
        }
        $this->item['p_additional_cost_notes'] = $input2;

    }

    #[On('input-confirmed')]
    public function inputConfirmed($data,$from) {
        // Get input value and do anything you want to it

        if ($from=="insertimage") {
            $id = $data;

            if ($id && $this->productId) {
                $previous_image = Image::find($id);

                if ($previous_image) {
                    $product=$this->product;
                    $product->images()->attach($id);

                    $this->thumbnails = [];
                    $this->item['position'] = [];

                    foreach ($product->images as $index => $image) {
                        $this->thumbnails[] = ['path'=>"/images/thumbs/".$image->location,'id'=>$image->id,'position' => $image->position,'main'=>"/images/".$image->location];
                        $this->item['position'][$index] = $image->position;
                    }
                } else {
                    LivewireAlert::title("No image was found with this id.")->warning()->position(Position::TopEnd)->toast()->show();
                }
            }
        } else {

        }
    }

    public function updated($e,$props) {

        if ($e=="images") {
            if (isset($this->item['position']))
                $i = count($this->item['position']);
            else $i = 0;

            foreach ($this->images as $index => $image) {
                $this->item['position'][$i] = $i;
                $this->thumbnails[] = ['path'=>$image,'id'=>"",'position' => $i];
                $i++;
            }

        } elseif ($e == "newprice") {
            $rolexBoxMargin = 0;
            $amount = $this->newprice;

            if ($this->category_selected_id == 1 && $this->item['p_condition']==2) $rolexBoxMargin=100;

            $platforms = GlobalPrices::all();
            foreach ($platforms as $platform) {
                $percent = $platform->margin;

                if (!$amount) $amount = 0;

                $newprice = ceil($amount+$rolexBoxMargin+(($amount+$rolexBoxMargin) * ($percent/100)));
                if ($platform->platform == "Chrono24") {
                    $this->item['p_price3P'] = $newprice;
                } elseif ($platform->platform == "Website")
                    $this->item['web_price'] = $newprice;

            }

        } elseif ($e == 'status') {
            if ($this->status == 8) {
                $this->item['p_qty'] = 0;
            } elseif ($this->status == 0)
                $this->item['p_qty'] = 1;
        }
    }

    private function createSlug() {
        $slug = '';$model=''; $reference='';

        if (isset($this->item['p_model']))
            $model='-'.$this->item['p_model'];

        if (isset($this->item['slug']))
            $slug = $this->item['slug'];

        if (isset($this->item['p_reference']))
            $reference = '-'.$this->item['p_reference'];

        if (!$slug) {
            $r=rand(11111, 99999);
            $g = priceToLetters($this->item['p_price']);

            $category_name=$this->category_selected_text;

            $slug =  strtolower(str_replace([' ','&','/','.'],'-',$category_name.$model.$reference.'-'.$this->item['p_color'].'-'.$g.'-'.$r));
            if (strpos($slug,'--')>0)
                $slug = str_replace('--','-',$slug);
            if (strpos($slug,'--')>0)
                $slug = str_replace('--','-',$slug);
        }

        return $slug;
    }

    private function generateTitle() {
        $material='';$model='';
        $reference='';$category_name='';$casesize='';
        $features='';

        if (isset($this->item['p_model']))
            $model = $this->item['p_model'];

        if (isset($this->item['p_casesize']))
            $casesize=str_replace(' ', '', $this->item['p_casesize']);

        if (isset($this->item['p_reference']))
            $reference = $this->item['p_reference'];

        if (isset($this->item['p_material'])) {
            if ($this->groupId==0)
                $material = Materials()->get($this->item['p_material']);
            elseif ($this->groupId==1)
                $material = MetalMaterial()->get($this->item['p_material']);
        }

        if (is_numeric($this->item['p_gender']))
            $gender = Gender()->get($this->item['p_gender']);
        else $gender = $this->item['p_gender'];

        $category_name=$this->category_selected_text;

        $orgTitle='';

        if ($this->groupId == 0)
            $orgTitle = "$category_name $model $casesize $reference $material $gender Watch";
        else {
            $jewelryType =$this->item['jewelry_type'];
            if ($category_name)
                $orgTitle = $category_name.' ';
            if ($model)
                $orgTitle .= $model.' ';
            if ($reference)
                $orgTitle .= $reference.' ';

            $orgTitle .= "$material $gender $jewelryType";
        }


        return $orgTitle;
    }

    #[On('edit-item')]
    public function editItem($id) {

        if ($id) {
            $includeToSelect="";
            $custom_columns = '';

            foreach ($this->custom_columns as $column) {
                $custom_columns .= '`' . $column.'`,';
            }

            if ($this->is_duplicate==0) {
                $includeToSelect = "p_additional_cost,p_additional_cost_notes,supplier,p_price,slug,p_serial,p_price,p_price3P,p_newprice,web_price,";
            }
            $product = Product::select(\DB::raw('id,created_at,title,category_id,p_bezelmaterial,p_model,p_casesize, serial_code,
            p_material,p_condition,p_qty,p_strap,p_clasp,bezel_features,'.$includeToSelect.'p_retail,
            p_reference,p_color,p_gender,p_status,supplier_invoice,water_resistance,'.$custom_columns.
            'movement,p_year,p_dial_style,p_box,p_papers,p_smalldescription,p_longdescription,p_comments'))->where("id",$id)->first();


            $this->item = $product->toArray();

            // dd($this->item );
            if ($this->is_duplicate) {
                $this->item['p_qty'] = 1;
                $this->item['p_year'] = '';
                $this->item['p_status'] = 0;
                $this->status = 0;
                $this->item['movement'] = '';
                foreach ($this->custom_columns as $column) {
                    $this->item[$column] = '';
                }

                $this->p_year = '';
                $this->totalorders = 0;
                $this->orders = null;
                $this->item['p_papers'] = 0;
                $this->item['p_box'] = 0;
            } else {
                $this->item['p_box'] = $this->item['p_box'] == 1 ? true : false;
                $this->item['p_papers'] = $this->item['p_papers'] == 1 ? true : false;
                $this->newprice = $this->item['p_newprice'];
                $this->productId = $id;
                $this->status = $product->p_status;
                if ($id != 1) {
                    $this->totalorders = $product->orders->count();
                    $this->orders = $product->orders;
                } else {
                    $this->orders = null;
                    $this->totalorders = 0;
                }
            }

            $this->created_date = $product->created_at;

            $this->category_selected_text = $product->categories->category_name;
            $this->category_selected_id = $product->category_id;

            foreach ($product->images as $index => $image) {
                $this->thumbnails[] = ['path'=>"/images/thumbs/".$image->location,'id'=>$image->id,'position' => $image->position,'main'=>"/images/".$image->location];
                $this->item['position'][$index] = $image->position;
            }

            if (is_numeric($this->item['p_gender'])) {
                $gender = $this->item['p_gender'];
            } else $gender = array_search($this->item['p_gender'],Gender()->toArray());

            $this->item['p_gender'] = $gender;

            //$gender = array_search($this->item['p_gender'],Gender()->toArray());

            $this->product = $product;
        }
    }

    public function saveProduct() {
        $validatedData = $this->validate();

        // if (\Auth::user()->name != 'Edward B') {
            $productId = $this->save();

            if ($productId) {
                $reminder = $this->checkForReminders();
                if (!empty($reminder)) { // Dispatches to display-message in Products component to show alert
                    LivewireAlert::title("Customer Reminder")->text($reminder->assigned_to .' asked to contact him should this item becomes available.')->asInfo('Ok')->show();
                    $reminder->update(['status' => 1]);
                    $this->dispatch('display-message',['msg'=>'Product Saved.','id'=>$productId,'is_duplicate'=>$this->is_duplicate, 'reminder' => true]); // We set reminder to true
                } else
                    $this->dispatch('display-message',['msg'=>'Product Saved.','id'=>$productId,'is_duplicate'=>$this->is_duplicate]);

            } else $this->dispatch('display-message',['msg'=>"This product is assigned to an order. Please remove the product from the invoice and try again.",'id'=>$productId,'is_duplicate'=>$this->is_duplicate,'status' => 'info']);

            $this->clearFields();
            ProductUpdateEvent::dispatch();

        // } else {
        //     $reminder = $this->checkForReminders();
        //     dd($reminder);
        // }
        // Check if there are any reminders for this product

    }

    private function checkForReminders() {
        // dd($this->item);
        $size = $this->item['p_casesize'];

        $size_format_1 = str_replace(' mm', 'mm', $size);
        $size_format_2 = str_replace('mm', ' mm', $size_format_1);

        // 3. Build the Eloquent Query with all conditions ANDed together
        $reminder = Reminder::where('criteria', 'LIKE', '%' . $this->category_selected_text . '%')
            ->where('criteria', 'LIKE', '%' . $this->item['p_model'] . '%')
            ->where('criteria', 'LIKE', '%' . $this->item['p_reference'] . '%')

            ->where(function ($query) use ($size_format_1, $size_format_2) {
                // Check for the format without a space ('44mm')
                $query->where('criteria', 'LIKE', '%' . $size_format_1 . '%')
                    // OR check for the format with a space ('44 mm')
                    ->orWhere('criteria', 'LIKE', '%' . $size_format_2 . '%');
            })
            ->where('status', 0)
            ->first();

        if (!$reminder) return null;

            $condition = [];
            $boxpapers = [];

            // 2. Safely unserialize $condition
            if (!empty($reminder->product_condition)) {
                $unserialized_condition = unserialize($reminder->product_condition);
                // Ensure unserialize succeeded and returned an array
                if (is_array($unserialized_condition)) {
                    $condition = $unserialized_condition;
                }
            }

            // 3. Safely unserialize $boxpapers (assuming the property name is reminder->boxpapers)
            if (!empty($reminder->boxpapers)) {
                $unserialized_boxpapers = unserialize($reminder->boxpapers);
                // Ensure unserialize succeeded and returned an array
                if (is_array($unserialized_boxpapers)) {
                    $boxpapers = $unserialized_boxpapers;
                }
            }

            // --- NEW LOGIC FOR STEP 4 ---

            // Condition 1: Check if the item's condition is in the $condition array.
            // IF $condition is empty, the check is bypassed (returns TRUE).
            $condition_met = empty($condition) || in_array($item['p_condition'], $condition);


            // Condition 2: Check if both $box and $papers are in the $boxpapers array.
            // IF $boxpapers is empty, the check is bypassed (returns TRUE).
            $boxpapers_met = empty($boxpapers) ||
                            (in_array($box, $boxpapers) && in_array($papers, $boxpapers));


            // 4. Perform the comparison. Both modified conditions must be true.
            if ($condition_met && $boxpapers_met) {
                return $reminder;
            }

            return null;
    }

    // public function getListeners() {
    //     return [
    //         "echo-private:message.{$this->loggedInUser},new-message" => "MessageNotification"
    //     ];
    // }

    // public function MessageNotification($event) {
    //     if ($event['receiver_id'] == auth()->id())
    //         $this->dispatch('receive-message', $event);
    // }

    #[On('echo:products,ProductUpdateEvent')]
    public function onPackageSent($event) {
        $this->dispatch('refresh-products', $event);
    }

    public function save() {

        // if ($this->groupId == 1) {
        //     $this->item['p_box'] = isset($this->item['p_box']) && $this->item['p_box'] == 'on' ? 1 : 0;
        //     $this->item['p_papers'] = isset($this->item['p_papers']) && $this->item['p_papers'] == 'on' ? 1 : 0;
        // }

        // dd('asdf');

        $cat = null;

        if ($this->category_selected_id==0) {
            if ($this->category_selected_text)
                $cat = Category::where('category_name',$this->category_selected_text)->first();

            if (!$cat)
                $cat = Category::create([
                    'category_name' => $this->category_selected_text,
                    'location' => strtolower(str_replace([' ','&','/','.'],'-',$this->category_selected_text))
                ]);

            $this->category_selected_id = $cat->id;
        }

        $customer = Customer::where('company','=',$this->item['supplier'])->get();
        if (count($customer)==0 && isset($this->item['supplier'])) {
            Customer::create([
                'company' => $this->item['supplier'],
            ]);
        }

        // $this->item['item.web_price'] = $this->item['item.p_newprice'];
        if (is_numeric($this->item['p_gender']))
            $gender = Gender()->get($this->item['p_gender']);
        else $gender = $this->item['p_gender'];

        if (!isset($this->item['web_price']))
            $this->item['web_price'] = 0;

        if (!isset($this->item['p_price3P']))
            $this->item['p_price3P'] = 0;

        $this->item['p_gender'] = $gender;
        $this->item['slug']=$this->createSlug();

        $serial = '';
        if (!empty($this->item['p_serial'])) {
            $serial=strtoupper($this->item['p_serial']);
            $this->item['p_serial'] = $serial;
            $this->item['p_status'] = $this->status;
        }

        if ($this->item['p_status'] == 7 || $this->item['p_status'] == 4 || $this->item['p_status'] == 5) {
            eBayEndItem::dispatch([$this->productId]);
        }

        if ($this->category_selected_text == "Rolex") {
            $year = '';
            if (isset($this->item['p_year']))
                $year = $this->item['p_year'];

            $rolexBySerial = $this->RolexYearBySerial($serial,$year);

            if ($rolexBySerial['serial'] || $rolexBySerial['year']) {
                $this->item['serial_code'] = $rolexBySerial['serial'];
                if ($rolexBySerial['year'])
                    $this->item['p_year'] = $rolexBySerial['year'];
            }
        }

        if ($this->groupId == 2) { // 2=Bezel group id
            $this->item['p_metal_cost'] = $this->item['metal_cost'];
            $this->item['p_metal_market_cost'] = $this->item['metal_cost'];
            $this->item['p_metal_weight'] = $this->item["metal_weight"];
            $this->item['p_diamond_cost'] = $this->item['diamond_cost'];
            $this->item['p_diamond_market_cost'] = $this->item['diamond_market_cost'];
            $this->item['p_diamond_weight'] = $this->item['diamond_weight'];
            $this->item['p_labor_cost'] = $this->item['labor'];
        }

        if (empty($this->item['title']))
            $this->item['title'] = $this->generateTitle();

        $this->item['p_newprice'] = $this->newprice;

        $this->generateKeywordDescription();
        $this->item['category_id'] = $this->category_selected_id;
        //dd($this->item);

        if ($this->productId && $this->is_duplicate==0) {
            $product = Product::find($this->productId);

            if ($this->item['p_qty'] == 0) {
                $product->update($this->item);
                eBayEndItem::dispatch([$this->productId]);
            } else {
                $productInInvoice = \DB::table('order_product')
                    ->where('product_id', $this->productId)->first();

                if (isset($productInInvoice)) {
                    if ($productInInvoice->qty == 1) {
                        return 0;
                    }
                }
                $product->update($this->item);
                $this->postToEbay($product);
            }
        } elseif ($this->is_duplicate) {
            $this->item['id'] = "";

            $this->item['created_at'] = Carbon::now();
            $product = Product::create($this->item);
            $this->postToEbay($product);
        } else {
            $product = Product::create($this->item);
            $this->postToEbay($product);
        }

        if ($this->thumbnails) {

            foreach ($this->thumbnails as $index => $image) {
                $title = Str::slug($this->item['title']);
                $str = $this->generateRandomString(10);
                $filename = $title ."-$str.jpg";

                if (!$image['id']) {
                    $image['path']->storeAs('images', $filename ,'public');
                    $imageLocation = base_path()."/storage/app/public/images/";
                    File::move($imageLocation.$filename, public_path("/images/$filename"));

                    $this->adjustImage($filename);

                    $new_image = Image::create([
                        'title' => $title,
                        'location' => $filename,
                        'position' => $this->item['position'][$index]
                    ]);

                    $product->images()->attach($new_image->id);
                } elseif ($this->is_duplicate) {

                    $new_image = Image::create([
                        'title' => $title,
                        'location' => str_replace('/images/thumbs/','',$image['path']),
                        'position' => $this->item['position'][$index]
                    ]);
                    $product->images()->attach($new_image->id);
                } else {
                    Image::where('id',$image['id'])->update([
                        'position' => $this->item['position'][$index]
                    ]);
                }
            }
        }

        AIProductDescription::dispatch($product)->delay(now());
        // $product->update(['keyword_build' => $keyword_build]);
        return $product->id;
    }

    public function postToEbay($product) {
        if (is_numeric($product)) {
            $product = Product::find($product);
            request()->session()->flash('message', "Product submitted to eBay.");
        }

        if ($product->categories->category_name != "Rolex" && $product->p_newprice > 100
            && count($product->images)> 0 && $product->p_status == 0) {
                $listing = EbayListing::where('product_id',$product->id)->first();

                if (!$listing)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]])->delay(now()->addMinutes(2));
                elseif ($listing->listitem == null)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]])->delay(now()->addMinutes(2));
        }
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function adjustImage($filename) {
        $folderNameThumb = "thumbs";
        if (!file_exists(base_path()."/public/images/thumbs/")) {
            mkdir(base_path()."/public/images/thumbs/");
        }

        $imagelocation = base_path()."/public/images/$filename" ;
        $newimagelocation = base_path()."/public/images/thumbs/".$filename ;

        list($width, $height, $type, $attr) = getimagesize($imagelocation);
        $img = new Imagick($imagelocation);
        $img->setImageBackgroundColor('#ffffff');
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $img = $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        // if ($width > 450) {

            //Imagick::open($imagelocation)->thumb(450, 450)->saveTo($newimagelocation);
            //return response()->json($newimagelocation);
            $img->setImageFormat ("jpg");
            $img->thumbnailImage(450, 450,true,true);
            $img->writeImage($newimagelocation);
        // } else {
        //     if ($folderNameThumb) {
        //         $img->writeImage($newimagelocation);
        //     }
        // }

    }

    protected function generateKeywordDescription() {
        $reference = "";
        if (isset($this->item['p_reference'])) {
            $reference = $this->item['p_reference'];
        }
        $condition = Conditions()->get($this->item['p_condition']);
        $gender = Gender()->get($this->item['p_gender']);
        $material='';
        if ($this->item['p_material']!=0){
            if ($this->groupId == 1)
                $material = MetalMaterial()->get($this->item['p_material']);
            else
                $material = Materials()->get($this->item['p_material']);
            $material=strtolower($material).' bezel, ';
        }

        if ($condition == "Unworn")
            $condition = "New / Unworn";

        $strap='';$model='';$keyword_build='';
        $casesize='';
        $serial = '';

        if (isset($this->item['p_casesize']))
            $casesize=str_replace(' ', '', $this->item['p_casesize']);

        if ($this->groupId==0) {
            $model = str_replace('-', '', $this->item['p_model']);
            $strap = Strap()->get($this->item['p_strap']);
            $strap=strtolower($strap) . ' strap, ';

            $keyword_build=$condition. ' '. $gender.' '.$this->category_selected_text.' '
                .$model. ' ' . $casesize . ' '
                . $reference. ' '
                . $strap . $material
                . 'on '.strtolower($this->item['p_color']).' face watch.';

        } elseif ($this->groupId == 1) {
            $keyword_build=$condition. ' '. $gender.' '.$this->category_selected_text.' '
                .$model. ' ' . $casesize . ' '
                . $reference. ' '
                . $material;
        }

        $this->item['keyword_build'] = $keyword_build;
    }

    public function mount() {
        $this->item['p_qty'] = 1;
        $this->custom_columns = getCustomColumns();
        $this->categories = Category::orderBy('category_name','asc')->get();
        $this->loggedInUser = auth()->id();
    }

    protected function RolexYearBySerial($serial,$year) {
        $serial_code = '';

        $condition = $this->item['p_condition'];
        if ($this->category_selected_id == 1 && ($condition == 3 || $condition == 4)) { // Rolex
            if (ord($serial[0]) >= 65 && ord($serial[0]) <= 90) {
                for ($i=1; $i<strlen($serial);$i++) {
                    if (ord($serial[$i]) >= 65 && ord($serial[$i]) <= 90) {
                        $serial_code = 'Scrambled Serial';
                        break;
                    }
                }
                if (!$serial_code)
                    $serial_code = $serial[0].' Serial';

            } elseif (ord($serial[0]) >= 48 && ord($serial[0]) <= 57) {
                for ($i=0; $i<strlen($serial);$i++) {
                    if (ord($serial[$i]) >= 65 && ord($serial[$i]) <= 90) {
                        $serial_code = 'Scrambled Serial';
                        break;
                    }
                }

                if (!$serial_code)
                    if (strlen($serial)>=7)
                        $serial_code = 'Million Serial';
                    else $serial_code = 'Thousand Serial';
            }
        }

        if (!$year && $this->category_selected_id == 1) {
            $years = [
                2011 => "Scrambled",2010=>"G",2009=>"V",
                2008=>"M",2007=>"Z",2001=>"K",
                2006=>"Z",2005=>"D",2004=>"F",2003=>"F",
                2002=>"Y",2000=>"P",1999=>"A",1998=>"U",
                1997=>"U",1996=>"T",1995=>"W",1994=>"S",
                1993=>"S",1992=>"C",1991=>"X",
                1990=>"E",1989=>"L",1988=>"R"
            ];

            $definition = substr($serial_code,0,strpos($serial_code," "));

            $key = array_search($definition,$years);

            $year = "";
            if (false !== $key || $definition == "N" || $definition == "Z") {
                if ($key == 2011)
                    $year = "2011-Present";
                elseif ($definition == "F")
                    $year = "2003-2005";
                elseif ($definition == "N")
                    $year = "1991";
                elseif ($definition == "Z")
                    $year = "2006-2007";
                else $year = $key;
            }
        }

        return ["serial"=>$serial_code,"year"=>$year];
    }

    public function render()
    {
        return view('livewire.product-item');
    }
}
