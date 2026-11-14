<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $devices = Device::withCount('messages')
            ->orderByDesc('last_seen_at')
            ->get();

        $stats = [
            'devices' => $devices->count(),
            'online' => $devices->filter->isOnline()->count(),
            'notifications' => Message::where('type', 'notification')->count(),
            'sms' => Message::where('type', 'sms')->count(),
        ];

        return view('dashboard', compact('devices', 'stats'));
    }

    public function messages(Request $request): View
    {
        $filters = $request->validate([
            'device' => ['nullable', 'integer', 'exists:devices,id'],
            'type' => ['nullable', 'in:notification,sms'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $messages = Message::with('device')
            ->when($filters['device'] ?? null, fn ($query, $id) => $query->where('device_id', $id))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('body', 'like', "%{$term}%")
                        ->orWhere('sender', 'like', "%{$term}%")
                        ->orWhere('app_name', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('received_at')
            ->paginate(50)
            ->withQueryString();

        $devices = Device::orderBy('name')->get();

        return view('messages', compact('messages', 'devices', 'filters'));
    }
}
