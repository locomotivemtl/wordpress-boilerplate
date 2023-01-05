<?php

namespace App\Theme\Transformer;

/**
 * Abstract transformer
 */
abstract class AbstractTransformer
{
    /**
     * Alias of {@see self::__invoke()}.
     *
     * @param  array $data
     * @return array|null
     */
    public function transform(array $data)
    {
        return $this($data);
    }
}
