<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MessageController extends Controller
{
    /**
     * Accepts a batch: { "items": [ {type, app_package, app_name, sender, title, body, received_at}, ... ] }
     * received_at is epoch milliseconds (what Android gives us).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.type' => ['required', 'in:notification,sms'],
            'items.*.app_package' => ['nullable', 'string', 'max:255'],
            'items.*.app_name' => ['nullable', 'string', 'max:255'],
            'items.*.sender' => ['nullable', 'string', 'max:255'],
            'items.*.title' => ['nullable', 'string', 'max:500'],
            'items.*.body' => ['nullable', 'string', 'max:20000'],
            'items.*.received_at' => ['required', 'integer'],
        ]);

        $device = $request->attributes->get('device');
        $now = now();
        $rows = [];

        foreach ($request->input('items') as $item) {
            $rows[] = [
                'device_id' => $device->id,
                'type' => $item['type'],
                'app_package' => $item['app_package'] ?? null,
                'app_name' => $item['app_name'] ?? null,
                'sender' => $item['sender'] ?? null,
                'title' => $item['title'] ?? null,
                'body' => $item['body'] ?? null,
                'received_at' => Carbon::createFromTimestampMs($item['received_at']),
                'hash' => hash('sha256', implode('|', [
                    $item['type'], $item['app_package'] ?? '', $item['sender'] ?? '',
                    $item['title'] ?? '', $item['body'] ?? '', $item['received_at'],
                ])),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // insertOrIgnore + unique(device_id, hash) => the app can safely retry the same batch
        $stored = Message::insertOrIgnore($rows);

        return response()->json(['received' => count($rows), 'stored' => $stored]);
    }
}
