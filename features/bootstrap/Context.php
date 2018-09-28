<?php

declare(strict_types=1);

use Behat\Behat\Context\Context as BehatContext;
use RulerZ\Test\BaseContext;

class Context extends BaseContext implements BehatContext
{
    /** @var \Solarium\Client */
    private $client;

    public function initialize()
    {
        $dotenv = new Dotenv\Dotenv(__DIR__.'/../../');
        $dotenv->load();

        $this->client = new Solarium\Client([
            'endpoint' => [
                $_ENV['SOLR_CORE'] => [
                    'host' => $_ENV['SOLR_HOST'],
                    'port' => $_ENV['SOLR_PORT'],
                    'path' => $_ENV['SOLR_PATH'],
                    'core' => $_ENV['SOLR_CORE'],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompilationTarget(): \RulerZ\Compiler\CompilationTarget
    {
        $visitor = new \RulerZ\Solarium\Target\Solarium();
        $visitor->defineInlineOperator('boost', function ($expression, $factor) {
            return sprintf('%s^%d', $expression, $factor);
        });

        return $visitor;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultDataset()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    protected function fieldFromResult($result, $field)
    {
        return $result[$field][0];
    }
}
