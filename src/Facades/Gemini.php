<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel\Facades;

use GeminiAPI\Laravel\ChatSession;
use GeminiAPI\Resources\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static int countTokens(string $prompt)
 * @method static string generateText(string $prompt)
 * @method static string generateTextUsingImage(string $imageType, string $image, string $prompt = '')
 * @method static string generateTextUsingImageFile(string $imageType, string $imagePath, string $prompt = '')
 * @method static ChatSession startChat()
 * @method static Model[] listModels()
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
