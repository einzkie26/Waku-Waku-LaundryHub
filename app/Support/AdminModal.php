<?php

namespace App\Support;

use Illuminate\Http\Request;

class AdminModal
{
    public static function pageUrl(Request $request): string
    {
        $query = $request->query();
        unset($query['add'], $query['new'], $query['settings']);

        return $request->getBaseUrl().$request->getPathInfo().($query ? '?'.http_build_query($query) : '');
    }

    public static function returnUrl(Request $request, string $fallbackRoute): string
    {
        $target = $request->input('_return_to');
        if (is_string($target) && str_starts_with($target, '/admin/')) {
            $path = rawurldecode(parse_url($target, PHP_URL_PATH) ?: '');
            if (!str_contains($target, '\\') && !str_contains($path, '\\')
                && !preg_match('/[\x00-\x1F\x7F]/', $target)
                && !preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $path)) {
                return $target;
            }
        }

        return route($fallbackRoute);
    }
}
