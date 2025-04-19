<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with(['category', 'creator', 'updater'])->latest()->paginate(10);
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        $categories = InventoryCategory::all();
        return view('inventory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:inventory_categories,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Get the tenant user ID from session
        $tenantUser = session('tenant_user');
        $userId = $tenantUser->id ?? 1; // Access as object property
        
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        InventoryItem::create($data);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(InventoryItem $item)
    {
        $item->load(['category', 'creator', 'updater']);
        return view('inventory.show', compact('item'));
    }

    public function edit(InventoryItem $item)
    {
        $categories = InventoryCategory::all();
        return view('inventory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:inventory_categories,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Get the tenant user ID from session
        $tenantUser = session('tenant_user');
        $userId = $tenantUser->id ?? 1; // Access as object property
        
        $data['updated_by'] = $userId;

        $item->update($data);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function lowStock()
    {
        $items = InventoryItem::with(['category', 'creator', 'updater'])
            ->whereRaw('quantity <= reorder_level')
            ->latest()
            ->paginate(10);
            
        return view('inventory.low-stock', compact('items'));
    }
} 