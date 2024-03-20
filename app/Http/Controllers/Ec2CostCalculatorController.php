<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Testing\Exceptions\InvalidArgumentException;
use Illuminate\Http\Request;

class Ec2CostCalculatorController extends Controller
{
    public function calculateCost(Request $request, string $type)
    {
        $instanceType = "t3-micro";
        $usageHours = floatval($request->query('usageHours'));

        $type = $request->route('type'); // on-demand or reserved

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
        $pricePerHour = 0.01;
        return $usageHours * $pricePerHour;
    }
}

class ReservedInstanceCalculator implements Ec2CostCalculator
{
    public function calculateCost(string $instanceType, float $usageHours): float
    {
        $reservedCost = 10.0;
        return $reservedCost * log($usageHours) / 2;
    }
}
