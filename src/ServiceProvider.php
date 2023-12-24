<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel;

use GeminiAPI\Client;
use GeminiAPI\ClientInterface;
use GeminiAPI\Laravel\Contracts\GeminiContract;
use GeminiAPI\Laravel\Contracts\HttpClientContract;
use GeminiAPI\Laravel\Exceptions\InvalidArgumentException;
use GeminiAPI\Laravel\Exceptions\MissingApiKey;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;

/**
 * @internal
 */
final class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/gemini.php', 'gemini',
        );

        $this->app->singleton(
            ClientInterface::class,
            static function (Container $container): ClientInterface {
                /** @var Repository $config */
                $config = $container->get('config');

                $apiKey = $config->get('gemini.api_key');
                if (! is_string($apiKey)) {
                    throw MissingApiKey::create();
                }

                $baseUrl = $config->get('gemini.base_url');
                if (isset($baseUrl) && ! is_string($baseUrl)) {
                    throw new InvalidArgumentException('The Gemini API Base URL is invalid.');
                }

                try {
                    /** @var HttpClientContract|PsrHttpClientInterface|null $httpClient */
                    $httpClient = $container->has(HttpClientContract::class)
                        ? $container->get(HttpClientContract::class)
                        : $container->get(PsrHttpClientInterface::class);
                } catch (NotFoundExceptionInterface) {
                    $httpClient = null;
                }

                $client = new Client($apiKey, $httpClient);

                if (! empty($baseUrl)) {
                    $client = $client->withBaseUrl($baseUrl);
                }

                return $client;
            }
        );
        $this->app->singleton(GeminiContract::class, Gemini::class);
        $this->app->alias(GeminiContract::class, 'gemini');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/gemini.php' => $this->app->configPath('gemini.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Client::class,
            ClientInterface::class,
            Gemini::class,
            GeminiContract::class,
            'gemini',
        ];
    }
}
