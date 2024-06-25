<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\Stdlib\StringUtils;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Exception\RuntimeException;
use Throwable;

use function is_string;

/**
 * @psalm-type OptionsArgument = array{
 *     min?: int,
 *     max?: int|null,
 *     encoding?: string,
 * }
 */
final class StringLength extends AbstractValidator
{
    public const INVALID   = 'stringLengthInvalid';
    public const TOO_SHORT = 'stringLengthTooShort';
    public const TOO_LONG  = 'stringLengthTooLong';

    /** @var array<string, string> */
    protected array $messageTemplates = [
        self::INVALID   => 'Invalid type given. String expected',
        self::TOO_SHORT => 'The input is less than %min% characters long',
        self::TOO_LONG  => 'The input is more than %max% characters long',
    ];

    /** @var array<string, string> */
    protected array $messageVariables = [
        'min'    => 'min',
        'max'    => 'max',
        'length' => 'length',
    ];

    protected readonly int $min;
    protected readonly int|null $max;
    private readonly string $encoding;
    protected ?int $length = null;

    /** @var array<string, mixed> */
    protected array $options = [
        'min'      => 0, // Minimum length
        'max'      => null, // Maximum length, null if there is no length limitation
        'encoding' => 'UTF-8', // Encoding to use
        'length'   => 0, // Actual length
    ];

    /**
     * Sets validator options
     *
     * @param OptionsArgument $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->min      = $options['min'] ?? 0;
        $this->max      = $options['max'] ?? null;
        $this->encoding = $options['encoding'] ?? 'utf-8';

        if ($this->max !== null && $this->max < $this->min) {
            throw new InvalidArgumentException(
                "The maximum must be greater than or equal to the minimum length, but {$this->max} < {$this->min}"
            );
        }
    }

    /**
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     */
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        $wrapper   = StringUtils::getWrapper($this->encoding);
        $exception = null;
        try {
            $length = $wrapper->strlen($value);
        } catch (Throwable $exception) {
            $length = false;
        }

        if ($length === false) {
            throw new RuntimeException('Failed to detect string length', 0, $exception);
        }

        $this->length = $length;

        if ($this->length < $this->min) {
            $this->error(self::TOO_SHORT);

            return false;
        }

        if ($this->max !== null && $this->length > $this->max) {
            $this->error(self::TOO_LONG);

            return false;
        }

        return true;
    }
}
