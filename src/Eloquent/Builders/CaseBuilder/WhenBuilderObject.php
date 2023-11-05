<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\CaseBuilder;

class WhenBuilderObject
{
    public array $when;
    /** @var mixed $ands */
    public $ands;
    /** @var mixed $ors */
    public $ors;
    /** @var string|array string  */
    public $then;

    public function __construct(array $when, $ands, $ors, $then)
    {
        $this->when = $when;
        $this->ands = $ands;
        $this->ors = $ors;
        $this->then = $then;
    }
}