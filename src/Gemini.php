<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel;

use GeminiAPI\ClientInterface;
use GeminiAPI\Enums\MimeType;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Laravel\Contracts\GeminiContract;
use GeminiAPI\Laravel\Exceptions\InvalidArgumentException;
use GeminiAPI\Laravel\Exceptions\InvalidMimeType;
use GeminiAPI\Resources\Model;
use GeminiAPI\Resources\Parts\ImagePart;
use GeminiAPI\Resources\Parts\TextPart;
use Psr\Http\Client\ClientExceptionInterface;

class Gemini implements GeminiContract
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function generateText(string $prompt): string
    {
        $response = $this->client
            ->generativeModel(ModelName::GeminiPro)
            ->generateContent(
                new TextPart($prompt),
            );

        return $response->text();
    }

    /**
     * Generates a text based on the given image file.
     * You can also provide a prompt.
     *
     * The image type must be one of the types below
     * * image/png
     * * image/jpeg
     * * image/heic
     * * image/heif
     * * image/webp
     *
     * @throws ClientExceptionInterface
     * @throws InvalidMimeType
     */
    public function generateTextUsingImageFile(
        string $imageType,
        string $imagePath,
        string $prompt = '',
    ): string {
        if (! is_file($imagePath) || ! is_readable($imagePath)) {
            throw new InvalidArgumentException(
                sprintf('The "%s" file does not exist or is not readable.', $imagePath),
            );
        }

        $contents = file_get_contents($imagePath);
        if ($contents === false) {
            throw new InvalidArgumentException(
                sprintf('Cannot read contents of the "%s" file', $imagePath),
            );
        }

        $image = base64_encode($contents);

        return $this->generateTextUsingImage($imageType, $image, $prompt);
    }

    /**
     * Generates a text based on the given image.
     * The image data must be a base64 encoded string,
     *
     * You can also provide a prompt.
     *
     * The image type must be one of the types below
     * * image/png
     * * image/jpeg
     * * image/heic
     * * image/heif
     * * image/webp
     *
     * @throws ClientExceptionInterface
     * @throws InvalidMimeType
     */
    public function generateTextUsingImage(
        string $imageType,
        string $image,
        string $prompt = '',
    ): string {
        $mimeType = MimeType::tryFrom($imageType);
        if (is_null($mimeType)) {
            throw InvalidMimeType::create($imageType);
        }

        $parts = [
            new ImagePart($mimeType, $image),
        ];

        if (! empty($prompt)) {
            $parts[] = new TextPart($prompt);
        }

        $response = $this->client
            ->generativeModel(ModelName::GeminiProVision)
            ->generateContent(...$parts);

        return $response->text();
    }

    public function startChat(): ChatSession
    {
        $chatSession = $this->client
            ->generativeModel(ModelName::GeminiPro)
            ->startChat();

        return new ChatSession($chatSession);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function countTokens(string $prompt): int
    {
        $response = $this->client
            ->generativeModel(ModelName::GeminiPro)
            ->countTokens(
                new TextPart($prompt),
            );

        return $response->totalTokens;
    }

    /**
     * @return Model[]
     */
    public function listModels(): array
    {
        $response = $this->client->listModels();

        return $response->models;
    }
}
