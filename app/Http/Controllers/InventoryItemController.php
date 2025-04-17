<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with(['creator', 'updater'])->latest()->paginate(10);
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku|max:50',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'reorder_level' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        InventoryItem::create($data);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(InventoryItem $item)
    {
        return view('inventory.show', compact('item'));
    }

    public function edit(InventoryItem $item)
    {
        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:inventory_items,sku,' . $item->id,
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'reorder_level' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['updated_by'] = Auth::id();

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
        $items = InventoryItem::with(['creator', 'updater'])
            ->whereRaw('quantity <= reorder_level')
            ->latest()
            ->paginate(10);
            
        return view('inventory.low-stock', compact('items'));
    }
} 