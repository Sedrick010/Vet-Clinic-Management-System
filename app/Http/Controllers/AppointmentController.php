<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Client;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Schema;

class AppointmentController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    private function getClinic(Request $request)
    {
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $subdomain = null;

        if ($host !== $appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
        }

        if ($subdomain) {
            return \App\Models\Clinic::where('subdomain', $subdomain)->first();
        }

        return null;
    }

    public function index(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $appointments = Appointment::with(['staff'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);
        
        // Get appointments count and subscription limit
        $appointmentsCount = Appointment::count();
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $appointmentsLimit = $subscriptionService->getLimitForFeature($clinic, 'appointments_limit');
        $hasReachedLimit = $subscriptionService->hasReachedLimit($clinic, 'appointments_limit', $appointmentsCount);
            
        return view('appointments.index', [
            'appointments' => $appointments,
            'appointmentsCount' => $appointmentsCount,
            'appointmentsLimit' => $appointmentsLimit,
            'hasReachedLimit' => $hasReachedLimit,
        ]);
    }

    public function create(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get all clients
        $clients = Client::orderBy('name')->get();
        
        // Log basic information about what we're loading
        \Log::info('Loading appointment creation form', [
            'clinic_id' => $clinic->id,
            'clinic_name' => $clinic->name,
            'database' => $clinic->database_name,
            'client_count' => $clients->count()
        ]);
        
        // Check if deleted_at column exists in pets table
        $hasPetsDeletedAt = false;
        try {
            $hasPetsDeletedAt = Schema::connection('tenant')->hasColumn('pets', 'deleted_at');
        } catch (\Exception $e) {
            \Log::error('Error checking for pets.deleted_at column: ' . $e->getMessage());
        }
        
        // Build the query
        $petsQuery = DB::connection('tenant')
            ->table('pets')
            ->join('clients', 'pets.owner_id', '=', 'clients.id')
            ->select(
                'pets.id', 
                'pets.name', 
                'pets.species', 
                'pets.breed', 
                'pets.gender', 
                'clients.name as owner_name', 
                'clients.id as owner_id'
            );
            
        // Only apply the SoftDeletes condition if the column exists
        if ($hasPetsDeletedAt) {
            $petsQuery->whereNull('pets.deleted_at');
        }
        
        // Get the results
        $pets = $petsQuery->orderBy('pets.name')->get();
            
        // Get doctor staff
        $staff = Staff::where('role', 'doctor')->orderBy('name')->get();
        
        // Add detailed diagnostic information
        \Log::info('Appointment form data loaded', [
            'client_count' => $clients->count(),
            'pet_count' => $pets->count(),
            'staff_count' => $staff->count(),
            'first_few_pets' => $pets->take(5)->map(function($pet) {
                return [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'owner_id' => $pet->owner_id,
                    'owner_name' => $pet->owner_name
                ];
            })
        ]);
        
        return view('appointments.create', compact('pets', 'staff', 'clients'));
    }

    /**
     * Get pets for a specific client - API endpoint
     */
    public function getPetsByClient(Request $request, $clientId)
    {
        try {
            \Log::info("API Request: getPetsByClient", [
                'clientId' => $clientId,
                'request_url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent()
            ]);
            
            $clinic = $this->getClinic($request);
            if (!$clinic) {
                \Log::error("API Error: No clinic found for request", [
                    'host' => $request->getHost(),
                    'headers' => $request->header()
                ]);
                return response()->json([
                    'success' => false, 
                    'error' => 'No clinic selected',
                    'message' => 'Unable to determine current clinic'
                ], 400);
            }

            // Ensure database is configured properly
            $this->tenantDatabaseService->switchToTenant($clinic);
            
            \Log::info('Tenant connection established for getPetsByClient', [
                'clinic' => $clinic->name,
                'database' => $clinic->database_name,
                'clientId' => $clientId
            ]);

            // Verify client exists before querying pets
            $clientExists = DB::connection('tenant')
                ->table('clients')
                ->where('id', $clientId)
                ->exists();
                
            if (!$clientExists) {
                \Log::warning('Client not found for pet lookup', [
                    'client_id' => $clientId,
                    'clinic_id' => $clinic->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Client not found',
                    'message' => 'The specified client does not exist',
                    'clientId' => $clientId
                ], 404);
            }

            // Check if deleted_at column exists in pets table
            $hasPetsDeletedAt = false;
            try {
                $hasPetsDeletedAt = Schema::connection('tenant')->hasColumn('pets', 'deleted_at');
            } catch (\Exception $e) {
                \Log::error('Error checking for pets.deleted_at column: ' . $e->getMessage());
            }

            // Get pets for the client using direct query
            $petsQuery = DB::connection('tenant')
                ->table('pets')
                ->leftJoin('clients', 'pets.owner_id', '=', 'clients.id')
                ->select(
                    'pets.id',
                    'pets.name',
                    'pets.species',
                    'pets.breed',
                    'pets.gender',
                    'clients.name as owner_name',
                    'clients.id as owner_id'
                )
                ->where('pets.owner_id', $clientId);
                
            // Only apply the SoftDeletes condition if the column exists
            if ($hasPetsDeletedAt) {
                $petsQuery->whereNull('pets.deleted_at');
            }
            
            $pets = $petsQuery->orderBy('pets.name')->get();
            
            // Log detailed information about the query and results
            \Log::info("Pet data for client {$clientId}", [
                'raw_sql' => $petsQuery->toSql(),
                'bindings' => $petsQuery->getBindings(),
                'pets_count' => $pets->count(),
                'pet_data' => $pets->toArray()
            ]);
            
            // Force UTC timezone for serialization
            foreach ($pets as $pet) {
                if (isset($pet->birthdate)) {
                    $pet->birthdate = carbon($pet->birthdate)->toDateString();
                }
            }
            
            // Log result for debugging
            \Log::info("Pet data fetched for client", [
                'clientId' => $clientId,
                'petCount' => $pets->count(),
                'pet_ids' => $pets->pluck('id')->toArray(),
                'pet_names' => $pets->pluck('name')->toArray()
            ]);
            
            // Ensure compatibility with older formats by implementing both response styles
            if ($request->query('format') === 'simple') {
                return response()->json($pets);
            }
            
            // Return a standardized response
            return response()->json([
                'success' => true,
                'data' => $pets,
                'pets' => $pets,
                'count' => $pets->count(),
                'clientId' => $clientId,
                'clinic' => $clinic->name,
                'debug' => [
                    'hasPetsDeletedAt' => $hasPetsDeletedAt,
                    'query' => 'SELECT pets.* FROM pets WHERE owner_id = ' . $clientId . ($hasPetsDeletedAt ? ' AND deleted_at IS NULL' : '')
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("API Error in getPetsByClient: {$e->getMessage()}", [
                'clientId' => $clientId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error fetching pet data: ' . $e->getMessage(),
                'message' => 'Server error occurred while fetching pets',
                'clientId' => $clientId
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Check appointment subscription limit
        $appointmentCount = Appointment::count();
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        
        if ($subscriptionService->hasReachedLimit($clinic, 'appointments_limit', $appointmentCount)) {
            return redirect()->route('subscription.limit.reached', ['limitType' => 'appointments'])
                ->with('error', 'You have reached the maximum number of appointments allowed in your current subscription plan.');
        }

        // Appointment type validation - ensure it's a valid value
        $validAppointmentTypes = ['check-up', 'vaccination', 'surgery', 'consultation', 'emergency', 'follow-up', 'grooming', 'other'];
        
        $validated = $request->validate([
            'pet_id' => 'required|exists:tenant.pets,id',
            'staff_id' => 'required|exists:tenant.staff,id',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'duration' => 'required|integer|min:5',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled,no-show',
            'appointment_type' => 'required|in:' . implode(',', $validAppointmentTypes),
            'client_id' => 'required|exists:tenant.clients,id',
            'client_name' => 'required|string|max:255',
        ]);
        
        try {
            // Combine date and time
            $startDateTime = $validated['start_date'] . ' ' . $validated['start_time'];
            $startTime = new \DateTime($startDateTime);
            
            // Calculate end time based on duration
            $endTime = clone $startTime;
            $endTime->add(new \DateInterval('PT' . $validated['duration'] . 'M'));
            
            // Create the appointment
            $appointment = new Appointment();
            $appointment->pet_id = $validated['pet_id'];
            $appointment->staff_id = $validated['staff_id'];
            $appointment->start_time = $startTime;
            $appointment->end_time = $endTime;
            $appointment->duration = $validated['duration'];
            $appointment->reason = $validated['reason'];
            $appointment->notes = $validated['notes'];
            $appointment->status = $validated['status'];
            $appointment->appointment_type = $validated['appointment_type'];
            $appointment->client_id = $validated['client_id'];
            $appointment->client_name = $validated['client_name'];
            $appointment->save();
            
            return redirect()->route('appointments.index')
                ->with('success', 'Appointment scheduled successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating appointment: ' . $e->getMessage(), [
                'clinic_id' => $clinic->id,
                'user_input' => $request->except(['_token'])
            ]);
            
            return back()->withInput()->with('error', 'Error creating appointment: ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $appointment = Appointment::with(['staff', 'pet'])->findOrFail($id);
        
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $appointment = Appointment::findOrFail($id);
        
        // Get all clients
        $clients = Client::orderBy('name')->get();
        
        // Get list of pets for dropdown
        $hasPetsDeletedAt = false;
        try {
            $hasPetsDeletedAt = Schema::connection('tenant')->hasColumn('pets', 'deleted_at');
        } catch (\Exception $e) {
            \Log::error('Error checking for pets.deleted_at column: ' . $e->getMessage());
        }
        
        // Build the query for pets
        $petsQuery = DB::connection('tenant')
            ->table('pets')
            ->join('clients', 'pets.owner_id', '=', 'clients.id')
            ->select(
                'pets.id', 
                'pets.name', 
                'pets.species', 
                'pets.breed', 
                'pets.gender', 
                'clients.name as owner_name', 
                'clients.id as owner_id'
            );
            
        // Only apply the SoftDeletes condition if the column exists
        if ($hasPetsDeletedAt) {
            $petsQuery->whereNull('pets.deleted_at');
        }
        
        // Get the results
        $pets = $petsQuery->orderBy('pets.name')->get();
        
        // Get the selected pet if available
        $selectedPet = null;
        if (isset($appointment->pet_id)) {
            $selectedPet = $petsQuery->where('pets.id', $appointment->pet_id)->first();
        }
        
        $staff = Staff::where('role', 'doctor')->orderBy('name')->get();
        
        return view('appointments.edit', compact('appointment', 'staff', 'pets', 'selectedPet', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        try {
            DB::beginTransaction();

            $appointment = Appointment::findOrFail($id);

            // Validate basic fields
            $validated = $request->validate([
                'client_id' => 'required|exists:tenant.clients,id',
                'client_name' => 'nullable|string|max:255',
                'pet_id' => 'required|exists:tenant.pets,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Get client name if not provided
            if (empty($validated['client_name']) && isset($validated['client_id'])) {
                $client = Client::find($validated['client_id']);
                if ($client) {
                    $validated['client_name'] = $client->name;
                }
            }

            // Update the staff if specified
            if ($request->has('staff_id')) {
                $validated['staff_id'] = $request->staff_id;
            } elseif ($request->has('veterinarian_id')) {
                // For backward compatibility
                $validated['staff_id'] = $request->veterinarian_id;
            }

            $appointment->update($validated);
            
            // Log successful appointment update with pet information
            $pet = Pet::find($request->pet_id);
            \Log::info('Appointment updated successfully', [
                'appointment_id' => $appointment->id,
                'client_id' => $validated['client_id'],
                'client_name' => $validated['client_name'],
                'pet_id' => $request->pet_id,
                'pet_name' => $pet ? $pet->name : 'Unknown',
                'start_time' => $validated['start_time']
            ]);

            DB::commit();

            return redirect()
                ->route('appointments.show', $appointment->id)
                ->with('success', 'Appointment updated successfully.');

        } catch (ValidationException $e) {
            DB::rollBack();
            \Log::error('Appointment update validation failed', [
                'appointment_id' => $id,
                'errors' => $e->errors(),
                'input' => $request->except(['_token', '_method'])
            ]);
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Appointment update failed: ' . $e->getMessage(), [
                'appointment_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to update appointment: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return response()->json(['error' => 'No clinic selected'], 400);
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no-show'
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment status updated successfully.',
            'new_status' => $validated['status']
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    public function debugPetsCheck(Request $request)
    {
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);
        
        // Get all clients
        $clients = Client::orderBy('name')->get();
        
        // Initialize array to store pets for each client
        $clientPets = [];
        
        // Get pets for each client
        foreach ($clients as $client) {
            // Using the Pet model relationship
            $clientPets[$client->id] = $client->pets()->get();
        }
        
        // Also check with direct DB query
        $query_results = [];
        
        foreach ($clients as $client) {
            // Check if deleted_at column exists in pets table
            $hasPetsDeletedAt = Schema::connection('tenant')->hasColumn('pets', 'deleted_at');
            
            // Get pets for the client using direct query
            $petsQuery = DB::connection('tenant')
                ->table('pets')
                ->select('*')
                ->where('owner_id', $client->id);
                
            // Only apply the SoftDeletes condition if the column exists
            if ($hasPetsDeletedAt) {
                $petsQuery->whereNull('deleted_at');
            }
            
            $pets = $petsQuery->get();
            
            $query_results[$client->id] = [
                'client_name' => $client->name,
                'pets_count' => $pets->count(),
                'pets' => $pets->toArray(),
                'has_deleted_at' => $hasPetsDeletedAt,
                'connection' => config('database.connections.tenant.database')
            ];
        }
        
        return view('debug.pets-check', compact('clients', 'clientPets', 'query_results'));
    }
} 