<?php

declare(strict_types=1);

namespace GeminiAPI\Laravel\Exceptions;

use GeminiAPI\Enums\MimeType;

use function array_map;
use function implode;

/**
 * @internal
 */
class InvalidMimeType extends InvalidArgumentException
{
    public static function create(string $mimeType): self
    {
        $supportedTypes = array_map(
            static fn (MimeType $mimeType): string => $mimeType->value,
            MimeType::cases(),
        );

        return new self(
            sprintf(
                'The Gemini API does not support the image type [%s]. Supported image types are [%s]',
                $mimeType,
                implode(',', $supportedTypes)
            ),
        );
    }
}
