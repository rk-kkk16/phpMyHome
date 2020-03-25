<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImpEval extends JsonResource
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
            'score' => $this->score,
            'user_id' => $this->user_id,
            'user_name' => $this->user->user_name,
            'created_at' => $this->created_at,
        ];
    }
}
