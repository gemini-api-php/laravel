<?php

declare(strict_types=1);

use GeminiAPI\Client;
use GeminiAPI\ClientInterface;
use GeminiAPI\Laravel\Contracts\GeminiContract;
use GeminiAPI\Laravel\Exceptions\InvalidArgumentException;
use GeminiAPI\Laravel\Exceptions\MissingApiKey;
use GeminiAPI\Laravel\Gemini;
use GeminiAPI\Laravel\ServiceProvider;
use Illuminate\Config\Repository;

it('binds the client interface on the container', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'gemini' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    expect($app->get(ClientInterface::class))->toBeInstanceOf(Client::class);
});

it('binds the client on the container as singleton', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'gemini' => [
            'api_key' => 'test',
        ],
    ]));

    (new ServiceProvider($app))->register();

    $client = $app->get(ClientInterface::class);

    expect($app->get(ClientInterface::class))->toBe($client);
});

it('requires an api key', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([]));

    (new ServiceProvider($app))->register();
})->throws(
    MissingApiKey::class,
    'The Gemini API Key is missing. Please publish the [gemini.php] configuration file and set the [api_key].',
);

it('validates base url', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'gemini' => [
            'api_key' => 'test',
            'base_url' => [], // not a string
        ],
    ]));

    (new ServiceProvider($app))->register();
})->throws(
    InvalidArgumentException::class,
    'The Gemini API Base URL is invalid.',
);

it('allows base url', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'gemini' => [
            'api_key' => 'test',
            'base_url' => 'https://localhost',
        ],
    ]));

    (new ServiceProvider($app))->register();

    $client = $app->get(ClientInterface::class);

    expect($app->get(ClientInterface::class))->toBe($client);
});

it('returns provided services', function () {
    $app = app();

    $provides = (new ServiceProvider($app))->provides();

    expect($provides)->toBe([
        Client::class,
        ClientInterface::class,
        Gemini::class,
        GeminiContract::class,
        'gemini',
    ]);
});
