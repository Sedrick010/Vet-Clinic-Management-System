<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryCategory;
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:inventory_categories,id'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Item added successfully');
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:inventory_categories,id'
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