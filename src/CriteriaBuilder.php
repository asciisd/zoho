<?php


namespace Asciisd\Zoho;


use Asciisd\Zoho\Facades\ZohoManager;

class CriteriaBuilder
{
    protected string $criteria = '';
    protected ZohoModule $module;
    protected array $operators = ['equals', 'starts_with'];
    protected int $page = 1;
    protected int $perPage = 200;

    public function __construct($module)
    {
        $this->module = $module ?? ZohoManager::useModule();
    }

    /**
     * add criteria to the search
     *
     * @param $field
     * @param $value
     * @param  string  $operator
     * @param  null  $module
     *
     * @return $this
     */
    public static function where($field, $value, string $operator = 'equals', $module = null): CriteriaBuilder|static
    {
        $builder = new CriteriaBuilder($module);

        $builder->criteria = static::queryBuilder($field, $operator, $value);

        return $builder;
    }

    private static function queryBuilder(...$args): string
    {
        return "($args[0]:$args[1]:$args[2])";
    }

    public function startsWith($field, $value, $operator = 'starts_with'): static
    {
        $this->criteria .= ' and '.$this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function andWhere($field, $value, $operator = 'equals'): static
    {
        $this->criteria .= ' and '.$this->queryBuilder($field, $operator,
                $value);

        return $this;
    }

    public function orWhere($field, $value, $operator = 'equals'): static
    {
        $this->criteria .= ' or '.$this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function toString(): string
    {
        return $this->getCriteria() ?? '';
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function page($page): static
    {
        $this->page = $page;

        return $this;
    }

    public function perPage($per_page): static
    {
        $this->perPage = $per_page;

        return $this;
    }

    public function get(): array
    {
        return $this->module->searchRecordsByCriteria(
            $this->getCriteria(), $this->page, $this->perPage
        );
    }

    public function search(): array
    {
        return $this->get();
    }
}
