<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Callback;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\Explode;
use Laminas\Validator\InArray;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Explode
 */
final class ExplodeTest extends TestCase
{
    public function testRaisesExceptionWhenValidatorIsMissing(): void
    {
        $validator = new Explode();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('validator');

        $validator->isValid('foo,bar');
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: mixed,
     *     1: null|string,
     *     2: bool,
     *     3: int,
     *     4: bool,
     *     5: string[],
     *     6: bool
     * }>
     */
    public function getExpectedData(): array
    {
        return [
            //    value              delim break  N  valid  messages                   expects
            ['foo,bar,dev,null', ',', false, 4, true, [], true],
            ['foo,bar,dev,null', ',', true, 1, false, ['X'], false],
            ['foo,bar,dev,null', ',', false, 4, false, ['X'], false],
            ['foo,bar,dev,null', ';', false, 1, true, [], true],
            ['foo;bar,dev;null', ',', false, 2, true, [], true],
            ['foo;bar,dev;null', ',', false, 2, false, ['X'], false],
            ['foo;bar;dev;null', ';', false, 4, true, [], true],
            ['foo', ',', false, 1, true, [], true],
            ['foo', ',', false, 1, false, ['X'], false],
            ['foo', ',', true, 1, false, ['X'], false],
            [['a', 'b'], null, false, 2, true, [], true],
            [['a', 'b'], null, false, 2, false, ['X'], false],
            ['foo', null, false, 1, true, [], true],
            [1, ',', false, 1, true, [], true],
            [null, ',', false, 1, true, [], true],
            [new stdClass(), ',', false, 1, true, [], true],
            [new ArrayObject(['a', 'b']), null, false, 2, true, [], true],
        ];
    }

    /**
     * @dataProvider getExpectedData
     */
    public function testExpectedBehavior(
        mixed $value,
        ?string $delimiter,
        bool $breakOnFirst,
        int $numIsValidCalls,
        bool $isValidReturn,
        array $messages,
        bool $expects
    ): void {
        $mockValidator = $this->createMock(ValidatorInterface::class);
        $mockValidator
            ->expects($this->exactly($numIsValidCalls))
            ->method('isValid')
            ->willReturn($isValidReturn);
        $mockValidator
            ->method('getMessages')
            ->willReturn('X');

        $validator = new Explode([
            'validator'           => $mockValidator,
            'valueDelimiter'      => $delimiter,
            'breakOnFirstFailure' => $breakOnFirst,
        ]);

        self::assertSame($expects, $validator->isValid($value));
        self::assertSame($messages, $validator->getMessages());
    }

    public function testGetMessagesReturnsDefaultValue(): void
    {
        $validator = new Explode();

        self::assertSame([], $validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Explode([]);

        self::assertSame(
            [
                Explode::INVALID,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator = new Explode([]);

        self::assertSame([], $validator->getOption('messageVariables'));
        self::assertSame(array_keys([]), $validator->getMessageVariables());
    }

    public function testSetValidatorAsArray(): void
    {
        $validator = new Explode();
        $validator->setValidator([
            'name'    => 'inarray',
            'options' => [
                'haystack' => ['a', 'b', 'c'],
            ],
        ]);

        /** @var InArray $inArrayValidator */
        $inArrayValidator = $validator->getValidator();

        self::assertInstanceOf(InArray::class, $inArrayValidator);
        self::assertSame(['a', 'b', 'c'], $inArrayValidator->getHaystack());
    }

    public function testSetValidatorMissingName(): void
    {
        $validator = new Explode();

        $this->expectException(RuntimeException::class);

        /** @psalm-suppress InvalidArgument */
        $validator->setValidator([
            'options' => [],
        ]);
    }

    public function testSetValidatorInvalidParam(): void
    {
        $validator = new Explode();

        $this->expectException(RuntimeException::class);

        $validator->setValidator('inarray');
    }

    /**
     * @group Laminas-5796
     */
    public function testGetMessagesMultipleInvalid(): void
    {
        $validator = new Explode([
            'validator'           => new Regex(
                '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/'
            ),
            'valueDelimiter'      => ',',
            'breakOnFirstFailure' => false,
        ]);

        $messages = [
            0 => [
                'regexNotMatch' => 'The input does not match against pattern '
                    . "'/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/'",
            ],
        ];

        self::assertFalse($validator->isValid('api-tools-devteam@zend.com,abc,defghij'));
        self::assertSame($messages, $validator->getMessages());
    }

    /**
     * Assert context is passed to composed validator
     */
    public function testIsValidPassContext(): void
    {
        $context     = 'context';
        $contextSame = false;
        $validator   = new Explode([
            'validator'           => new Callback(static function ($v, $c) use ($context, &$contextSame): bool {
                $contextSame = $context === $c;
                return true;
            }),
            'valueDelimiter'      => ',',
            'breakOnFirstFailure' => false,
        ]);

        self::assertTrue($validator->isValid('a,b,c', $context));
        self::assertTrue($contextSame);
    }
}
