<?php

namespace Stillat\WolfPack;

use Exception;
use Stillat\WolfPack\Analyzers\ArrayAnalyzer;
use Stillat\WolfPack\Analyzers\ConfigAnalyzer;
use Stillat\WolfPack\Document\Transformer;
use Stillat\WolfPack\Writers\TypeWriter;

/**
 * Class ConfigUpdater
 *
 * Provides utilities and features to update Laravel-style configuration files.
 *
 * This class is a wrapper around the ConfigAnalyzer and the ArrayAnalyzer.
 *
 * @package Stillat\WolfPack
 * @since 1.0.0
 */
class ConfigUpdater
{

    /**
     * The ConfigAnalyzer instance.
     *
     * @var ConfigAnalyzer
     */
    private $analyzer = null;

    /**
     * The ArrayAnalyzer instance.
     *
     * @var ArrayAnalyzer
     */
    private $arrayAnalyzer = null;

    /**
     * The Transformer instance.
     *
     * @var Transformer
     */
    private $transformer = null;

    public function __construct()
    {
        $this->transformer = new Transformer();
        $this->arrayAnalyzer = new ArrayAnalyzer();
        $this->analyzer = new ConfigAnalyzer();
    }

    /**
     * Opens the provided file and parses the configuration values.
     *
     * @param string $filePath The file to open.
     * @throws Exception
     */
    public function open($filePath)
    {
        $existingConfigItems = require $filePath;
        $this->arrayAnalyzer->analyze($existingConfigItems);
        $this->analyzer->open($filePath);
    }

    /**
     * Attempts to apply the requested changes to the existing configuration values.
     *
     * @param array $changes The changes to apply to the existing configuration.
     * @throws Exception
     */
    public function update(array $changes)
    {
        $changesToMake = $this->arrayAnalyzer->getChanges($changes);

        foreach ($changesToMake->insertions as $insert) {
            $valuesToInsert = TypeWriter::write($changes[$insert]);


            if ($this->analyzer->hasNode($insert)) {
                $this->analyzer->replaceNodeValue($insert, $valuesToInsert);
            } else {

                if ($this->arrayAnalyzer->isCompound($insert)) {
                    $insertionPoint = $this->arrayAnalyzer->getInsertionPoint($insert);

                    if ($insertionPoint === null) {
                        $root = $this->arrayAnalyzer->getAbsoluteRoot($insert);
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($insert);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, $changes[$insert]);
                        $valuesToInsert = TypeWriter::write($structure);

                        $this->analyzer->insertValuesAtNode($root, $valuesToInsert);
                    } else {
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($insert);
                        $newKeyValue = array_pop($components);
                        $newVal = [$newKeyValue => $changes[$insert]];

                        array_pop($components);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, $newVal);
                        $valuesToInsert = TypeWriter::write($structure);

                        $this->analyzer->replaceNodeValue($insertionPoint, $valuesToInsert);
                    }
                    return;
                } else {
                    $this->analyzer->insertValuesAtNode($insert, $valuesToInsert);
                }
            }
        }

        foreach ($changesToMake->updates as $update) {
            $constructedValue = TypeWriter::write($changes[$update]);

            if ($this->analyzer->hasNode($update)) {
                if ($this->analyzer->isNodeArray($update) && is_array($changes[$update]) === false) {
                    $this->analyzer->appendArrayItem($update, $constructedValue);
                } else {
                    $this->analyzer->replaceNodeValue($update, $constructedValue, true);
                }
            } else {
                // How may existing entires are at the desired level?
                $depthCount = $this->arrayAnalyzer->getDepthMatchCount($update);
                $insertPoint = $this->arrayAnalyzer->getInsertionPoint($update);
                $rootReplacement = $this->arrayAnalyzer->getAbsoluteRoot($update);

                if ($this->arrayAnalyzer->hasRoot($rootReplacement)) {
                    if ($depthCount === 0 && $insertPoint === null) {
                        // Most likely a replacement :)
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($update);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, $changes[$update]);
                        // Rewrite our construction.
                        $constructedValue = TypeWriter::write($structure);

                        $this->analyzer->replaceNodeValue($rootReplacement, $constructedValue);
                    } else {
                        $this->analyzer->insertRootValue($update, $constructedValue);
                    }
                } else {
                    if ($this->arrayAnalyzer->isCompound($update)) {
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($update);
                        $compoundStruct = $this->arrayAnalyzer->getCompoundStructure($components, $changes[$update]);

                        $constructedValue = TypeWriter::write($compoundStruct);

                        $this->analyzer->insertRootValue($rootReplacement, $constructedValue);
                    } else {
                        $this->analyzer->insertRootValue($rootReplacement, $constructedValue);
                    }
                }
            }
        }
    }

    /**
     * Converts the modified configuration to a consumable PHP document.
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->transformer->transform($this->analyzer->dumpConfig());
    }

}