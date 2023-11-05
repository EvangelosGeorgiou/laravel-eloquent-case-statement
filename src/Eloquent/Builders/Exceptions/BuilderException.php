<?php

namespace EvangGeo\CaseStatement\Eloquent\Builders\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

class BuilderException extends Exception implements Arrayable
{
    public function toArray(): array
    {
        return [
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'type'    => get_class($this),
            'details' => $this->getTrace(),
        ];
    }
}