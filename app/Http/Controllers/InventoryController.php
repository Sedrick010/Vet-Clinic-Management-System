<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\SubscriptionService;

class InventoryController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    /**
     * Switch to tenant database based on current clinic
     */
    protected function switchToTenantDb()
    {
        $clinicId = session('current_clinic_id');
        
        if (!$clinicId) {
            Log::error('No current clinic ID in session');
            abort(403, 'No clinic selected');
        }
        
        $clinic = \App\Models\Clinic::find($clinicId);
        
        if (!$clinic) {
            Log::error('Clinic not found', ['clinic_id' => $clinicId]);
            abort(404, 'Clinic not found');
        }
        
        try {
            $this->tenantDatabaseService->switchToTenant($clinic);
        } catch (\Exception $e) {
            Log::error('Failed to switch to tenant database', [
                'clinic_id' => $clinicId, 
                'error' => $e->getMessage()
            ]);
            abort(500, 'Database connection error');
        }
    }

    /**
     * Display a listing of the inventory items.
     */
    public function index(Request $request): View
    {
        $this->switchToTenantDb();
        
        $query = Inventory::query();
        
        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        
        // Get inventory items with pagination
        $items = $query->orderBy('name')->paginate(15);
        
        // Get unique categories for filter dropdown
        $categories = Inventory::distinct()->pluck('category')->filter()->sort()->values();
        
        // Get clinic information
        $clinicId = session('current_clinic_id');
        $clinic = \App\Models\Clinic::find($clinicId);
        
        // Get inventory count and subscription limit
        $inventoryCount = Inventory::count();
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $inventoryLimit = $subscriptionService->getLimitForFeature($clinic, 'inventory_limit');
        $hasReachedLimit = $subscriptionService->hasReachedLimit($clinic, 'inventory_limit', $inventoryCount);
        
        return view('inventory.index', [
            'items' => $items,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'isSidebar' => true,
            'inventoryCount' => $inventoryCount,
            'inventoryLimit' => $inventoryLimit,
            'hasReachedLimit' => $hasReachedLimit,
        ]);
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create(): View
    {
        $this->switchToTenantDb();
        
        // Get unique categories for dropdown
        $categories = Inventory::distinct()->pluck('category')->filter()->sort()->values();
        
        return view('inventory.create', [
            'categories' => $categories,
            'isSidebar' => true,
        ]);
    }

    /**
     * Store a newly created inventory item in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->switchToTenantDb();
        
        // Check inventory limit
        $inventoryCount = Inventory::count();
        $clinicId = session('current_clinic_id');
        $clinic = \App\Models\Clinic::find($clinicId);
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        
        if ($subscriptionService->hasReachedLimit($clinic, 'inventory_limit', $inventoryCount)) {
            return redirect()->route('subscription.limit.reached', ['limitType' => 'inventory'])
                ->with('error', 'You have reached the maximum number of inventory items allowed in your current subscription plan.');
        }
        
        // Make sure we're using the tenant connection for validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:tenant.inventory_items,sku',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'stock_quantity' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('inventory.create')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get only the fields we know exist in the table
            $data = $request->only([
                'name',
                'sku',
                'description',
                'category',
                'stock_quantity',
                'selling_price',
                'reorder_level',
            ]);
            
            // Set cost_price to 0
            $data['cost_price'] = 0;
            
            // Handle is_active field if the column exists in the database
            if ($this->columnExists('inventory_items', 'is_active')) {
                $data['is_active'] = $request->has('is_active');
            }
            
            // Create inventory item
            $inventoryItem = Inventory::create($data);
            
            return redirect()->route('inventory.index')
                ->with('success', "Item '{$inventoryItem->name}' has been added to inventory.");
        } catch (\Exception $e) {
            Log::error('Error creating inventory item', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('inventory.create')
                ->with('error', 'Failed to create item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show($id): View
    {
        $this->switchToTenantDb();
        
        $inventory = Inventory::findOrFail($id);
        
        return view('inventory.show', [
            'item' => $inventory,
            'isSidebar' => true,
        ]);
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit($id): View
    {
        $this->switchToTenantDb();
        
        $inventory = Inventory::findOrFail($id);
        
        // Get unique categories for dropdown
        $categories = Inventory::distinct()->pluck('category')->filter()->sort()->values();
        
        return view('inventory.edit', [
            'item' => $inventory,
            'categories' => $categories,
            'isSidebar' => true,
        ]);
    }

    /**
     * Update the specified inventory item in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->switchToTenantDb();
        
        $inventory = Inventory::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:tenant.inventory_items,sku,' . $inventory->id,
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'stock_quantity' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('inventory.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get only the fields we know exist in the table
            $data = $request->only([
                'name',
                'sku',
                'description',
                'category',
                'stock_quantity',
                'selling_price',
                'reorder_level',
            ]);
            
            // Set cost_price to 0
            $data['cost_price'] = 0;
            
            // Handle is_active field if the column exists in the database
            if ($this->columnExists('inventory_items', 'is_active')) {
                $data['is_active'] = $request->has('is_active');
            }
            
            // Update the inventory item
            $inventory->update($data);
            
            return redirect()->route('inventory.index')
                ->with('success', "Item '{$inventory->name}' has been updated.");
        } catch (\Exception $e) {
            Log::error('Error updating inventory item', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('inventory.edit', $id)
                ->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified inventory item from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $this->switchToTenantDb();
        
        $inventory = Inventory::findOrFail($id);
        $itemName = $inventory->name;
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', "Item '{$itemName}' has been deleted from inventory.");
    }
    
    /**
     * Toggle the active status of an inventory item.
     */
    public function toggleStatus($id): RedirectResponse
    {
        $this->switchToTenantDb();
        
        $inventory = Inventory::findOrFail($id);
        
        // Toggle the active status
        $inventory->is_active = !$inventory->is_active;
        $inventory->save();
        
        $status = $inventory->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('inventory.show', $id)
            ->with('success', "Item '{$inventory->name}' has been {$status}.");
    }
    
    /**
     * Get inventory statistics for dashboard.
     */
    public static function getInventoryStats(): array
    {
        try {
            // This is a static method, so we need to get the tenant database service differently
            $clinicId = session('current_clinic_id');
            
            if (!$clinicId) {
                return [
                    'total' => 0,
                ];
            }
            
            $clinic = \App\Models\Clinic::find($clinicId);
            
            if (!$clinic) {
                return [
                    'total' => 0,
                ];
            }
            
            app(\App\Services\TenantDatabaseService::class)->switchToTenant($clinic);
            
            $totalItems = Inventory::count();
            
            return [
                'total' => $totalItems,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting inventory stats: ' . $e->getMessage());
            return [
                'total' => 0,
            ];
        }
    }

    // Add helper method to safely check if column exists
    protected function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::connection('tenant')->hasColumn($table, $column);
        } catch (\Exception $e) {
            Log::error('Error checking if column exists', [
                'table' => $table, 
                'column' => $column,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 