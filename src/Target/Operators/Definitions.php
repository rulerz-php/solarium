<?php

declare(strict_types=1);

namespace RulerZ\Solarium\Target\Operators;

use RulerZ\Target\Operators\Definitions as RulerzDefinitions;

class Definitions
{
    public static function create(RulerzDefinitions $customOperators): RulerzDefinitions
    {
        $defaultInlineOperators = [
            'and' => function ($a, $b) {
                return sprintf('(%s AND %s)', $a, $b);
            },
            'or' => function ($a, $b) {
                return sprintf('(%s OR %s)', $a, $b);
            },
            'not' => function ($a) {
                return sprintf('-(%s)', $a);
            },
            '=' => function ($a, $b) {
                return sprintf('%s:%s', $a, $b);
            },
            '!=' => function ($a, $b) {
                return sprintf('-%s:%s', $a, $b);
            },
            '>' => function ($a, $b) {
                return sprintf('%s:{%s TO *]', $a, $b);
            },
            '>=' => function ($a, $b) {
                return sprintf('%s:[%s TO *]', $a, $b);
            },
            '<' => function ($a, $b) {
                return sprintf('%s:[* TO %s}', $a, $b);
            },
            '<=' => function ($a, $b) {
                return sprintf('%s:[* TO %s]', $a, $b);
            },
            'in' => function ($a, $b) {
                return sprintf('%s:(%s)', $a, implode(' OR ', $b));
            },
        ];

        $definitions = new RulerzDefinitions();
        $definitions->defineInlineOperators($defaultInlineOperators);

        return $definitions->mergeWith($customOperators);
    }
}
