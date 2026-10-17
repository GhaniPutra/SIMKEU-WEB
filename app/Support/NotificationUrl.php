<?php

namespace App\Support;

class NotificationUrl
{
    public static function normalize(array $data): array
    {
        $url = $data['url'] ?? null;
        $path = is_string($url) ? parse_url($url, PHP_URL_PATH) : null;

        if (is_string($path) && preg_match('~^/employees/(\d+)/stock/?$~', $path, $matches)) {
            $data['url'] = route('employees.manage', [
                'employee' => $matches[1],
                'period' => 'all',
                'tab' => 'stock',
            ], false);
        }

        return $data;
    }
}
