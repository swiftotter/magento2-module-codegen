<?php

/**
 * @copyright Copyright © 2021 Orba. All rights reserved.
 * @author    info@orba.co
 */

declare(strict_types=1);

namespace Orba\Magento2Codegen\Service\PropertyValueCollector;

use InvalidArgumentException;
use Orba\Magento2Codegen\Model\ArrayProperty;
use Orba\Magento2Codegen\Model\InputPropertyInterface;
use Orba\Magento2Codegen\Model\PropertyInterface;
use Orba\Magento2Codegen\Service\IO;
use Orba\Magento2Codegen\Service\PropertyDependencyChecker;
use Orba\Magento2Codegen\Util\PropertyBag;
use RuntimeException;

use function is_null;
use function sprintf;

class ArrayCollector extends AbstractInputCollector
{
    private ?CollectorFactory $collectorFactory = null;
    private PropertyDependencyChecker $propertyDependencyChecker;

    public function __construct(IO $io, PropertyDependencyChecker $propertyDependencyChecker)
    {
        parent::__construct($io);
        $this->propertyDependencyChecker = $propertyDependencyChecker;
    }

    public function setCollectorFactory(CollectorFactory $collectorFactory): void
    {
        $this->collectorFactory = $collectorFactory;
    }

    protected function validateProperty(PropertyInterface $property): void
    {
        if (!$property instanceof ArrayProperty) {
            throw new InvalidArgumentException('Invalid property type.');
        }
    }

    protected function collectValueFromInput(InputPropertyInterface $property, PropertyBag $propertyBag): array
    {
        if (is_null($this->collectorFactory)) {
            throw new RuntimeException('Collector factory is unset.');
        }
        /** @var ArrayProperty $property */
        $items = [];
        $i = 0;

        $promptPattern = 'Do you want to add an item to "%s" array?';
        while (
            $this->isQuestionForced($property, $i) || $this->io->getInstance()->confirm(
                sprintf($promptPattern, $property->getName()),
                true
            )
        ) {
            $item = [];
            foreach ($property->getChildren() as $child) {
                $collector = $this->collectorFactory->create($child);
                $questionPrefix = $this->questionPrefix . $property->getName() . '.' . $i . '.';
                if ($collector instanceof AbstractInputCollector) {
                    $collector->setQuestionPrefix($questionPrefix);
                }
                if (
                    $child instanceof InputPropertyInterface
                    && $this->areDependencyConditionsMet($child, $propertyBag, $item)
                ) {
                    continue;
                }
                $item[$child->getName()] = $collector->collectValue($child, $propertyBag);
            }
            $items[] = $item;
            $i++;
            $promptPattern = 'Do you want to add another item to "%s" array?';
        };

        return $items;
    }

    private function isQuestionForced(InputPropertyInterface $property, int $i): bool
    {
        if ($i > 0) {
            return false;
        }
        return $property->getRequired();
    }

    private function areDependencyConditionsMet(
        InputPropertyInterface $property,
        PropertyBag $propertyBag,
        array $item
    ): bool {
        return !$this->propertyDependencyChecker->areRootConditionsMet($property, $propertyBag)
            || !$this->propertyDependencyChecker->areScopeConditionsMet('item', $property, $item);
    }
}
