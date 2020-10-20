<?php

namespace Stillat\WolfPack\Analyzers;

/**
 * Class MutationGraph
 *
 * Represents a collection of mutations to apply to a configuration document.
 *
 * @package Stillat\WolfPack\Analyzers
 * @since 1.0.0
 */
class MutationGraph
{

    /**
     * The probable insertions.
     *
     * @var array
     */
    public $insertions = [];

    /**
     * The probable updates.
     *
     * @var array
     */
    public $updates = [];

}