<?php

namespace Stillat\Proteus\Analyzers;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use Stillat\Proteus\Contracts\FunctionHandlerContract;

/**
 * Class FunctionHandler.
 *
 * Provides a centralized location to analyze and handle a variety of function calls.
 */
class FunctionHandler
{
    /**
     * @var FunctionHandlerContract[]
     */
    protected $handlers = [];

    /**
     * Registers a new function handler.
     *
     * @param  string  $name    The function name.
     * @param  FunctionHandlerContract  $handler The handler.
     */
    public function addHandler($name, FunctionHandlerContract $handler)
    {
        $this->handlers[$name] = $handler;
    }

    /**
     * Analyzes the source expression and applies any required function mutations.
     *
     * @param  Expr  $expr          The source expression.
     * @param  mixed  $currentNode   The current node.
     * @param  mixed  $referenceNode The reference node.
     * @param  string  $referenceKey  The reference key.
     * @return mixed
     */
    public function handle(Expr $expr, $currentNode, $referenceNode, string $referenceKey)
    {
        $funcName = $this->getFunctionName($expr);

        if ($funcName === null) {
            return;
        }

        if ($expr instanceof FuncCall && array_key_exists($funcName, $this->handlers)) {
            $this->handlers[$funcName]->handle($expr, $currentNode, $referenceNode, $referenceKey);
        }
    }

    /**
     * Attempts to retrieve the function call's name.
     *
     * @param  Expr  $expr The expression.
     * @return string|null
     */
    private function getFunctionName(Expr $expr)
    {
        if ($expr instanceof FuncCall) {
            return $expr->name->getParts()[0];
        }

        return null;
    }
}
