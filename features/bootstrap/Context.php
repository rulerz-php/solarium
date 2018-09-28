<?php

declare(strict_types=1);

use Behat\Behat\Context\Context as BehatContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

class Context implements BehatContext
{
    /** @var \RulerZ\RulerZ */
    private $rulerz;

    /** @var mixed */
    private $dataset;

    /** @var array */
    private $parameters = [];

    /** @var array */
    private $executionContext = [];

    /** @var mixed */
    private $results;

    /** @var \Solarium\Client */
    private $client;

    public function __construct()
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
     * Returns the compilation target to be tested.
     */
    private function getCompilationTarget(): \RulerZ\Compiler\CompilationTarget
    {
        $visitor = new \RulerZ\Solarium\Target\Solarium();
        $visitor->defineInlineOperator('boost', function ($expression, $factor) {
            return sprintf('%s^%d', $expression, $factor);
        });

        return $visitor;
    }

    /**
     * Returns the default dataset to be filtered.
     *
     * @return mixed
     */
    private function getDefaultDataset()
    {
        return $this->client;
    }

    /**
     * Create a default execution context that will be given to RulerZ when
     * filtering a dataset.
     *
     * @return mixed
     */
    private function getDefaultExecutionContext()
    {
        return [];
    }

    /**
     * @Given RulerZ is configured
     */
    public function rulerzIsConfigured()
    {
        // compiler
        $compiler = new \RulerZ\Compiler\Compiler(new \RulerZ\Compiler\EvalEvaluator());

        // RulerZ engine
        $this->rulerz = new \RulerZ\RulerZ($compiler, [$this->getCompilationTarget()]);
    }

    /**
     * @When I define the parameters:
     */
    public function iDefineTheParameters(TableNode $parameters)
    {
        // named parameters
        if (count($parameters->getRow(0)) !== 1) {
            $this->parameters = $parameters->getRowsHash();

            return;
        }

        // positional parameters
        $this->parameters = array_map(function ($row) {
            return $row[0];
        }, $parameters->getRows());
    }

    /**
     * @When I use the default execution context
     */
    public function iUseTheDefaultExecutionContext()
    {
        $this->executionContext = $this->getDefaultExecutionContext();
    }

    /**
     * @When I use the default dataset
     */
    public function iUseTheDefaultDataset()
    {
        $this->dataset = $this->getDefaultDataset();
    }

    /**
     * @When I filter the dataset with the rule:
     */
    public function iFilterTheDatasetWithTheRule(PyStringNode $rule)
    {
        $this->results = $this->rulerz->filter($this->dataset, (string) $rule, $this->parameters, $this->executionContext);

        $this->parameters = [];
        $this->executionContext = [];
    }

    /**
     * @Then I should have the following results:
     */
    public function iShouldHaveTheFollowingResults(TableNode $table)
    {
        $results = iterator_to_array($this->results);

        if (count($table->getHash()) !== count($results)) {
            throw new \RuntimeException(sprintf("Expected %d results, got %d. Expected:\n%s\nGot:\n%s", count($table->getHash()), count($results), $table, var_export($results, true)));
        }

        foreach ($table as $row) {
            foreach ($results as $result) {
                $value = $this->fieldFromResult($result, 'pseudo');

                if ($value === $row['pseudo']) {
                    return;
                }
            }

            throw new \RuntimeException(sprintf('Player "%s" not found in the results.', $row['pseudo']));
        }
    }

    /**
     * Fetches a field from a result.
     *
     * @param mixed $result
     * @param string $field
     *
     * @return mixed
     */
    private function fieldFromResult($result, $field)
    {
        return $result[$field][0];
    }
}
