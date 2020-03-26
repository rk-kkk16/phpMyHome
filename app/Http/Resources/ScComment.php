<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScComment extends JsonResource
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
            'scrap_entry_id' => $this->scrap_entry_id,
            'user_id' => $this->user_id,
            'user' => $this->user,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
        ];
    }
}
