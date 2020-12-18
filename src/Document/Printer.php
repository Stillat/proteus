<?php

namespace Stillat\Proteus\Document;

use Exception;
use PhpParser\Builder\Class_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Expr\New_;
use PhpParser\Internal\PrintableNewAnonClassNode;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\PrettyPrinter\Standard;

/**
 * Class Printer
 *
 * Provides utilities for converting an AST tree back to a PHP document.
 *
 * @package Stillat\Proteus\Document
 * @since 1.0.0
 */
class Printer extends Standard
{

    protected function pExpr_Array(Array_ $node)
    {
        return '[' . $this->pMaybeMultiline($node->items, true) . ']';
    }


    protected function pExpr_FuncCall(FuncCall $node) {
        return $this->pCallLhs($node->name)
            . '(' . $this->pCommaSeparated($node->args) . ')';
    }

    /**
     * Pretty prints a node.
     *
     * This method also handles formatting preservation for nodes.
     *
     * @param Node $node Node to be pretty printed
     * @param bool $parentFormatPreserved Whether parent node has preserved formatting
     *
     * @return string Pretty printed node
     * @throws Exception
     */
    protected function p(Node $node, $parentFormatPreserved = false) : string {
        // No orig tokens means this is a normal pretty print without preservation of formatting
        if (!$this->origTokens) {
            return $this->{'p' . $node->getType()}($node);
        }

        /** @var Node $origNode */
        $origNode = $node->getAttribute('origNode');
        if (null === $origNode) {
            return $this->pFallback($node);
        }

        $class = \get_class($node);
        \assert($class === \get_class($origNode));

        $startPos = $origNode->getStartTokenPos();
        $endPos = $origNode->getEndTokenPos();
        \assert($startPos >= 0 && $endPos >= 0);

        $fallbackNode = $node;
        if ($node instanceof New_ && $node->class instanceof Class_) {
            // Normalize node structure of anonymous classes
            $node = PrintableNewAnonClassNode::fromNewNode($node);
            $origNode = PrintableNewAnonClassNode::fromNewNode($origNode);
        }

        // InlineHTML node does not contain closing and opening PHP tags. If the parent formatting
        // is not preserved, then we need to use the fallback code to make sure the tags are
        // printed.
        if ($node instanceof InlineHTML && !$parentFormatPreserved) {
            return $this->pFallback($fallbackNode);
        }

        $indentAdjustment = $this->indentLevel - $this->origTokens->getIndentationBefore($startPos);

        $type = $node->getType();
        $fixupInfo = $this->fixupMap[$class] ?? null;

        $result = '';

        $pos = $startPos;
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            $origSubNode = $origNode->$subNodeName;

            if ((!$subNode instanceof Node && $subNode !== null)
                || (!$origSubNode instanceof Node && $origSubNode !== null)
            ) {
                if ($subNode === $origSubNode) {
                    // Unchanged, can reuse old code
                    continue;
                }

                if (is_array($subNode) && is_array($origSubNode)) {
                    // Array subnode changed, we might be able to reconstruct it
                    $listResult = $this->pArray(
                        $subNode, $origSubNode, $pos, $indentAdjustment, $type, $subNodeName,
                        $fixupInfo[$subNodeName] ?? null
                    );
                    if (null === $listResult) {
                        return $this->pFallback($fallbackNode);
                    }

                    $result .= $listResult;
                    continue;
                }

                if (is_int($subNode) && is_int($origSubNode)) {
                    // Check if this is a modifier change
                    $key = $type . '->' . $subNodeName;
                    if (!isset($this->modifierChangeMap[$key])) {
                        return $this->pFallback($fallbackNode);
                    }

                    $findToken = $this->modifierChangeMap[$key];
                    $result .= $this->pModifiers($subNode);
                    $pos = $this->origTokens->findRight($pos, $findToken);
                    continue;
                }

                // If a non-node, non-array subnode changed, we don't be able to do a partial
                // reconstructions, as we don't have enough offset information. Pretty print the
                // whole node instead.
                return $this->pFallback($fallbackNode);
            }

            $extraLeft = '';
            $extraRight = '';
            if ($origSubNode !== null) {
                $subStartPos = $origSubNode->getStartTokenPos();
                $subEndPos = $origSubNode->getEndTokenPos();
                \assert($subStartPos >= 0 && $subEndPos >= 0);
            } else {
                if ($subNode === null) {
                    // Both null, nothing to do
                    continue;
                }

                // A node has been inserted, check if we have insertion information for it
                $key = $type . '->' . $subNodeName;
                if (!isset($this->insertionMap[$key])) {
                    return $this->pFallback($fallbackNode);
                }

                list($findToken, $beforeToken, $extraLeft, $extraRight) = $this->insertionMap[$key];
                if (null !== $findToken) {
                    $subStartPos = $this->origTokens->findRight($pos, $findToken)
                        + (int) !$beforeToken;
                } else {
                    $subStartPos = $pos;
                }

                if (null === $extraLeft && null !== $extraRight) {
                    // If inserting on the right only, skipping whitespace looks better
                    $subStartPos = $this->origTokens->skipRightWhitespace($subStartPos);
                }
                $subEndPos = $subStartPos - 1;
            }

            if (null === $subNode) {
                // A node has been removed, check if we have removal information for it
                $key = $type . '->' . $subNodeName;
                if (!isset($this->removalMap[$key])) {
                    return $this->pFallback($fallbackNode);
                }

                // Adjust positions to account for additional tokens that must be skipped
                $removalInfo = $this->removalMap[$key];
                if (isset($removalInfo['left'])) {
                    $subStartPos = $this->origTokens->skipLeft($subStartPos - 1, $removalInfo['left']) + 1;
                }
                if (isset($removalInfo['right'])) {
                    $subEndPos = $this->origTokens->skipRight($subEndPos + 1, $removalInfo['right']) - 1;
                }
            }

            $result .= $this->origTokens->getTokenCode($pos, $subStartPos, $indentAdjustment);

            if (null !== $subNode) {
                $result .= $extraLeft;

                $origIndentLevel = $this->indentLevel;
                $this->setIndentLevel($this->origTokens->getIndentationBefore($subStartPos) + $indentAdjustment);

                // If it's the same node that was previously in this position, it certainly doesn't
                // need fixup. It's important to check this here, because our fixup checks are more
                // conservative than strictly necessary.
                if (isset($fixupInfo[$subNodeName])
                    && $subNode->getAttribute('origNode') !== $origSubNode
                ) {
                    $fixup = $fixupInfo[$subNodeName];
                    $res = $this->pFixup($fixup, $subNode, $class, $subStartPos, $subEndPos);
                } else {
                    $res = $this->p($subNode, true);
                }

                $this->safeAppend($result, $res);
                $this->setIndentLevel($origIndentLevel);

                $result .= $extraRight;
            }

            $pos = $subEndPos + 1;
        }

        $result .= $this->origTokens->getTokenCode($pos, $endPos + 1, $indentAdjustment);
        return $result;
    }

    /**
     * Pretty prints a comma-separated list of nodes in multiline style, including comments.
     *
     * The result includes a leading newline and one level of indentation (same as pStmts).
     *
     * @param Node[] $nodes         Array of Nodes to be printed
     * @param bool   $trailingComma Whether to use a trailing comma
     *
     * @return string Comma separated pretty printed nodes in multiline style
     */
    protected function pCommaSeparatedMultiline(array $nodes, bool $trailingComma) : string {
        $this->indent();

        $result = '';
        $lastIdx = count($nodes) - 1;
        foreach ($nodes as $idx => $node) {
            if ($node !== null) {
                $comments = $node->getComments();
                if ($comments) {
                    $result .= $this->nl. $this->pComments($comments);
                }

                $result .= $this->nl . $this->p($node);
            } else {
                $result .= $this->nl;
            }
            if ($trailingComma || $idx !== $lastIdx) {
                $result .= ',';
            }
        }

        $this->outdent();
        return $result;
    }

    protected function pMaybeMultiline(array $nodes, bool $trailingComma = false)
    {
        return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
    }

    /**
     * Pretty prints an array of nodes and implodes the printed values with commas.
     *
     * @param Node[] $nodes Array of Nodes to be printed
     *
     * @return string Comma separated pretty printed nodes
     */
    protected function pCommaSeparated(array $nodes): string
    {
        return $this->pImplode($nodes, ",");
    }

    protected function pExpr_ArrayItem(ArrayItem $node)
    {
        return parent::pExpr_ArrayItem($node);
    }

    protected function hasNodeWithComments(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node && $node->getComments()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Pretty prints an array of nodes and implodes the printed values.
     *
     * @param Node[] $nodes Array of Nodes to be printed
     * @param string $glue  Character to implode with
     *
     * @return string Imploded pretty printed nodes
     */
    protected function pImplode(array $nodes, string $glue = '') : string {
        $pNodes = [];
        foreach ($nodes as $node) {
            if (null === $node) {
                $pNodes[] = '';
            } else {
                $pNodes[] = $this->p($node);
            }
        }

        return implode($glue.$this->nl, $pNodes);
    }

}
