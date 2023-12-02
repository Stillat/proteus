<?php

namespace Stillat\Proteus;

use Exception;
use PhpParser\Lexer\Emulative;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Parser\Php7;

class ClosureParser
{
    public function parse($code)
    {
        $code = '<?php return '.$code.';';
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);

        $parser = new Php7($lexer);

        $stmts = $parser->parse($code);

        if (count($stmts) !== 1) {
            throw new Exception('Invalid closure provided.');
        }

        if (! $stmts[0] instanceof Return_) {
            throw new Exception('Invalid closure provided.');
        }

        if ($stmts[0]->expr instanceof Closure || $stmts[0]->expr instanceof ArrowFunction) {
            return $stmts[0]->expr;
        }

        throw new Exception('Invalid closure provided.');
    }
}
