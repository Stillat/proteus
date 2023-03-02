<?php

namespace Stillat\Proteus\Contracts;

/**
 * Interface ValueWriterContract.
 *
 * Provides a consistent interface for converting runtime value types to mutable node types.
 *
 * @since 1.0.0
 */
interface ValueWriterContract
{
    /**
     * Writes the provided value.
     *
     * @param  mixed  $value The value to write.
     * @return mixed
     */
    public function write($value);
}
