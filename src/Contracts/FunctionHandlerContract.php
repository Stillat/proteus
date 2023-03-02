<?php

namespace Stillat\Proteus\Contracts;

use PhpParser\Node\Expr\FuncCall;

/**
 * Interface FunctionHandlerContract.
 *
 * Function handlers are responsible for analyzing an expression to
 * apply mutations to function-based configuration items/values.
 */
interface FunctionHandlerContract
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
    public function handle(FuncCall $expr, $currentNode, $referenceNode, string $referenceKey);
}
