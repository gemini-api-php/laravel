<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel;

use GeminiAPI\ClientInterface;
use GeminiAPI\Enums\MimeType;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Enums\Role;
use GeminiAPI\Laravel\Contracts\GeminiContract;
use GeminiAPI\Laravel\Exceptions\InvalidArgumentException;
use GeminiAPI\Laravel\Exceptions\InvalidMimeType;
use GeminiAPI\Resources\Content;
use GeminiAPI\Resources\Model;
use GeminiAPI\Resources\Parts\ImagePart;
use GeminiAPI\Resources\Parts\TextPart;
use Psr\Http\Client\ClientExceptionInterface;

use function array_map;
use function base64_encode;
use function file_get_contents;
use function in_array;
use function is_file;
use function is_null;
use function is_readable;
use function is_string;
use function sprintf;

class Gemini implements GeminiContract
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return float[]
     *
     * @throws ClientExceptionInterface
     */
    public function embedText(string $prompt, ?string $title = null): array
    {
        $model = $this->client->embeddingModel(ModelName::Embedding);

        $response = $title
            ? $model->embedContentWithTitle($title, new TextPart($prompt))
            : $model->embedContent(new TextPart($prompt));

        return $response->embedding->values;
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
        ModelName $model = ModelName::GeminiProVision
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
            ->generativeModel($model)
            ->generateContent(...$parts);

        return $response->text();
    }

    /**
     * @param array<int, array{
     *   message: string,
     *   role: string,
     * }> $history
     *
     * @throws InvalidArgumentException
     */
    public function startChat(array $history = []): ChatSession
    {
        $chatSession = $this->client
            ->generativeModel(ModelName::GeminiPro)
            ->startChat();

        if (! empty($history)) {
            $contents = array_map(
                static function (array $message): Content {
                    if (empty($message['message']) || empty($message['role'])) {
                        throw new InvalidArgumentException('Invalid message in the chat history');
                    }

                    if (! is_string($message['message']) || ! in_array($message['role'], ['user', 'model'], true)) {
                        throw new InvalidArgumentException('Invalid message in the chat history');
                    }

                    return Content::text($message['message'], Role::from($message['role']));
                },
                $history,
            );
            $chatSession = $chatSession->withHistory($contents);
        }

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

    public function client(): ClientInterface
    {
        return $this->client;
    }
}
