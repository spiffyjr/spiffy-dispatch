<?php

namespace Spiffy\Dispatch\TestAsset;

class Invokable
{
    public function __invoke($id, $slug = 'foo')
    {
        return 'id: ' . $id . ', slug: ' . $slug;
    }
}
