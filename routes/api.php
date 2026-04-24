<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\SlotService;
use App\Events\SlotUpdated;


Route::post('/esp/update-slot', function (Request $request) {

    $data = $request->all();

    // update slot via service kamu
    $slots = SlotService::update($data);

    // broadcast realtime ke admin
    broadcast(new SlotUpdated($slots));

    return response()->json([
        "success" => true,
        "message" => "Slot updated from ESP",
        "data" => $slots
    ]);
});

Route::get('/esp/get-command', function () {

    $queue = cache()->get('esp_queue', []);

    if (empty($queue)) {
        return response()->json([
            "command" => null,
            "payload" => null,
            "time" => now()->format('H:i:s')
        ]);
    }

    $cmd = array_shift($queue);
    cache()->put('esp_queue', $queue, 60);

    return response()->json([
        "id" => $cmd['id'],
        "command" => $cmd['command'],
        "payload" => $cmd['payload'] ?? null,
        "gate" => $cmd['gate'] ?? null,
        "time" => now()->format('H:i:s')
    ]);
});

Route::post('/esp/push-command', function (Request $request) {

    $queue = cache()->get('esp_queue', []);

    $queue[] = [
        "id" => rand(1000, 9999),
        "command" => $request->command,
        "payload" => $request->payload ?? null,
        "gate" => $request->gate ?? null
    ];

    cache()->put('esp_queue', $queue, 60);

    return response()->json([
        "success" => true,
        "message" => "command queued"
    ]);
});