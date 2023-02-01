<?php

namespace Stillat\Proteus\Visitors;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitor;
use Stillat\Proteus\Analyzers\ConfigAnalyzer;

/**
 * Class ConfigNodeVisitor.
 *
 * Visits each AST node and sets information about the parent history.
 *
 * @since 1.0.0
 */
class ConfigNodeVisitor implements NodeVisitor
{
    /**
     * @var null|ConfigAnalyzer
     */
    protected $configAnalyzer = null;

    public function setAnalyzer(ConfigAnalyzer $analyzer)
    {
        $this->configAnalyzer = $analyzer;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof ArrayItem) {
            /** @var String_ $stringKey */
            $stringKey = $node->key;

            //$refKey = $this->constructReferenceKey($node, '');
            $thisKey = $this->getArrayItemKey($node);
            $refKey = $this->getParentKeyChainItem($node, $thisKey);

            if ($this->configAnalyzer != null && Str::endsWith($refKey, '.') === false) {
                $this->configAnalyzer->mapNode($refKey, $node);
            }
        }
    }

    private function getArrayItemKey(ArrayItem $node)
    {
        $stringKey = $node->key;

        if ($stringKey != null) {
            return $stringKey->value;
        }

        return null;
    }

    private function getParentKeyChainItem(Node $node, $key)
    {
        $parent = $this->findNearestArrayItem($node);

        if ($parent != null) {
            $nodeKey = $this->getArrayItemKey($parent);

            return $this->getParentKeyChainItem($parent, $nodeKey.'.'.$key);
        }

        return $key;
    }

    private function findNearestArrayItem(Node $node)
    {
        $parent = $node->getAttribute('parent');

        if ($parent !== null && ($parent instanceof ArrayItem) === false) {
            return $this->findNearestArrayItem($parent);
        }

        return $parent;
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function leaveNode(Node $node)
    {
    }

    public function afterTraverse(array $nodes)
    {
    }
}
