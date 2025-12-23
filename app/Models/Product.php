<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Jobs\eBayEndItem;
//use Laravel\Scout\Searchable;

class Product extends Model
{
    public $timestamps = true;
    
    use SoftDeletes, FullTextSearch;
    //use Searchable;

    protected $searchable = [
        'keyword_build'
    ];

    protected $guarded = ['filename','category','category_selected','p_category','printAfterSave','related'];
    protected $dates = ['deleted_at'];
    
    public function categories() {
        return $this->belongsTo(Category::class,'category_id','id'); 
    }

    public function theshow() {
        return $this->hasOne(TheShow::class,"product_id","id");
    }

    public function images() {
        //return $this->hasMany(ProductImage::class,'product_id','id')->orderBy('position');
        return $this->belongsToMany(Image::class,'product_image','product_id','image_id')
            ->orderBy('position','asc');
    }

    public function repair(){
        //return $this->hasManyThrough(RepairProduct::class,Repair::class,'id','job_id','id','product_id');
        return $this->belongsToMany(Repair::class)->withPivot('id','job','serial','amount','cost')->orderBy('created_at','desc');
    }

    public function listings() {
        return $this->hasOne(EbayListing::class,'product_id','id');
    }

    public function orders() {
        return $this->belongsToMany(Order::class)->withPivot('product_id');
    }

    public function customers() {
        return $this->belongsToMany(Customer::class)->withPivot('id');
    }

    public function scopeOrderByQtyID($query) {
        return $query->orderBy('p_qty','desc')->orderBy('id','desc');
    }

    public function setPStatusAttribute($value) {
        if ($value==10) {
            $this->attributes['p_qty'] = 0;
            eBayEndItem::dispatch(["products"=>$this->attributes['id']]);
        }
        $this->attributes['p_status'] = $value;
    }
    
    public function scopePriceGreaterThanZero($query) {
        return $query->where('p_newprice','>','0');
        // return $query->cursor()->filter(function($product) {
        //     return $product->p_qty > 0;
        // });
    }

    public function scopeQtyGreaterThanZero($query) {
        return $query->cursor()->filter(function($product) {
            return $product->p_qty > 0;
        });
    }

    // public function setPRepairCostAttribute($value) {
    //     if ($value) {
    //         //$this->attributes['p_price'] = $this->attributes['p_price'] + $value;
    //         $this->attributes['p_repair_cost'] = $value;
    //     } else $this->attributes['p_repair_cost']=0;
    // }

    // public function setWebPriceAttribute($value) {
    //     $this->attributes['web_price'] = ceil($value+($value*CCMargin()));
    // }

    public function setPCasesizeAttribute($value) {
        if ($value)
            $this->attributes['p_casesize'] = trim(str_replace('mm','',$value)).' mm';
    }

    public function scopeQtyEqualToZero($query) {
        return $query->cursor()->filter(function($product) {
            return $product->p_qty <= 0;
        });
    }

    protected function makeAllSearchableUsing($query) {
        //return $query->with('images');
    }

    public function scopeByModel(Category $category, Builder $query) {
        return $query->where('category_name', function($q) use
            ($p_model) {
                $q->where('p_model', $category->category_name);
            });
    }

    public function scopeSByModel($query, $criteria) {
        return $query->where('p_model','like','%'.$criteria.'%');
    }

    public function scopeByCategoryName($query, $criteria) {
        return $query->where('category_name','like','%'.$criteria.'%');
    }

    public function searchableAs()
    {
        return 'products';
    }

    public function scopeSearch($query, $term)
    {
        $columns = implode(',',$this->searchable);
        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)" , $this->fullTextWildcards($term));
        //\Log::debug($query->toSql());
        return $query;
    }
    
    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = \App\Models\DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();
        
        return $discountRule;
    }

    private function discountRule2() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = \App\Models\DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->get();
        
        return $discountRule;
    }

    public function setWaterResistanceAttribute($value) {
        if (strpos($value, "m /") !== false) {
            $meters = str_replace("m /"," meters /",$value);
            $meters = str_replace("ft"," feet",$meters);
            $this->attributes['water_resistance'] = $meters;
        } else $this->attributes['water_resistance'] = $value;
    }

    private function discountProducts($discount) {
        //$rules = $this->discountRule2();
        $products = array();

        foreach ($discount as $rule) {
            foreach (unserialize($rule->product) as $product) {
                $products[] = array('item'=>$product,'action'=>$rule->action,'amount'=>$rule->amount);
            }
        }

        return $products;
    }

    public function toSearchableArray()
    {
        //$array = $this->toArray();

        if ($this->p_qty > 0 ) {
            $website =  \URL::to('/')  . '/public/images/';
            $img = $this->images->first();
            if (count($this->images)) {
                $image = $this->images->first();
                if (strpos($image->location,'snapshot') > 0 )
                $path=$website."no-image.jpg";
                else $path = $website.'thumbs/'.$image->location;
            } else {
                $path=$website."no-image.jpg";
            }
            
            $webprice=0;$sale='';
            $discount = $this->discountRule2();
            
            if ($discount) {
                $productDiscount=$this->discountProducts($discount);
                $productIndx = array_search($this->id,array_column($productDiscount,'item'));

                if ($productIndx !== false) {
                    $product = $productDiscount[$productIndx];
                    
                    if ($discount && $product['action'] == 4) {
                        $webprice = ceil($this->p_newprice+($this->p_newprice*CCMargin())); 
                        $webprice = ceil($webprice - ($webprice * ($product['amount']/100)));
                        $sale='sale';
                    } elseif ($discount && $product['action'] == 5 && !empty($product) && in_array($this->id,array_column($productDiscount,'item'))) {
                        $webprice = ceil($this->p_newprice+($this->p_newprice*CCMargin()));
                        $webprice = ceil($webprice - ($webprice * ($product['amount']/100)));
                        $sale='sale';
                    } else {
                        $webprice = ceil($this->p_newprice+($this->p_newprice*CCMargin()));
                    }
                } else {
                    $webprice = ceil($this->p_newprice+($this->p_newprice*CCMargin()));
                }
            } else {
                //dd('no discount');
                $webprice = ceil($this->p_newprice+($this->p_newprice*CCMargin()));
            }

            if ($this->p_qty==0 && $this->p_status == 7) { // status 7
                $status = 'SOLD';
            } elseif ( $this->p_status==3) {
                $status = 'Available';
            } else {
                $status = Status()->get($this->p_status );
            }

            $array = array('id'=>$this->id, 
                'updated_at' => $this->updated_at,
                'status' => $status,
                'sale' => $sale,
                'category' => $this->categories->category_name,
                'model'=>$this->p_model,
                'condition' => Conditions()->get($this->p_condition),
                'reference'=>$this->p_reference,
                'case_size'=>$this->p_casesize,
                'slug'=>($this->p_condition == 3) ? '/certified-pre-owned-watches/'.$this->slug : '/'. $this->slug,
                'gender' => $this->p_gender,
                'details' => $this->title,
                'strap' => Strap()->get($this->p_strap),
                'bezel_features' => $this->bezel_features,
                'material' => Materials()->get($this->p_material),
                'price' => $webprice==0 ? 'Call us' : '$'.number_format($webprice,2),
                'image' => $path
            );
        }
        // Customize array...

        return $array;
    }

        /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    // public function getScoutKey()
    // {
    //     return $this->id;
    // }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    // public function getScoutKeyName()
    // {
    //     return 'id';
    // }

    // public function shouldBeSearchable()
    // {
    //     $m = 1;
    //     if ($this->p_status==3 || $this->p_status == 4 || $this->p_status == 7)
    //         $m = 0;

    //     return $this->p_qty > 0 && $this->group_id==0 && $m==1;
    // }

}
