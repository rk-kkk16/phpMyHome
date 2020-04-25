<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScCategory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_name' => $this->category_name,
            'user_id' => $this->user_id,
            'user' => $this->user,
            'is_primary' => $this->is_primary,
            'depth' => $this->depth,
            'parent_category_id' => $this->parent_category_id,
            'childs' => $this->childs,
        ];
    }
}
