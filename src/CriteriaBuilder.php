<?php


namespace Asciisd\Zoho;


use Asciisd\Zoho\Facades\ZohoManager;

class CriteriaBuilder
{
    protected $criteria;
    protected $moduleIns;
    protected $operators = ['equals', 'starts_with'];
    protected $page = 1;
    protected $perPage = 200;

    public function __construct($moduleIns) {
        $this->moduleIns = $moduleIns ?? ZohoManager::useModule('leads');
    }

    /**
     * add criteria to the search
     *
     * @param $field
     * @param $value
     * @param string $operator
     *
     * @return $this
     */
    public static function where(
        $field,
        $value,
        $operator = 'equals',
        $moduleIns = null
    ) {
        $builder = new CriteriaBuilder($moduleIns);
        $builder->criteria = "";

        $builder->criteria .= static::queryBuilder($field, $operator, $value);

        return $builder;
    }

    private static function queryBuilder(...$args) {
        return "($args[0]:$args[1]:$args[2])";
    }

    public function startsWith(
        $field,
        $value,
        $operator = 'starts_with'
    ) {
        $this->criteria .= ' and ' . $this->queryBuilder($field, $operator,
                $value);

        return $this;
    }

    public function andWhere($field, $value, $operator = 'equals') {
        $this->criteria .= ' and ' . $this->queryBuilder($field, $operator,
                $value);

        return $this;
    }

    public function orWhere($field, $value, $operator = 'equals') {
        $this->criteria .= ' or ' . $this->queryBuilder($field, $operator,
                $value);

        return $this;
    }

    public function toString() {
        return $this->getCriteria() ?? '';
    }

    public function getCriteria() {
        return $this->criteria;
    }

    public function page($page) {
        $this->page = $page;

        return $this;
    }

    public function perPage($per_page) {
        $this->perPage = $per_page;

        return $this;
    }

    public function get() {
        $param_map = ["page" => $this->page, "per_page" => $this->perPage];

        return $this->moduleIns->searchRecordsByCriteria(
            $this->getCriteria(), $param_map
        )->getData();
    }

    public function search() {
        return $this->get();
    }
}
