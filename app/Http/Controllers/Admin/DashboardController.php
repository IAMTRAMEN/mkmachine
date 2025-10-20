<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Component;
use App\Models\Quote;
use App\Models\CompatibilityRule;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic counts
        $stats = [
            'machines_count' => Machine::count(),
            'components_count' => Component::count(),
            'quotes_count' => Quote::count(),
            'rules_count' => CompatibilityRule::count(),
            'categories_count' => Category::count(),
        ];

        // Quote statistics
        $quoteStats = [
            'total' => Quote::count(),
            'draft' => Quote::where('status', 'draft')->count(),
            'sent' => Quote::where('status', 'sent')->count(),
            'accepted' => Quote::where('status', 'accepted')->count(),
            'rejected' => Quote::where('status', 'rejected')->count(),
            'total_revenue' => Quote::sum('total_price'),
            'average_quote_value' => Quote::avg('total_price') ?? 0,
        ];

        // Recent quotes with more details
        $recentQuotes = Quote::with('machine')
            ->latest()
            ->take(8)
            ->get();

        // Most popular components
        $popularComponents = DB::table('quote_components')
            ->join('components', 'quote_components.component_id', '=', 'components.id')
            ->join('categories', 'components.category_id', '=', 'categories.id')
            ->select(
                'components.name',
                'components.code',
                'categories.name as category_name',
                DB::raw('COUNT(quote_components.component_id) as usage_count'),
                DB::raw('SUM(quote_components.unit_price) as total_revenue')
            )
            ->groupBy('quote_components.component_id', 'components.name', 'components.code', 'categories.name')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        // Machine usage statistics
        $machineUsage = DB::table('quotes')
            ->join('machines', 'quotes.machine_id', '=', 'machines.id')
            ->select(
                'machines.name',
                'machines.display_name',
                DB::raw('COUNT(quotes.machine_id) as quote_count'),
                DB::raw('SUM(quotes.total_price) as total_revenue')
            )
            ->groupBy('quotes.machine_id', 'machines.name', 'machines.display_name')
            ->orderByDesc('quote_count')
            ->get();

        // Monthly quote trends (last 6 months)
        $monthlyTrends = Quote::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as quote_count'),
            DB::raw('SUM(total_price) as monthly_revenue')
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

        // Component statistics by category
        $componentStats = Category::withCount(['components as total_components'])
            ->withCount(['components as active_components' => function($query) {
                $query->where('active', true);
            }])
            ->with(['components' => function($query) {
                $query->orderBy('price', 'desc')->limit(3);
            }])
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'quoteStats',
            'recentQuotes',
            'popularComponents',
            'machineUsage',
            'monthlyTrends',
            'componentStats'
        ));
    }
}