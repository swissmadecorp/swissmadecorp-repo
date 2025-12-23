<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['category_name'];

    public function products() {
        return $this->hasMany(Product::class);
    }

    public static function categories() {
        return static::selectRaw("*, lower(replace(category_name,' ','-')) as category_url")
            ->get();
    }

    public static function sidebar() {
        
        return static::whereHas('products', function ($query){
            $query->where('p_qty','>',0);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderByRaw('category_name="Rolex" desc, category_name')->get();
    }

    // public static function megaMenu() {
    //     return static::whereHas('products',function($query) {
    //         $query->where('p_qty','>',0);
    //         $query->whereIn('p_status',array(0,1,2,5,6));
    //     })->orderBy('category_name')->get();
    // }
}
