<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Representative;
use App\Models\Ciclo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $productCount = Product::count();
        $representativeCount = Representative::count();
        $currentMonthAssignments = Ciclo::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return view('dashboard', [
            'productCount' => $productCount,
            'representativeCount' => $representativeCount,
            'currentMonthAssignments' => $currentMonthAssignments
        ]);
    }
}
