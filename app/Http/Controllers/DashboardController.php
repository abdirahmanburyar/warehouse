<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dashboardData = [
            'summary' => [
                [
                    'label' => 'Monthly Beginning Balance',
                    'value' => 25000,
                    'unit' => 'units',
                    'date' => '01/11/2024',
                    'color' => 'gray',
                ],
                [
                    'label' => 'Monthly Received Items',
                    'value' => 10000,
                    'unit' => 'units',
                    'date' => '01/11/2024',
                    'color' => 'rose',
                ],
                [
                    'label' => 'Monthly Issued Items',
                    'value' => 8500,
                    'unit' => 'units',
                    'date' => '01/11/2024',
                    'color' => 'cyan',
                ],
                [
                    'label' => 'Average Monthly Consumption',
                    'value' => 25000,
                    'unit' => 'units',
                    'date' => '01/11/2024',
                    'color' => 'amber',
                ],
            ],
            'tasks' => [
                'Remind your Facilities about 31 not-submitted LIMIS Reports',
                'You’re waiting for LIMIS Reports From 17 Facilities',
                'Monitor delivery status for 12 incoming orders.',
                'Expected within 5 days.'
            ],
            'recommended_actions' => [
                'Stock levels for Paracetamol 500mg Tab 30% are below the recommended threshold. Consider placing a reorder for 1,200 units to avoid stock-out.',
                'Gil-Nugal Hospital has excess stock of Albendazole 400mg Tab. Transfer 500 units to Facility B, where demand is higher.'
            ],
            'product_status' => [
                [ 'label' => 'Paracetamol 500mg Tab', 'percent' => 30, 'approved' => 53, 'rejected' => 8, 'pending' => 6, 'in_process' => 43, 'dispatched' => 735, 'delivered' => 725 ],
                [ 'label' => 'Ceftriaxone 1g Injection', 'percent' => 60 ],
                [ 'label' => 'ORS', 'percent' => 80 ],
                [ 'label' => 'Penicillin V 5MIU Injection', 'percent' => 20 ],
                [ 'label' => 'Amoxicillin 500mg Cap', 'percent' => 50 ],
                [ 'label' => 'Gloves', 'percent' => 44 ],
                [ 'label' => 'Folic acid 5mg Tab', 'percent' => 80 ],
                [ 'label' => 'Albendazole 400mg Tab', 'percent' => 100 ],
                [ 'label' => 'Tetracycline Ointment', 'percent' => 85 ],
            ],
        ];
        return inertia('Dashboard', [
            'dashboardData' => $dashboardData
        ]);
    }
}
