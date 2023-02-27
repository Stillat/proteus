<?php

namespace Stillat\Proteus\Analyzers\FunctionHandlers;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Contracts\FunctionHandlerContract;

/**
 * Class LaravelResourcePath.
 *
 * Provides support for the simple named functions accepting one or zero parameters.
 */
class SimpleFunctionHandler implements FunctionHandlerContract
{
    /**
     * Analyzes the source expression and applies any required function mutations.
     *
     * @param  FuncCall  $expr          The source expression.
     * @param  mixed  $currentNode   The current node.
     * @param  mixed  $referenceNode The reference node.
     * @param  string  $referenceKey  The reference key.
     * @return mixed
     */
    public function handle(FuncCall $expr, $currentNode, $referenceNode, string $referenceKey)
    {
        if (count($expr->args) === 1) {
            $expr->args[0] = $referenceNode;
        } else {
            $expr->args[] = $referenceNode;
        }

        if ($referenceNode instanceof String_) {
            $refVal = $referenceNode->value;

            if ($referenceNode === null || mb_strlen(trim($refVal)) === 0) {
                $expr->args = [];
            }
        } elseif ($referenceNode === null) {
            $expr->args = [];
        }

        return $expr;
    }
}
