<?php

declare(strict_types=1);

namespace RulerZ\Solarium\Executor;

use RulerZ\Context\ExecutionContext;

trait SolariumFilterTrait
{
    abstract protected function execute($target, array $operators, array $parameters);

    /**
     * {@inheritdoc}
     */
    public function applyFilter($target, array $parameters, array $operators, ExecutionContext $context)
    {
        /** @var \Solarium\Client $target */

        /** @var string $searchQuery */
        $searchQuery = $this->execute($target, $operators, $parameters);

        $query = $target->createSelect();
        $query->createFilterQuery('rulerz')->setQuery($searchQuery);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($target, array $parameters, array $operators, ExecutionContext $context)
    {
        /** @var \Solarium\Client $target */

        $query = $this->applyFilter($target, $parameters, $operators, $context);

        return $target->select($query)->getIterator();
    }
}
