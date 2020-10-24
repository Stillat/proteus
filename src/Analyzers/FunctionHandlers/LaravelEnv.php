<?php

namespace Stillat\Proteus\Analyzers\FunctionHandlers;

use PhpParser\Node\Expr\FuncCall;
use Stillat\Proteus\Contracts\FunctionHandlerContract;
use PhpParser\Node\Arg;

/**
 * Class LaravelEnv
 *
 * Provides support for rewriting Laravel env() helper function calls.
 *
 * @package Stillat\Proteus\Analyzers\FunctionHandlers
 */
class LaravelEnv implements FunctionHandlerContract
{

    /**
     * Analyzes the source expression and applies any required function mutations.
     *
     * @param FuncCall $expr The source expression.
     * @param mixed $currentNode The current node.
     * @param mixed $referenceNode The reference node.
     * @return mixed
     */
    public function handle(FuncCall $expr, $currentNode, $referenceNode)
    {
        $argCount = count($expr->args);

        if ($argCount === 2) {
            $lastArgIndex = count($expr->args) - 1;
            $expr->args[$lastArgIndex]->value = $referenceNode;
        } elseif ($argCount === 1) {
            $funcArg = new Arg($referenceNode);

            $expr->args[] = $funcArg;
        }
    }

}
