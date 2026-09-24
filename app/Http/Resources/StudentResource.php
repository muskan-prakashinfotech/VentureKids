<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'name'              => ( string )$this->name,
            'parent_name'       => ( string )$this->parent_name,
            'parent_email'      => ( string )$this->parent_email,
            'address'           => ( string )$this->address,
        ];
        $user = User::where('id', $this->user_id)->first();
        $data['email'] = $user->email;
        $data['mobile'] = $user->mobile;
        $data['date_of_birth'] = date('d/m/Y',strtotime($user->date_of_birth));
        $data['gender'] = $user->gender;
        return $data;
    }
}
