<?php

namespace Stillat\Proteus\Analyzers\FunctionHandlers;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Stillat\Proteus\Contracts\FunctionHandlerContract;

/**
 * Class LaravelEnv.
 *
 * Provides support for rewriting Laravel env() helper function calls.
 */
class LaravelEnv implements FunctionHandlerContract
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
        $argCount = count($expr->args);

        if ($referenceNode instanceof FuncCall && $referenceNode->name->toString() === $expr->name->toString()) {
            // Assign the incoming args to the existing call.
            $expr->args = $referenceNode->args;
        } else {
            if ($argCount === 2) {
                $lastArgIndex = count($expr->args) - 1;
                $expr->args[$lastArgIndex]->value = $referenceNode;
            } elseif ($argCount === 1) {
                $funcArg = new Arg($referenceNode);

                $expr->args[] = $funcArg;
            }
        }

        return $expr;
    }
}
