<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Mustbuy extends JsonResource
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
            'item_name' => $this->item_name,
            'quantity' => $this->quantity,
            'level' => $this->level,
            'memo' => $this->memo,
            'state' => $this->state,
            'create_user_id' => $this->create_user_id,
            'buy_user_id' => $this->buy_user_id,
            'buy_at' => $this->buy_at,
            'edited_at' => $this->edited_at,
            'edited_user_id' => $this->edited_user_id,
            'created_at' => $this->created_at,
        ];
    }
}
