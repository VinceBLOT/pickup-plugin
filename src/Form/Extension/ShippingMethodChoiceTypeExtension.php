<?php

declare(strict_types=1);

namespace MagentixPickupPlugin\Form\Extension;

use MagentixPickupPlugin\Shipping\Calculator\CalculatorInterface as PickupCalculatorInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Core\Model\ShippingMethod;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{

    /**
     * @var ServiceRegistryInterface $calculatorRegistry
     */
    private $calculatorRegistry;

    /**
     * @param ServiceRegistryInterface $calculatorRegistry
     */
    public function __construct(
        ServiceRegistryInterface $calculatorRegistry
    ) {
        $this->calculatorRegistry = $calculatorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault(
            'choice_attr',
            function ($choiceValue, $key, $value) {
                /** @var ShippingMethod $choiceValue */
                $calculatorName = $choiceValue->getCalculator();

                /** @var PickupCalculatorInterface $calculator */
                $calculator = $this->calculatorRegistry->get($calculatorName);

                $attr = [];
                if ($calculator instanceof PickupCalculatorInterface) {
                    $attr['class'] = 'pickup';
                }

                return $attr;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return ShippingMethodChoiceType::class;
    }
}
