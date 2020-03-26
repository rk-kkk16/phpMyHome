<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScrapEntry extends JsonResource
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
            'subject' => $this->subject,
            'body' => $this->body,
            'sc_category_id' => $this->sc_category_id,
            'category' => $this->category,
            'user_id' => $this->user_id,
            'user' => $this->user,
            'good_point' => $this->good_point,
            'good_trx' => $this->good_trx,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
