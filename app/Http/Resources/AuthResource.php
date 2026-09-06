<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    protected ?string $token;

    public function __construct($resource, ?string $token = null)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'user' => new UserResource($this->resource),
        ];

        if ($this->token !== null) {
            $data['token'] = $this->token;
            $data['token_type'] = 'Bearer';
        }

        return $data;
    }
}
