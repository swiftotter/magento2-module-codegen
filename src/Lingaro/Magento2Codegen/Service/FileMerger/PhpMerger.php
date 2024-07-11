<?php

/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Magento2Codegen\Service\FileMerger;

use Exception;
use Lingaro\Magento2Codegen\Service\FileMerger\PhpMerger\NodeTreeFactory;
use Lingaro\Magento2Codegen\Service\FileMerger\PhpParser\NodeWrapper;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

class PhpMerger extends AbstractMerger implements MergerInterface
{
    protected bool $experimental = true;
    private Parser $parser;
    private PrettyPrinter\Standard $printer;
    private NodeWrapper $wrapper;
    private NodeTreeFactory $treeFactory;

    public function __construct(NodeTreeFactory $nodeTreeFactory)
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->printer = new PrettyPrinter\Standard();
        $this->wrapper = new NodeWrapper();
        $this->treeFactory = $nodeTreeFactory;
    }

    public function merge(string $oldContent, string $newContent): string
    {
        $tree = $this->treeFactory->create();
        $this->wrapper->wrap(
            $this->parser->parse($oldContent),
            $this->parser->parse($newContent)
        );

        try {
            $tree->grow($this->wrapper)->resolve();
        } catch (Exception $e) {
            throw new Exception("An error occurred while merging:\n" . $e->getMessage());
        }

        return $this->printer->prettyPrintFile($this->wrapper->unwrap());
    }
}
