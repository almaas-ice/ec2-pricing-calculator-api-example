<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Testing\Exceptions\InvalidArgumentException;
use Illuminate\Http\Request;

class Ec2CostCalculatorController extends Controller
{
    public function calculateCost(Request $request, string $type)
    {
        $instanceType = "t-micro"; // Instance type (e.g., t2.micro)
        $usageHours = floatval($request->query('usageHours'));

        $type = $request->route('type'); // On-Demand or Reserved

        $calculator = $this->getCalculator($type);

        $cost = $calculator->calculateCost($instanceType, $usageHours);

        return [
            "instanceType" => $instanceType,
            "usageHours" => $usageHours,
            "costType" => $type,
            "cost" => $cost,
        ];
    }

    private function getCalculator(string $type): Ec2CostCalculator
    {
        $map = [
            'on-demand' => new OnDemandCostCalculator(),
            'reserved' => new ReservedInstanceCalculator(),
        ];

        if (!array_key_exists($type, $map)) {
            throw new InvalidArgumentException("Invalid EC2 pricing model: $type");
        }

        return $map[$type];
    }
}

interface Ec2CostCalculator
{
    public function calculateCost(string $instanceType, float $usageHours): float;
}

class OnDemandCostCalculator implements Ec2CostCalculator
{
    public function calculateCost(string $instanceType, float $usageHours): float
    {
        // Replace with logic to fetch on-demand pricing from AWS API
        // based on instanceType and region
        $pricePerHour = 0.01; // Placeholder price
        return $usageHours * $pricePerHour;
    }
}

class ReservedInstanceCalculator implements Ec2CostCalculator
{
    public function calculateCost(string $instanceType, float $usageHours): float
    {
        // Replace with logic to calculate cost based on reserved instance purchase details
        // (e.g., upfront cost, discount percentage, etc.) for the specific instanceType
        $reservedCost = 10.0; // Placeholder cost
        return $reservedCost * log($usageHours) / 2;
    }
}
