<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['pet', 'veterinarian'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);
            
        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $pets = Pet::with('owner')->get();
        $veterinarians = User::where('role', 'veterinarian')->orderBy('name')->get();
        
        return view('appointments.create', compact('pets', 'veterinarians'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Log the incoming request data
            \Log::info('Appointment creation request:', $request->all());

            // Validate basic fields
            $validated = $request->validate([
                'pet_id' => $request->has('newPetToggle') ? 'nullable' : 'required|exists:pets,id',
                'veterinarian_id' => 'required|exists:users,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'reason' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            // Handle Pet Creation if needed
            if ($request->has('newPetToggle')) {
                $ownerData = $request->validate([
                    'new_pet_name' => 'required|string|max:255',
                    'owner_name' => 'required|string|max:255',
                    'owner_phone' => 'required|string|max:255',
                    'species' => 'required|string|max:255',
                    'breed' => 'nullable|string|max:255',
                    'gender' => 'required|in:male,female,unknown'
                ]);

                // Create owner
                $owner = User::create([
                    'name' => $ownerData['owner_name'],
                    'email' => 'temp_' . Str::random(10) . '@temp.com',
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'client',
                    'phone' => $ownerData['owner_phone']
                ]);

                // Create pet
                $pet = Pet::create([
                    'name' => $ownerData['new_pet_name'],
                    'owner_id' => $owner->id,
                    'species' => $ownerData['species'],
                    'breed' => $ownerData['breed'],
                    'gender' => $ownerData['gender']
                ]);

                $validated['pet_id'] = $pet->id;
            }

            // Set appointment status
            $validated['status'] = 'scheduled';

            // Create the appointment
            $appointment = Appointment::create($validated);

            // Log the created appointment
            \Log::info('Appointment created:', $appointment->toArray());

            DB::commit();

            return redirect()
                ->route('appointments.index')
                ->with('success', 'Appointment created successfully.');

        } catch (ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error:', ['errors' => $e->errors()]);
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating appointment:', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Failed to create appointment: ' . $e->getMessage());
        }
    }

    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $pets = Pet::all();
        $veterinarians = User::where('role', 'veterinarian')->get();
        
        return view('appointments.edit', compact('appointment', 'pets', 'veterinarians'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        try {
            DB::beginTransaction();

            // Validate basic fields
            $validated = $request->validate([
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'reason' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            // Handle Pet
            if ($request->has('newPetToggle')) {
                // Validate new pet fields
                $request->validate([
                    'new_pet_name' => 'required|string|max:255',
                    'owner_name' => 'required|string|max:255',
                    'owner_phone' => 'required|string|max:255',
                    'species' => 'required|string|max:255',
                    'breed' => 'nullable|string|max:255',
                ]);

                // Create new owner with temporary email
                $tempEmail = 'temp_' . Str::random(10) . '@temp.com';
                $owner = User::create([
                    'name' => $request->owner_name,
                    'phone' => $request->owner_phone,
                    'role' => 'client',
                    'email' => $tempEmail,
                    'password' => bcrypt(Str::random(16))
                ]);

                // Create new pet
                $pet = Pet::create([
                    'owner_id' => $owner->id,
                    'name' => $request->new_pet_name,
                    'species' => $request->species,
                    'breed' => $request->breed
                ]);

                $validated['pet_id'] = $pet->id;
            } else {
                // Handle existing pet or manual input
                $request->validate([
                    'pet_name' => 'required|string|max:255'
                ]);

                if (!empty($request->pet_id) && $request->pet_id !== 'new:' . $request->pet_name) {
                    // If pet_id is provided and doesn't match the manual input, validate it exists
                    $request->validate(['pet_id' => 'required|exists:pets,id']);
                    $validated['pet_id'] = $request->pet_id;
                } else {
                    // Handle manually entered pet name
                    // Create temporary owner with temporary email
                    $tempEmail = 'temp_' . Str::random(10) . '@temp.com';
                    $owner = User::create([
                        'name' => 'Temporary Owner',
                        'role' => 'client',
                        'email' => $tempEmail,
                        'password' => bcrypt(Str::random(16))
                    ]);

                    // Create new pet with basic info and default species
                    $pet = Pet::create([
                        'owner_id' => $owner->id,
                        'name' => $request->pet_name,
                        'species' => 'Unknown', // Default species for quick entry
                        'breed' => null
                    ]);

                    $validated['pet_id'] = $pet->id;
                }
            }

            // Handle Veterinarian
            $request->validate([
                'vet_name' => 'required|string|max:255'
            ]);

            if (!empty($request->veterinarian_id) && $request->veterinarian_id !== 'new:' . $request->vet_name) {
                // If veterinarian_id is provided and doesn't match the manual input, validate it exists
                $request->validate(['veterinarian_id' => 'required|exists:users,id']);
                $validated['veterinarian_id'] = $request->veterinarian_id;
            } else {
                // Create new veterinarian with temporary email
                $tempEmail = 'temp_vet_' . Str::random(10) . '@temp.com';
                $vet = User::create([
                    'name' => $request->vet_name,
                    'role' => 'veterinarian',
                    'email' => $tempEmail,
                    'password' => bcrypt(Str::random(16))
                ]);

                $validated['veterinarian_id'] = $vet->id;
            }

            // Update appointment
            $appointment->update($validated);

            DB::commit();

            return redirect()->route('appointments.index')
                ->with('success', 'Appointment updated successfully.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update appointment. ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
} 