<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BugReport extends JsonResource
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
            'level' => $this->level,
            'description' => $this->description,
            'state' => $this->state,
            'create_user_id' => $this->create_user_id,
            'create_user' => $this->create_user,
            'done_user_id' => $this->done_user_id,
            'done_user' => $this->done_user,
            'done_at' => $this->done_at,
            'created_at' => $this->created_at,
        ];
    }
}
