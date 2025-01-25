<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        // Example: Pass data to the view if needed
        $reports = [
            ['name' => 'Report 1', 'description' => 'Monthly sales report', 'created_at' => '2025-01-01'],
            ['name' => 'Report 2', 'description' => 'Customer engagement report', 'created_at' => '2025-01-02'],
        ];

        return view('reports', compact('reports'));
    }
}
