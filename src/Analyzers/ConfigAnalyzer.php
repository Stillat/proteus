<?php

namespace Stillat\Proteus\Analyzers;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser\Php7;
use Stillat\Proteus\Analyzers\FunctionHandlers\LaravelEnv;
use Stillat\Proteus\Analyzers\FunctionHandlers\SimpleFunctionHandler;
use Stillat\Proteus\Document\Printer;
use Stillat\Proteus\Visitors\ConfigNodeVisitor;
use Stillat\Proteus\Visitors\CreateParentVisitor;
use Stillat\Proteus\Writers\StringWriter;

/**
 * Class ConfigAnalyzer
 *
 *
 *
 * @package Stillat\Proteus\Analyzers
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
     * A list of all the original source nodes, before updates.
     *
     * @var array
     */
    private $sourceNodes = [];

    /**
     * The root node, if any.
     *
     * @var null|Node
     */
    private $rootNode = null;

    /**
     * The FunctionHandler instance.
     *
     * @var FunctionHandler
     */
    private $functionHandler = null;

    /**
     * The current values.
     *
     * @var array
     */
    private $currentValues = [];

    public function __construct()
    {
        $this->functionHandler = new FunctionHandler();

        // Register some default handlers.
        $this->functionHandler->addHandler('env', new LaravelEnv());
        $this->functionHandler->addHandler('resource_path', new SimpleFunctionHandler());
        $this->functionHandler->addHandler('app_path', new SimpleFunctionHandler());
        $this->functionHandler->addHandler('base_path', new SimpleFunctionHandler());
        $this->functionHandler->addHandler('config_path', new SimpleFunctionHandler());
        $this->functionHandler->addHandler('database_path', new SimpleFunctionHandler());
        $this->functionHandler->addHandler('public_path', new SimpleFunctionHandler());
        $this->functionHandler->addHandler('storage_path', new SimpleFunctionHandler());

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
     * Sets the current configuration values.
     *
     * @param array $values The values.
     */
    public function setValues($values)
    {
        $this->currentValues = $values;
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
        // TODO: Review if this can be removed completely.
        // if ($node instanceof Array_) {
        // $node = $this->checkForNestedUselessArrays($node);
        // }

        $stringWriter = new StringWriter();
        $itemKey = $stringWriter->write($key);

        return new ArrayItem($node, $itemKey);
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
                        $mergeItemKeyValue = null;

                        if ($mergeItem->key !== null && $mergeItem->key instanceof String_) {
                            $mergeItemKeyValue = $mergeItem->key->value;
                        }

                        if ($mergeItem->value instanceof Array_ && $mergeItemKeyValue === null) {
                            foreach ($mergeItem->value->items as $subMergeItem) {
                                $currentNode->value->items[] = $subMergeItem;
                            }
                        } else {
                            // Check if this array already has a value with the same key.

                            $didReplace = false;
                            foreach ($currentNode->value->items as $checkNode) {
                                if ($checkNode instanceof ArrayItem) {
                                    $checkNodeKeyValue = null;

                                    if ($checkNode->key !== null && $checkNode->key instanceof String_) {
                                        $checkNodeKeyValue = $checkNode->key->value;
                                    }

                                    if ($checkNodeKeyValue !== null && $mergeItemKeyValue === $checkNodeKeyValue) {
                                        $checkNode->value = $mergeItem->value;
                                        $didReplace = true;
                                        break;
                                    }
                                }
                            }

                            if ($didReplace === false) {
                                $currentNode->value->items[] = $mergeItem;
                            }
                        }
                    }
                } else {
                    $currentNode->value = $node;
                }
                return;
            }

            $currentCheckValue = null;

            if ($node instanceof String_) {
                $currentCheckValue = $node->value;
            }

            if ($currentNode->value instanceof Node\Expr\Cast\Bool_) {
                $boolCastNode = $currentNode->value;

                if ($this->shouldProceedWithFunctionRewrite($key, $currentCheckValue)) {
                    $this->functionHandler->handle($boolCastNode->expr, $currentNode, $node, $key);
                }
                return;
            } elseif ($currentNode->value instanceof Node\Expr\Cast\String_) {
                $stringCastNode = $currentNode->value;

                if ($this->shouldProceedWithFunctionRewrite($key, $currentCheckValue)) {
                    $this->functionHandler->handle($stringCastNode->expr, $currentNode, $node, $key);
                }
                return;
            } elseif ($currentNode->value instanceof Node\Expr\Cast\Int_) {
                $intCastNode = $currentNode->value;

                if ($this->shouldProceedWithFunctionRewrite($key, $currentCheckValue)) {
                    $this->functionHandler->handle($intCastNode->expr, $currentNode, $node, $key);
                }
                return;
            } elseif ($currentNode->value instanceof Node\Expr\Cast\Double) {
                $doubleCastNode = $currentNode->value;

                if ($this->shouldProceedWithFunctionRewrite($key, $currentCheckValue)) {
                    $this->functionHandler->handle($doubleCastNode->expr, $currentNode, $node, $key);
                }
                return;
            } elseif ($currentNode->value instanceof FuncCall) {
                if ($this->shouldProceedWithFunctionRewrite($key, $currentCheckValue)) {
                    $this->functionHandler->handle($currentNode->value, $currentNode, $node, $key);
                }
                return;
            } else {
                // Replace node value.
                $currentNode->value = $node;
            }
        }
    }

    /**
     * Guards against resupplying an already configured value in env() calls.
     *
     * @param string $key The configuration key.
     * @param mixed $checkValue The check value.
     * @return bool
     */
    private function shouldProceedWithFunctionRewrite($key, $checkValue)
    {
        if ($checkValue === null) {
            return true;
        }

        if (array_key_exists($key, $this->currentValues)) {
            if ($this->currentValues[$key] === $checkValue) {
                return false;
            }
        }

        return true;
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
     * Replaces a node's value with the provided new value.
     *
     * @param string $key The key.
     * @param mixed $newValue The value to insert.
     */
    public function replaceNode($key, $newValue)
    {
        $currentNode = $this->nodeMapping[$key];

        if ($currentNode instanceof ArrayItem) {
            $currentNode->value = $newValue;
        }
    }

    /**
     * Removes the key and its associated value from the configuration.
     *
     * @param string $key The key to remove.
     * @return bool
     */
    public function removeNode($key)
    {
        $foundNode = false;

        $currentNode = $this->nodeMapping[$key];

        if ($currentNode instanceof ArrayItem) {
            $parent = $this->getParentKey($key);


            if (mb_strlen($parent) > 0 && $this->hasNode($parent)) {
                $parentNode = $this->nodeMapping[$parent];
                $relativeKey = $this->getLastKeySegment($key);

                if ($parentNode instanceof ArrayItem && $parentNode->value instanceof Array_) {
                    $newItems = [];

                    /** @var ArrayItem $valueItem */
                    foreach ($parentNode->value->items as $valueItem) {
                        $valueItemKey = $valueItem->key->value;

                        if ($valueItemKey === null || $valueItemKey !== $relativeKey) {
                            $newItems[] = $valueItem;
                        } else {
                            $foundNode = true;
                        }
                    }

                    // Reassign the value items, without the node to remove.
                    $parentNode->value->items = $newItems;
                }
            } else {
                $newItems = [];

                /** @var ArrayItem $valueItem */
                foreach ($this->rootNode->items as $valueItem) {
                    $valueItemKey = $valueItem->key->value;

                    if ($valueItemKey === null || $valueItemKey !== $key) {
                        $newItems[] = $valueItem;
                    } else {
                        $foundNode = true;
                    }
                }

                // Reassign the value items, without the node to remove.
                $this->rootNode->items = $newItems;
            }
        }

        return $foundNode;
    }

    protected function getParentKey($key)
    {
        $parts = explode('.', $key);
        array_pop($parts);

        return implode('.', $parts);
    }

    protected function getLastKeySegment($key)
    {
        $parts = explode('.', $key);

        return array_pop($parts);
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

        $this->sourceNodes = array_keys($this->nodeMapping);
    }

    /**
     * Returns a list of all source node keys.
     *
     * @return array
     */
    public function getSourceNodeKeys()
    {
        return $this->sourceNodes;
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
     * @deprecated
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
            } else {
                $newNodes[] = $item;
            }
        }

        $node->items = array_values($newNodes);

        return $node;
    }

}
