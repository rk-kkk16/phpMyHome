<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Imagepost extends JsonResource
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
            'title' => $this->title,
            'user_id' => $this->user_id,
            'user' => $this->user,
            'imp_category_id' => $this->imp_category_id,
            'tags' => $this->tags,
            'evals' => $this->evals,
            'created_at' => $this->created_at,
        ];
    }
}
