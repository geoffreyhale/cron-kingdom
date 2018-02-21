<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceAction;
use CronkdBundle\Entity\Resource\ResourceActionInput;
use CronkdBundle\Exceptions\KingdomDoesNotHaveResourceException;
use CronkdBundle\Model\ResourceActionCostOverview;
use Doctrine\ORM\EntityManagerInterface;

class ResourceActionService
{
    /** @var EntityManagerInterface  */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Kingdom $kingdom
     * @param ResourceAction $action
     * @return bool
     * @throws KingdomDoesNotHaveResourceException
     */
    public function calculateCanProduceResource(Kingdom $kingdom, ResourceAction $action)
    {
        /** @var ResourceActionInput $resourceActionInput */
        foreach ($action->getInputs() as $resourceActionInput) {
            $kingdomResource = $this->getKingdomResource($kingdom, $resourceActionInput->getResource());
            if ($kingdomResource->getResource() != $resourceActionInput->getResource()) {
                continue;
            }

            switch ($resourceActionInput->getInputStrategy()) {
                case ResourceActionInput::INPUT_STATIC_STRATEGY:
                    $hasEnoughQuantity = $kingdomResource->getQuantity() > $resourceActionInput->getInputQuantity();
                    break;
                case ResourceActionInput::INPUT_ADDITIVE_STRATEGY:
                    $outputKingdomResource = $this->getKingdomResource($kingdom, $resourceActionInput->getResourceAction()->getResource());
                    $hasEnoughQuantity = $kingdomResource->getQuantity() > $outputKingdomResource->getQuantity();
                    break;
                case ResourceActionInput::INPUT_EXPONENTIAL_STRATEGY:
                    $outputKingdomResource = $this->getKingdomResource($kingdom, $resourceActionInput->getResourceAction()->getResource());
                    $inputCost = $this->calculateExponentialCost($this->getExponentialTable(), $outputKingdomResource->getQuantity());
                    $hasEnoughQuantity = $kingdomResource->getQuantity() > $inputCost;
                    break;
                case ResourceActionInput::INPUT_REQUIREMENT_STRATEGY:
                    $outputKingdomResource = $this->getKingdomResource($kingdom, $resourceActionInput->getResourceAction()->getResource());
                    $total = $outputKingdomResource->getQuantity();
                    $total += $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $resourceActionInput->getResourceAction()->getResource());
                    $hasEnoughQuantity = $kingdomResource->getQuantity() > $total;
                    break;
                default:
                    $hasEnoughQuantity = false;
            }

            if (!$hasEnoughQuantity) {
                return false;
            }
        }

        return true;
    }

    public function calculateCostGivenQuantity(
        KingdomResource $inputResource,
        ResourceActionInput $input,
        int $quantity
    ) {
        $kingdom = $inputResource->getKingdom();
        $outputKingdomResource = $this->getKingdomResource($kingdom, $input->getResourceAction()->getResource());
        switch ($input->getInputStrategy()) {
            case ResourceActionInput::INPUT_STATIC_STRATEGY:
                $inputCost = $quantity * $input->getInputQuantity();
                break;
            case ResourceActionInput::INPUT_ADDITIVE_STRATEGY:
                $inputCost = 0;
                $requiredQuantityForOutput = $quantity;
                $outputCurrentQuantity = $outputKingdomResource->getQuantity();
                while ($requiredQuantityForOutput > 0) {
                    $inputCost += (int) $outputCurrentQuantity;
                    $requiredQuantityForOutput--;
                    $outputCurrentQuantity++;
                }
                break;
            case ResourceActionInput::INPUT_EXPONENTIAL_STRATEGY:
                $requiredQuantityForOutput = $quantity;
                $outputCurrentQuantity = $outputKingdomResource->getQuantity();
                $table = $this->getExponentialTable();
                $inputCost = 0;
                while ($requiredQuantityForOutput > 0) {
                    $inputUnitCost = $this->calculateExponentialCost($table, $outputCurrentQuantity);
                    $requiredQuantityForOutput--;
                    $inputCost += $inputUnitCost;
                }
                break;
            case ResourceActionInput::INPUT_REQUIREMENT_STRATEGY:
                $inputCost = $outputKingdomResource->getQuantity();
                break;
            default:
                throw new \LogicException('Unknown strategy for calculating quantity');
        }

        return $inputCost;
    }

    public function calculateMaxProduceableQuantity(
        KingdomResource $inputResource,
        ResourceActionInput $input
    ) {
        $kingdom = $inputResource->getKingdom();
        $outputKingdomResource = $this->getKingdomResource($kingdom, $input->getResourceAction()->getResource());
        $inputKingdomResource = $this->getKingdomResource($kingdom, $input->getResource());
        switch ($input->getInputStrategy()) {
            case ResourceActionInput::INPUT_STATIC_STRATEGY:
                $inputUnitCost = $input->getInputQuantity();
                $quantity = 0 == $inputUnitCost ? 0 : (int) floor($inputKingdomResource->getQuantity() / $inputUnitCost);
                break;
            case ResourceActionInput::INPUT_ADDITIVE_STRATEGY:
                $quantity = $inputKingdomResource->getQuantity();
                $outputKingdomQuantity = $outputKingdomResource->getQuantity();
                $cost = $outputKingdomQuantity;
                $outputQuantity = 0;
                while ($quantity > 0) {
                    $quantity -= $cost;
                    $cost++;
                    if ($quantity > 0) {
                        $outputQuantity++;
                    }
                }
                $quantity = $outputQuantity;
                break;
            case ResourceActionInput::INPUT_EXPONENTIAL_STRATEGY:
                $quantity = $inputKingdomResource->getQuantity();
                $outputKingdomQuantity = $outputKingdomResource->getQuantity();
                $table = $this->getExponentialTable();
                $cost = $this->calculateExponentialCost($table, $outputKingdomQuantity);
                $outputQuantity = 0;
                while ($quantity > 0) {
                    $quantity -= $cost;
                    if ($quantity > 0) {
                        $outputQuantity++;
                        $outputKingdomQuantity++;
                        $cost = $this->calculateExponentialCost($table, $outputKingdomQuantity);
                    }
                }
                $quantity = $outputQuantity;
                break;
            case ResourceActionInput::INPUT_REQUIREMENT_STRATEGY:
                $total = $outputKingdomResource->getQuantity();
                $quantity = $inputResource->getQuantity() - $total;
                break;
            default:
                throw new \LogicException('Unknown strategy for calculating quantity');
        }

        return $quantity;
    }

    /**
     * @param Kingdom $kingdom
     * @param ResourceAction $action
     * @return ResourceActionCostOverview
     */
    public function getCostsForResource(Kingdom $kingdom, ResourceAction $action)
    {
        $inputCosts = new ResourceActionCostOverview();

        /** @var ResourceActionInput $resourceActionInput */
        foreach ($action->getInputs() as $resourceActionInput) {
            $inputKingdomResource = $this->em->getRepository(KingdomResource::class)->findOneBy([
                'kingdom' => $kingdom,
                'resource' => $resourceActionInput->getResource(),
            ]);
            $quantity = $this->calculateMaxProduceableQuantity($inputKingdomResource, $resourceActionInput);
            $cost = $this->calculateCostGivenQuantity($inputKingdomResource, $resourceActionInput, 1);
            $maxCost = $this->calculateCostGivenQuantity($inputKingdomResource, $resourceActionInput, $quantity);
            $inputCosts->addInput($inputKingdomResource->getResource(), $cost, $maxCost, $quantity, $resourceActionInput->getInputStrategy());
        }

        return $inputCosts;
    }

    /**
     * @return array
     */
    public function getExponentialTable()
    {
        $table = [];

        $i = 1;
        while ($i <= 10) {
            $table[] = [
                'quantity' => pow(2, $i),
                'cost'     => array_sum(range(1, $i)),
            ];
            $i++;
        }

        return $table;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return KingdomResource
     * @throws KingdomDoesNotHaveResourceException
     */
    private function getKingdomResource(Kingdom $kingdom, Resource $resource)
    {
        foreach ($kingdom->getResources() as $kingdomResource) {
            if ($kingdomResource->getResource()->getName() == $resource->getName()) {
                return $kingdomResource;
            }
        }

        throw new KingdomDoesNotHaveResourceException($resource->getName());
    }

    /**
     * @param array $table
     * @param int $quantity
     * @return int
     */
    private function calculateExponentialCost(array $table, int $quantity)
    {
        $cost = 1;

        $i = 0;
        while ($i < count($table)) {
            if ($quantity >= $table[$i]['quantity']) {
                $cost = $table[$i]['cost'];
            }
            $i++;
        }

        return $cost;
    }
}