<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DoubleTickController extends Controller
{
    public function sendText(Request $request)
    {
        $response = Http::withHeaders([
            'Authorization' => config('services.doubletick.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://public.doubletick.io/whatsapp/message/text', [
            'to' => $request->input('to', '918264609578'),
            'from' => config('services.doubletick.sender'),
            'messageId' => Str::uuid()->toString(),
            'content' => [
                'text' => $request->input('text', 'Hello, this is a sample message from DoubleTick!'),
            ],
        ]);

        return $response->json();
    }

    public function sendSimpleTemplate(Request $request)
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'Authorization' => config('services.doubletick.api_key'),
        ])->post('https://public.doubletick.io/whatsapp/message/template', [
            'messages' => [
                [
                    'to' => $request->input('to', '918264609578'),
                    'content' => [
                        'templateName' => $request->input('templateName', 'new_chat_v1'),
                        'language' => $request->input('language', 'en'),
                        'templateData' => [
                            'body' => [
                                'placeholders' => $request->input('placeholders', ['1']),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $response->json();
    }
}
