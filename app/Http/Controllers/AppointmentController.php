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
            
        return view('appointments.index', compact('appointments'));
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
        
        // Load all pets with proper eager loading for better performance
        $pets = DB::connection('tenant')
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
            )
            ->whereNull('pets.deleted_at')
            ->orderBy('pets.name')
            ->get();
            
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

            // Get pets for the client using direct query
            $pets = DB::connection('tenant')
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
                ->where('pets.owner_id', $clientId)
                ->whereNull('pets.deleted_at')
                ->orderBy('pets.name')
                ->get();
            
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
                'count' => $pets->count(),
                'clientId' => $clientId,
                'clinic' => $clinic->name
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

        try {
            DB::beginTransaction();

            // Validate basic fields
            $validated = $request->validate([
                'client_name' => 'required|string|max:255',
                'staff_id' => 'required|exists:tenant.staff,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'reason' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            // Set appointment status
            $validated['status'] = 'scheduled';

            // Create the appointment
            $appointment = Appointment::create($validated);

            DB::commit();

            return redirect()
                ->route('appointments.index')
                ->with('success', 'Appointment created successfully.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create appointment: ' . $e->getMessage());
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

        $appointment = Appointment::with(['staff'])->findOrFail($id);
        
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
        $staff = Staff::where('role', 'doctor')->orderBy('name')->get();
        
        return view('appointments.edit', compact('appointment', 'staff'));
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
                'client_name' => 'required|string|max:255',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Update the staff if specified
            if ($request->has('staff_id')) {
                $validated['staff_id'] = $request->staff_id;
            } elseif ($request->has('veterinarian_id')) {
                // For backward compatibility
                $validated['staff_id'] = $request->veterinarian_id;
            }

            $appointment->update($validated);

            DB::commit();

            return redirect()
                ->route('appointments.show', $appointment->id)
                ->with('success', 'Appointment updated successfully.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            DB::rollBack();
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
} 