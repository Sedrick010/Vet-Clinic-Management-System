<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.subscription');
    }

    public function index()
    {
        $items = InventoryItem::with(['createdBy', 'updatedBy'])->paginate(10);
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'reorder_level' => 'required|integer|min:0'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Item added successfully');
    }

    public function edit(InventoryItem $item)
    {
        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku,' . $item->id,
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'reorder_level' => 'required|integer|min:0'
        ]);

        $validated['updated_by'] = Auth::id();

        $item->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Item updated successfully');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return redirect()->route('inventory.index')
            ->with('success', 'Item deleted successfully');
    }
} 