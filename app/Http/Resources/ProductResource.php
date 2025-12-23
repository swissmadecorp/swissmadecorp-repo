<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $images = array();
        $imagesWithPath = array();

        foreach ($this->images as $image) {
            $images[] = array("https://swissmadecorp.com/public/images/thumbs/".$image->location);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'model' => $this->p_model,
            'category' => $this->categories->category_name,
            'reference' => $this->p_reference,
            'box' => $this->p_box == 0 ? 'No' : 'Yes',
            'papers' => $this->p_papers == 0 ? 'No' : 'Yes',
            'material' => Materials()->get($this->p_material),
            'condition' => Conditions()->get($this->p_condition),
            'retail' => $this->p_retail,
            'price' => $this->p_newprice,
            'status' => Status()->get($this->p_status),
            'platform' => Platforms()->get($this->platform),
            "movement" => Movement()->get($this->movement),
            "case_size" => $this->p_casesize,
            'gender' => $this->p_gender,
            'strap' => Strap()->get($this->p_strap),
            'slug' => $this->slug,
            "images" => $images,
            //"mainimage" => "https://swissmadecorp.com/public/images/".$image->location
            //'imagesWithPath' => $imagesWithPath
        ];
    }
}
