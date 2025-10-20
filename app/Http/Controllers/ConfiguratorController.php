<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Add this line
use App\Models\Machine;
use App\Models\Category;
use App\Models\Component;
use App\Models\CompatibilityRule;
use App\Models\Quote;
use App\Models\SavedConfiguration;
use Barryvdh\DomPDF\Facade\Pdf;

class ConfiguratorController extends Controller
{
    /**
     * Show machine selection page
     */
    public function index()
    {
        $featuredMachines = Machine::where('active', true)
            ->orderBy('base_price')
            ->take(3)
            ->get();

        $allMachines = Machine::where('active', true)
            ->whereNotIn('id', $featuredMachines->pluck('id'))
            ->get();

        return view('configurator.index', compact('featuredMachines', 'allMachines'));
    }

    /**
     * Show configuration page for a specific machine
     */
    public function show($id)
    {
        $machine = Machine::with(['compatibleComponents.category'])->findOrFail($id);
        
        // Get categories with compatible components for this machine
        $categories = Category::with(['components' => function($query) use ($machine) {
            $query->where('active', true)
                  ->whereHas('machines', function($q) use ($machine) {
                      $q->where('machine_id', $machine->id)
                        ->where('compatible', true);
                  });
        }])->whereHas('components', function($query) use ($machine) {
            $query->where('active', true)
                  ->whereHas('machines', function($q) use ($machine) {
                      $q->where('machine_id', $machine->id)
                        ->where('compatible', true);
                  });
        })->orderBy('order')->get();

        // Get popular components for this machine (components frequently selected together)
        $popularComponents = $this->getPopularComponents($machine->id);

        return view('configurator.show', compact('machine', 'categories', 'popularComponents'));
    }


 public function saveConfiguration(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'components' => 'array',
            'components.*' => 'exists:components,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_company' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        $machine = Machine::find($request->machine_id);
        $selectedComponents = $request->components ?? [];

        // Calculate pricing
        $pricing = $this->calculatePricing($machine, $selectedComponents);

        // Create saved configuration
        $configuration = SavedConfiguration::create([
            'machine_id' => $machine->id,
            'selected_components' => $selectedComponents,
            'total_price' => $pricing['total'],
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_company' => $request->customer_company,
            'customer_phone' => $request->customer_phone,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'configuration_number' => $configuration->configuration_number,
            'message' => 'Configuration saved successfully! You can access it later using your configuration number: ' . $configuration->configuration_number
        ]);
    }
     public function loadConfiguration($configurationNumber)
    {
        $configuration = SavedConfiguration::where('configuration_number', $configurationNumber)
            ->where('status', 'active')
            ->firstOrFail();

        $machine = $configuration->machine;
        $categories = Category::with(['components' => function($query) use ($machine) {
            $query->where('active', true)
                  ->whereHas('machines', function($q) use ($machine) {
                      $q->where('machine_id', $machine->id)
                        ->where('compatible', true);
                  });
        }])->whereHas('components', function($query) use ($machine) {
            $query->where('active', true)
                  ->whereHas('machines', function($q) use ($machine) {
                      $q->where('machine_id', $machine->id)
                        ->where('compatible', true);
                  });
        })->orderBy('order')->get();

        $popularComponents = $this->getPopularComponents($machine->id);

        return view('configurator.show', compact('machine', 'categories', 'popularComponents', 'configuration'));
    }
/**
     * Compare two configurations
     */
    public function compare()
    {
        $machines = Machine::where('active', true)->get();
        return view('configurator.compare', compact('machines'));
    }

    /**
     * Get comparison data
     */
    public function getComparisonData(Request $request)
    {
        $request->validate([
            'machine1_id' => 'required|exists:machines,id',
            'machine2_id' => 'required|exists:machines,id',
            'components1' => 'array',
            'components2' => 'array',
            'components1.*' => 'exists:components,id',
            'components2.*' => 'exists:components,id',
        ]);

        $machine1 = Machine::find($request->machine1_id);
        $machine2 = Machine::find($request->machine2_id);

        $components1 = $request->components1 ?? [];
        $components2 = $request->components2 ?? [];

        $pricing1 = $this->calculatePricing($machine1, $components1);
        $pricing2 = $this->calculatePricing($machine2, $components2);

        return response()->json([
            'machine1' => [
                'name' => $machine1->display_name,
                'base_price' => $machine1->base_price,
                'pricing' => $pricing1,
                'specifications' => $machine1->specifications,
            ],
            'machine2' => [
                'name' => $machine2->display_name,
                'base_price' => $machine2->base_price,
                'pricing' => $pricing2,
                'specifications' => $machine2->specifications,
            ],
            'difference' => [
                'price' => $pricing1['total'] - $pricing2['total'],
                'installation_time' => $pricing1['installation_time'] - $pricing2['installation_time'],
            ]
        ]);
    }

    /**
     * Get popular components for a machine (based on quote history)
     */
    private function getPopularComponents($machineId)
    {
        return DB::table('quote_components')
            ->join('quotes', 'quote_components.quote_id', '=', 'quotes.id')
            ->join('components', 'quote_components.component_id', '=', 'components.id')
            ->where('quotes.machine_id', $machineId)
            ->select(
                'components.id',
                'components.name',
                'components.code',
                'components.description',
                'components.price',
                'components.category_id',
                DB::raw('COUNT(quote_components.component_id) as usage_count')
            )
            ->groupBy('components.id', 'components.name', 'components.code', 'components.description', 'components.price', 'components.category_id')
            ->orderByDesc('usage_count')
            ->limit(6)
            ->get()
            ->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'description' => $item->description,
                    'price' => $item->price,
                    'category_id' => $item->category_id,
                    'usage_count' => $item->usage_count
                ];
            });
    }


    /**
     * Validate configuration in real-time (AJAX)
     */
    public function validateConfiguration(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'components' => 'array',
            'components.*' => 'exists:components,id'
        ]);

        $machine = Machine::find($request->machine_id);
        $selectedComponents = $request->components ?? [];
        
        $validationResult = $this->validateComponents($selectedComponents);
        $pricing = $this->calculatePricing($machine, $selectedComponents);

        return response()->json([
            'valid' => $validationResult['valid'],
            'messages' => $validationResult['messages'],
            'pricing' => $pricing,
            'can_proceed' => $validationResult['valid']
        ]);
    }

    /**
     * Generate and show quote
     */
    public function generateQuote(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'components' => 'array',
            'components.*' => 'exists:components,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_company' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        $machine = Machine::find($request->machine_id);
        $selectedComponents = $request->components ?? [];

        // Final validation
        $validationResult = $this->validateComponents($selectedComponents);
        if (!$validationResult['valid']) {
            return redirect()->back()->withErrors(['components' => 'Selected components have compatibility issues.']);
        }

        // Calculate pricing
        $pricing = $this->calculatePricing($machine, $selectedComponents);

        // Create quote
        $quote = Quote::create([
            'machine_id' => $machine->id,
            'total_price' => $pricing['total'],
            'total_installation_time' => $pricing['installation_time'],
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_company' => $request->customer_company,
            'customer_phone' => $request->customer_phone,
            'configuration_data' => [
                'machine' => $machine->toArray(),
                'selected_components' => Component::whereIn('id', $selectedComponents)->get()->toArray(),
                'pricing_breakdown' => $pricing
            ],
            'notes' => $request->notes,
            'status' => 'draft'
        ]);

        // Attach components to quote
        foreach ($selectedComponents as $componentId) {
            $component = Component::find($componentId);
            $quote->components()->attach($componentId, [
                'unit_price' => $component->price,
                'installation_time' => $component->installation_time,
                'quantity' => 1
            ]);
        }

        return view('configurator.quote', [
    'quote' => $quote,
    'pricing' => $pricing
]);
    }

    /**
     * Export quote as PDF
     */
   /**
 * Export quote as PDF
 */
public function exportPdf($id)
{
    $quote = Quote::with(['machine', 'components.category'])->findOrFail($id);
    
    // Calculate pricing for the PDF
    $componentsPrice = $quote->components->sum('pivot.unit_price');
    $pricing = [
        'base_price' => $quote->machine->base_price,
        'components_price' => $componentsPrice,
        'total' => $quote->total_price,
        'installation_time' => $quote->total_installation_time
    ];

    $pdf = Pdf::loadView('configurator.pdf', compact('quote', 'pricing'))
             ->setPaper('a4')
             ->setOptions(['defaultFont' => 'sans-serif']);

    return $pdf->download("quote_{$quote->quote_number}.pdf");
}

    /**
     * Validate component compatibility
     */
    private function validateComponents(array $componentIds)
    {
        $rules = CompatibilityRule::with(['triggerComponent', 'targetComponent'])
                                 ->where('active', true)
                                 ->get();

        $messages = [];
        $isValid = true;

        foreach ($rules as $rule) {
            $result = $rule->check($componentIds);
            
            if ($result['applies'] && !$result['satisfied']) {
                $messages[] = [
                    'type' => $result['type'],
                    'message' => $result['message'],
                    'component_id' => $rule->target_component_id
                ];
                
                if ($rule->block_configuration) {
                    $isValid = false;
                }
            }
        }

        return [
            'valid' => $isValid,
            'messages' => $messages
        ];
    }

    /**
     * Calculate pricing and installation time
     */
    private function calculatePricing(Machine $machine, array $componentIds)
    {
        $components = Component::whereIn('id', $componentIds)->get();
        
        $componentsPrice = $components->sum('price');
        $installationTime = $components->sum('installation_time');
        $total = $machine->base_price + $componentsPrice;

        return [
            'base_price' => $machine->base_price,
            'components_price' => $componentsPrice,
            'total' => $total,
            'installation_time' => $installationTime,
            'components' => $components
        ];
    }
}