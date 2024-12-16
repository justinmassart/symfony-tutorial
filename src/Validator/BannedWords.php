<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class BannedWords extends Constraint
{
    public function __construct(
        public string $message = 'This contains a banned word : "{{ bannedWord }}"',
        public $bannedWords = [
            'spam',
            'viagra',
            'free',
            'cheap',
            'viagra',
            'scam',
            'clickbait',
            'porn',
            'buy now',
            'sale',
            'hot girls',
            'diet pills',
            'miracle',
            'best ever',
            'lose weight',
            'easy money',
            '100% free',
            'fake',
            'xxx',
            'bonus',
            'secret',
            'exclusive',
            'guaranteed',
            'instant',
            'work from home',
            'as seen on',
            'perfect',
            'ultimate',
            'fast cash',
            'cheat',
            'must try',
            'unbelievable',
            'shocking',
            'unbelievable offer',
            'winner',
            'lucky',
            'hack',
            'unbeatable',
            'profit',
            'low carb miracle',
            'make money',
            'affiliate',
            'earn now',
            'trial',
            'contest',
            'one-time',
            'sensational',
            'no effort',
            'unreal',
            'unmissable',
            'fortune',
            'foolproof',
        ],
        ?array $groups = null,
        mixed $payload = null
    ) {

        parent::__construct(null, $groups, $payload);
    }
}
