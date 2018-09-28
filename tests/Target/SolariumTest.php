<?php

declare(strict_types=1);

namespace Tests\RulerZ\Target;

use PHPUnit\Framework\TestCase;
use RulerZ\Compiler\CompilationTarget;
use RulerZ\Compiler\Context;
use RulerZ\Model\Executor;
use RulerZ\Model\Rule;
use RulerZ\Parser\Parser;
use RulerZ\Solarium\Target\Solarium;
use Solarium\Client;

class SolariumTest extends TestCase
{
    /** @var Solarium */
    private $target;

    public function setUp()
    {
        $this->target = new Solarium();
    }

    /**
     * @dataProvider supportedTargetsAndModes
     */
    public function testSupportedTargetsAndModes($target, string $mode): void
    {
        $this->assertTrue($this->target->supports($target, $mode));
    }

    public function supportedTargetsAndModes(): array
    {
        $client = $this->createMock(Client::class);

        return [
            [$client, CompilationTarget::MODE_APPLY_FILTER],
            [$client, CompilationTarget::MODE_FILTER],
            [$client, CompilationTarget::MODE_SATISFIES],
        ];
    }

    /**
     * @dataProvider unsupportedTargets
     */
    public function testItRejectsUnsupportedTargets($target)
    {
        $this->assertFalse($this->target->supports($target, CompilationTarget::MODE_FILTER));
    }

    public function unsupportedTargets(): array
    {
        return [
            ['string'],
            [42],
            [new \stdClass()],
            [[]],
        ];
    }

    public function testItReturnsAnExecutorModel()
    {
        $rule = 'points = 1';

        /** @var Executor $executorModel */
        $executorModel = $this->target->compile($this->parseRule($rule), new Context());

        $this->assertInstanceOf(Executor::class, $executorModel);

        $this->assertCount(2, $executorModel->getTraits());
        $this->assertSame("'points:1'", $executorModel->getCompiledRule());
    }

    public function testItSupportsParameters()
    {
        $rule = 'points > :nb_points and group IN [:admin_group, :super_admin_group]';
        $expectedRule = '\'(points:{\'. $parameters[\'nb_points\'] .\' TO *] AND group:(\'. $parameters[\'admin_group\'] .\' OR \'. $parameters[\'super_admin_group\'] .\'))\'';

        /** @var Executor $executorModel */
        $executorModel = $this->target->compile($this->parseRule($rule), new Context());

        $this->assertSame($expectedRule, $executorModel->getCompiledRule());
    }

    public function testItSupportsNamedParameters()
    {
        $rule = 'points > :nb_points';
        $expectedRule = "'points:{'. \$parameters['nb_points'] .' TO *]'";

        /** @var Executor $executorModel */
        $executorModel = $this->target->compile($this->parseRule($rule), new Context());

        $this->assertSame($expectedRule, $executorModel->getCompiledRule());
    }

    public function testItSupportsInlineOperators()
    {
        $rule = 'points > 30 and always_true()';
        $expectedDql = "'(points:{30 TO *] AND *:*)'";

        $this->target->defineInlineOperator('always_true', function () {
            return '*:*';
        });

        /** @var Executor $executorModel */
        $executorModel = $this->target->compile($this->parseRule($rule), new Context());

        $this->assertSame($expectedDql, $executorModel->getCompiledRule());
    }

    private function parseRule(string $rule): Rule
    {
        return (new Parser())->parse($rule);
    }
}
