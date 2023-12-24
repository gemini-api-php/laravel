<?php

declare(strict_types=1);

use GeminiAPI\Laravel\ChatSession;
use GeminiAPI\Laravel\Facades\Gemini;
use GeminiAPI\Laravel\ServiceProvider;
use Illuminate\Config\Repository;

it('resolves resources', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'gemini' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    Gemini::setFacadeApplication($app);

    $chat = Gemini::startChat();

    expect($chat)->toBeInstanceOf(ChatSession::class);
});
