<?php


namespace Asciisd\Zoho;


class CriteriaBuilder
{
    protected $criteria;
    protected $operators = ['equals', 'starts_with'];

    /**
     * add criteria to the search
     *
     * @param $field
     * @param $value
     * @param string $operator
     * @return $this
     */
    public static function where($field, $value, $operator = 'equals')
    {
        $builder = new CriteriaBuilder();
        $builder->criteria = "";

        $builder->criteria .= static::queryBuilder($field, $operator, $value);

        return $builder;
    }

    public function startsWith($field, $value, $operator = 'starts_with')
    {
        $this->criteria .= ' and ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function andWhere($field, $value, $operator = 'equals')
    {
        $this->criteria .= ' and ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function orWhere($field, $value, $operator = 'equals')
    {
        $this->criteria .= ' or ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    private static function queryBuilder(...$args)
    {
        return "($args[0]:$args[1]:$args[2])";
    }

    public function toString()
    {
        return $this->getCriteria() ?? '';
    }

    public function getCriteria()
    {
        return $this->criteria;
    }
}
