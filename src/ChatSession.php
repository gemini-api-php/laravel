<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel;

use GeminiAPI\ChatSession as ApiChatSession;
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
}
