<?php

namespace Botble\AdminTools\Http\Controllers;

use Botble\AdminTools\Http\Requests\AdminToolsCacheRequest;
use Botble\AdminTools\Services\AdminToolsCacheService;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Throwable;

class AdminToolsCacheController extends BaseController
{
    public function clear(AdminToolsCacheRequest $request, AdminToolsCacheService $cacheService): BaseHttpResponse
    {
        try {
            $message = $cacheService->clear($request->input('type'));
        } catch (Throwable $exception) {
            report($exception);

            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }

        return $this
            ->httpResponse()
            ->setMessage($message)
            ->setData([
                'cache_size' => $cacheService->cacheSize(),
                'formatted_cache_size' => $cacheService->formattedCacheSize(),
            ]);
    }
}
