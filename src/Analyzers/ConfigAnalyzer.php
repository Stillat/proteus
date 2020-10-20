<?php

namespace Stillat\WolfPack\Analyzers;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser\Php7;
use Stillat\WolfPack\Document\Printer;
use Stillat\WolfPack\Visitors\ConfigNodeVisitor;
use Stillat\WolfPack\Visitors\CreateParentVisitor;
use Stillat\WolfPack\Writers\StringWriter;

/**
 * Class ConfigAnalyzer
 *
 *
 *
 * @package Stillat\WolfPack\Analyzers
 * @since 1.0.0
 */
class ConfigAnalyzer
{

    /**
     * The loaded file path.
     *
     * @var string
     */
    protected $filePath = '';

    /**
     * The loaded configuration contents.
     *
     * @var string
     */
    protected $contents = '';

    /**
     * The parser instance.
     *
     * @var Php7
     */
    private $parser = null;

    /**
     * The PHP lexer instance.
     *
     * @var Emulative
     */
    private $lexer = null;


    /**
     * The original statements, to help preserve formatting.
     *
     * @var array
     */
    private $oldStmts = [];

    /**
     * The new statements, which can be mutated to insert new nodes.
     *
     * @var array
     */
    private $newStmts = [];

    /**
     * The old tokens, which contain the original formatting details.
     * @var array
     */
    private $oldTokens = [];

    /**
     * A mapping of all known keys and nodes.
     *
     * @var array
     */
    private $nodeMapping = [];

    /**
     * The root node, if any.
     *
     * @var null|Node
     */
    private $rootNode = null;

    public function __construct()
    {
        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $this->parser = new Php7($this->lexer);
    }

    /**
     * Maps a known key to a resolved node.
     *
     * @param string $key The key.
     * @param Node $node The node.
     */
    public function mapNode($key, Node $node)
    {
        $this->nodeMapping[$key] = $node;
    }

    /**
     * Inserts a new value with the provided root key.
     *
     * @param string $key The new value's key.
     * @param Node $node The new value.
     */
    public function insertRootValue($key, Node $node)
    {
        @$this->rootNode->items[] = $this->wrapInArrayItem($key, $node);
    }

    /**
     * Wraps the provided value in an ArrayItem object.
     *
     * @param string $key The desired key.
     * @param Node $node The node value to wrap.
     * @return ArrayItem
     */
    private function wrapInArrayItem($key, $node)
    {
        if ($node instanceof Array_) {
            $node = $this->checkForNestedUselessArrays($node);
        }

        $stringWriter = new StringWriter();
        $itemKey = $stringWriter->write($key);
        return new ArrayItem($node, $itemKey);
    }

    /**
     * Analyzes the provided node and checks for useless double array wrappings.
     *
     * These typically come out of the key->structure process and look like this:
     *  'newkey' => [['value1', 'value2']]
     *
     * This method rewrites them to look like this:
     *  'newkey' => ['value1', 'value2']
     *
     * @param Array_ $node The node to check.
     * @return Array_
     */
    private function checkForNestedUselessArrays(Array_ $node)
    {
        $newNodes = [];

        $innerArrCount = 0;
        /** @var ArrayItem $innerItem */
        foreach ($node->items as $innerItem) {
            if ($innerItem->value instanceof Array_) {
                $innerArrCount += 1;
            }
        }

        // If none of the inner items are an array, we will just bail and return the current node.
        if ($innerArrCount === 0) {
            return $node;
        }

        /** @var ArrayItem $item */
        foreach ($node->items as $item) {
            if ($item->value instanceof Array_) {
                if ($item->key != null) {
                    $newNodes[] = new ArrayItem($this->checkForNestedUselessArrays($item->value), $item->key);
                } else {
                    if ($item->value instanceof Array_) {
                        foreach ($item->value->items as $subItem) {
                            $newNodes[] = $subItem;
                        }
                    }
                }
            }
        }

        $node->items = array_values($newNodes);

        return $node;
    }

    /**
     * Attempts to insert the provided values at the target key location.
     *
     * @param string $key The key insertion point.
     * @param mixed $values The values to insert.
     */
    public function insertValuesAtNode($key, $values)
    {
        if ($this->hasNode($key)) {
            $nodeToInsertInto = $this->nodeMapping[$key];

            if ($nodeToInsertInto instanceof ArrayItem) {
                if ($nodeToInsertInto->value instanceof Array_) {
                    if ($values instanceof Array_) {
                        foreach ($values->items as $arrayItem) {
                            $nodeToInsertInto->value->items[] = $arrayItem;
                        }
                    }
                } else {
                    $this->replaceNodeValue($key, $values);
                }
            }
        }
    }

    /**
     * Checks if a node with the provided key exists.
     *
     * @param string $key The desired key.
     * @return bool
     */
    public function hasNode($key)
    {
        return array_key_exists($key, $this->nodeMapping);
    }

    /**
     * Checks if a known node is an array.
     *
     * @param string $key The desired key.
     * @return bool
     */
    public function isNodeArray($key)
    {
        $nodeValue = $this->nodeMapping[$key];

        if ($nodeValue instanceof ArrayItem) {
            if ($nodeValue->value instanceof Array_) {
                return true;
            }
        }

        return false;
    }

    /**
     * Appends a value to an existing array at a known location.
     *
     * @param string $key The target location.
     * @param Node $node The value to append.
     */
    public function appendArrayItem($key, Node $node)
    {
        $currentNode = $this->nodeMapping[$key];
        if ($currentNode instanceof ArrayItem) {
            if ($currentNode->value instanceof Array_) {
                $newSubNode = new ArrayItem($node);

                $currentNode->value->items[] = $newSubNode;
            }
        }
    }

    /**
     * Attempts to replace a value at a known location with the provided node value.
     *
     * @param string $key The replacement location.
     * @param Node $node The value to insert.
     * @param bool $completeReplace Whether or not merging behavior is enabled.
     */
    public function replaceNodeValue($key, Node $node, $completeReplace = false)
    {
        $currentNode = $this->nodeMapping[$key];

        if ($currentNode instanceof ArrayItem) {
            if ($currentNode->value instanceof Array_ && $node instanceof Array_) {

                if ($completeReplace === false) {
                    /** @var ArrayItem $mergeItem */
                    foreach ($node->items as $mergeItem) {
                        if ($mergeItem->value instanceof Array_) {
                            foreach ($mergeItem->value->items as $subMergeItem) {
                                $currentNode->value->items[] = $subMergeItem;
                            }

                        } else {
                            $currentNode->value->items[] = $mergeItem;
                        }
                    }
                } else {
                    $newNodes = [];
                    /** @var ArrayItem $mergeItem */
                    foreach ($node->items as $mergeItem) {
                        if ($mergeItem->value instanceof Array_) {
                            foreach ($mergeItem->value->items as $subMergeItem) {
                                $newNodes[] = $subMergeItem;
                            }

                        } else {
                            $newNodes[] = $mergeItem;
                        }
                    }

                    $currentNode->value->items = array_values($newNodes);
                }
                return;
            }

            if ($currentNode->value instanceof Node\Expr\Cast\Bool_) {
                $boolCastNode = $currentNode->value;

                if ($boolCastNode->expr instanceof FuncCall) {
                    if ($this->isEnvCall($boolCastNode->expr)) {
                        $this->replaceEnvCallDefault($currentNode->value->expr, $node);
                        return;
                    }
                }
            } elseif ($currentNode->value instanceof FuncCall) {

                if ($this->isEnvCall($currentNode->value)) {
                    $this->replaceEnvCallDefault($currentNode->value, $node);

                    return;
                }
            } else {
                // Replace node value.
                $currentNode->value = $node;
            }
        }
    }

    /**
     * Tests if the provided function call is a call to Laravel's `env()` function.
     *
     * @param FuncCall $funcCall The function call to analyze.
     * @return bool
     */
    protected function isEnvCall(FuncCall $funcCall)
    {
        if ($funcCall->name->parts[0] === 'env') {
            return true;
        }

        return false;
    }

    /**
     * Attempts to change the env() function call arguments.
     *
     * If the env() function call does not currently have a default argument, it will be
     * set instead of over-writing the environment key value that is being checked for.
     *
     * @param FuncCall $funcCall The function call.
     * @param string $newValue The new value.
     */
    protected function replaceEnvCallDefault(FuncCall $funcCall, $newValue)
    {
        $argCount = count($funcCall->args);

        if ($argCount === 2) {
            $lastArgIndex = count($funcCall->args) - 1;
            $funcCall->args[$lastArgIndex]->value = $newValue;
        } elseif ($argCount === 1) {
            $funcArg = new Node\Arg($newValue);

            $funcCall->args[] = $funcArg;
        }
    }

    /**
     * Retrieves the file contents and parses the document.
     *
     * @param string $path The file path.
     */
    public function open($path)
    {
        $this->filePath = $path;
        $this->contents = file_get_contents($this->filePath);
        @$this->parse();
    }

    /**
     * Parses the configuration document.
     */
    private function parse()
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());
        $this->oldStmts = $this->parser->parse($this->contents);
        $this->oldTokens = $this->lexer->getTokens();
        $traverser->addVisitor(new CreateParentVisitor());
        $parentStatements = $traverser->traverse($this->oldStmts);
        $refTraverser = new NodeTraverser();
        $configNodeVisitor = new ConfigNodeVisitor();
        $configNodeVisitor->setAnalyzer($this);
        $refTraverser->addVisitor($configNodeVisitor);
        $refTraverser->traverse($parentStatements);
        $this->newStmts = $parentStatements;
        $this->rootNode = $this->newStmts[0]->expr;
    }

    /**
     * Converts the mutated configuration back to a PHP document.
     *
     * @return string
     */
    public function dumpConfig()
    {
        $printer = new Printer([
            'shortArraySyntax' => true
        ]);

        return $printer->printFormatPreserving($this->newStmts, $this->oldStmts, $this->oldTokens);
    }

}