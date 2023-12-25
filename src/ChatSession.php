<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel;

use GeminiAPI\ChatSession as ApiChatSession;
use GeminiAPI\Resources\Content;
use GeminiAPI\Resources\Parts\TextPart;
use Psr\Http\Client\ClientExceptionInterface;

class ChatSession
{
    public function __construct(
        private readonly ApiChatSession $chatSession,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function sendMessage(string $text): string
    {
        return $this->chatSession
            ->sendMessage(new TextPart($text))
            ->text();
    }

    /**
     * @return array<int, array{
     *     message: string,
     *     role: string,
     * }>
     */
    public function history(): array
    {
        return array_map(
            static fn (Content $content): array => [
                'message' => $content->parts[0] instanceof TextPart ? $content->parts[0]->text : '',
                'role' => $content->role->value,
            ],
            $this->chatSession->history(),
        );
    }
}
