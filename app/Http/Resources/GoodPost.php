<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodPost extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => $this->user,
            'to_user_id' => $this->to_user_id,
            'toUser' => $this->toUser,
            'body' => $this->body,
            'total_good' => $this->total_good,
            'created_at' => $this->created_at,
        ];
    }
}
