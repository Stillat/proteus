<?php

namespace Stillat\Proteus\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Class CreateParentVisitor.
 *
 * Visits each node and sets its parent relationship.
 *
 * @since 1.0.0
 */
class CreateParentVisitor extends NodeVisitorAbstract
{
    private $stack;

    public function beginTraverse(array $nodes)
    {
        $this->stack = [];
    }

    public function enterNode(Node $node)
    {
        if (! empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }
        $this->stack[] = $node;
    }

    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
    }
}
