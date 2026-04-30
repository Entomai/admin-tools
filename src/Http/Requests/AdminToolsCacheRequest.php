<?php

namespace Botble\AdminTools\Http\Requests;

use Botble\AdminTools\Services\AdminToolsCacheService;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AdminToolsCacheRequest extends Request
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(app(AdminToolsCacheService::class)->types())],
        ];
    }
}
