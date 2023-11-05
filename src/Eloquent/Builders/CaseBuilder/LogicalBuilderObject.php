<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder;

class LogicalBuilderObject
{
    /** @var mixed $ands */
    public $ands;
    /** @var mixed $ors */
    public $ors;

    /**
     * @param $ands
     * @param $ors
     */
    public function __construct($ands = null, $ors = null)
    {
        $this->ands = $ands;
        $this->ors = $ors;
    }
}