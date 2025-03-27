<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel\Facades;

use GeminiAPI\ClientInterface;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Laravel\ChatSession;
use GeminiAPI\Resources\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @param  array<int, array{message: string, role: string}>  $history
 *
 * @method static int countTokens(string $prompt)
 * @method static float[] embedText(string $prompt)
 * @method static string generateText(string $prompt)
 * @method static string generateTextUsingImage(string $imageType, string $image, string $prompt = '', ModelName $model = ModelName::GeminiProVision)
 * @method static string generateTextUsingImageFile(string $imageType, string $imagePath, string $prompt = '')
 * @method static ChatSession startChat(array $history)
 * @method static Model[] listModels()
 * @method static ClientInterface client()
 */
class Gemini extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'gemini';
    }
}
