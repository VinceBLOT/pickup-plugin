<?php

declare(strict_types=1);

namespace MagentixPickupPlugin\EventListener;

use MagentixPickupPlugin\Shipping\Calculator\CalculatorInterface as PickupCalculatorInterface;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Core\Model\Order;

class PickupListener
{
    /**
     * @var ServiceRegistryInterface $calculatorRegistry
     */
    private $calculatorRegistry;

    /**
     * @var ShippingMethodRepositoryInterface $shippingMethodRepository
     */
    private $shippingMethodRepository;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param ServiceRegistryInterface $calculatorRegistry
     * @param ShippingMethodRepositoryInterface $shippingMethodRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     */
    public function __construct(
        ServiceRegistryInterface $calculatorRegistry,
        ShippingMethodRepositoryInterface $shippingMethodRepository,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->calculatorRegistry = $calculatorRegistry;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getPickupAddress(Order $order): array
    {
        $shipment = $order->getShipments()->current();
        $pickupAddress = [];

        if ($shipment->getPickupId()) {
            $current = $this->shipmentRepository->find($shipment->getId());
            if ($current) {
                $method = $this->shippingMethodRepository->find($current->getMethod()->getId());
                $calculator = $this->calculatorRegistry->get($method->getCalculator());

                if ($calculator instanceof PickupCalculatorInterface) {
                    $pickup = $calculator->getPickupAddress(
                        $shipment->getPickupId(),
                        $method->getConfiguration()
                    );

                    if (!$pickup['error']) {
                        $pickupAddress = $pickup['pickup'];
                    }
                }
            }
        }

        return $pickupAddress;
    }
}
