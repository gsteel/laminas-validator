<?php

declare(strict_types=1);

namespace Laminas\Validator;

use function get_debug_type;
use function is_array;

final class IsArray extends AbstractValidator
{
    public const NOT_ARRAY = 'NotArray';

    /** @var array<string, string> */
    protected array $messageTemplates = [
        self::NOT_ARRAY => 'Expected an array value but %type% provided',
    ];

    /** @var array<string, string|array<string, string>> */
    protected array $messageVariables = [
        'type' => 'type',
    ];

    protected ?string $type = null;

    public function isValid(mixed $value): bool
    {
        if (is_array($value)) {
            return true;
        }

        $this->type = get_debug_type($value);
        $this->error(self::NOT_ARRAY);

        return false;
    }
}
