<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImpComment extends JsonResource
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
            'imagepost_id' => $this->imagepost_id,
            'comment' => $this->comment,
            'user_id' => $this->user_id,
            'user' => $this->user,
            'created_at' => $this->created_at,
        ];
    }
}
