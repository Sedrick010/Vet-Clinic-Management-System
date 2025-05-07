<?php

namespace App\Http\Controllers;

use App\Models\SystemUpdate;
use App\Models\SystemVersion;
use App\Models\Clinic;
use App\Models\ClinicUpdate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GitHubWebhookController extends Controller
{
    /**
     * Handle the incoming GitHub webhook request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        $eventType = $request->header('X-GitHub-Event');
        $payload = $request->all();
        
        Log::info('Received GitHub webhook', [
            'event' => $eventType,
            'action' => $payload['action'] ?? 'unknown'
        ]);
        
        // Make sure this is a release event
        if ($eventType !== 'release') {
            return response()->json(['message' => 'Not a release event'], 200);
        }
        
        // Only process published releases
        if (!isset($payload['action']) || $payload['action'] !== 'published') {
            return response()->json(['message' => 'Not a published release'], 200);
        }
        
        $release = $payload['release'];
        
        try {
            $this->processRelease($release);
            return response()->json(['message' => 'Release processed successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing GitHub release: ' . $e->getMessage(), [
                'exception' => $e,
                'release' => $release['tag_name'] ?? 'unknown'
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Process a GitHub release and create system updates.
     *
     * @param  array  $release
     * @return void
     */
    private function processRelease($release)
    {
        DB::beginTransaction();
        
        try {
            // Parse GitHub release data
            $version = ltrim($release['tag_name'], 'v'); // Remove 'v' prefix if any
            $name = $release['name'];
            $body = $release['body'] ?? '';
            
            // Parse release body for structured content
            $sections = $this->parseReleaseBody($body);
            
            // Check if this version already exists
            if (SystemUpdate::where('version', $version)->exists()) {
                Log::info("Version {$version} already exists in the system. Skipping.");
                DB::rollBack();
                return;
            }
            
            // Create a new system update
            $update = new SystemUpdate();
            $update->version = $version;
            $update->name = $name;
            $update->description = $sections['summary'] ?? $name;
            $update->changes = $sections['changes'] ?? $body;
            $update->features = $sections['features'] ?? null;
            $update->bug_fixes = $sections['bug_fixes'] ?? null;
            $update->is_critical = $sections['is_critical'] ?? false;
            $update->is_security = $sections['is_security'] ?? false;
            $update->is_mandatory = $sections['is_mandatory'] ?? false;
            $update->available_from = Carbon::now();
            $update->save();
            
            // Update the current version in system_versions
            SystemVersion::where('is_current', true)->update(['is_current' => false]);
            
            $systemVersion = new SystemVersion();
            $systemVersion->version = $version;
            $systemVersion->name = $name;
            $systemVersion->description = $sections['summary'] ?? $name;
            $systemVersion->is_current = true;
            $systemVersion->released_at = Carbon::now();
            $systemVersion->save();
            
            // If this is a mandatory update, create entries for all clinics
            if ($update->is_mandatory) {
                $this->createMandatoryUpdatesForClinics($update->id);
            }
            
            DB::commit();
            
            Log::info("Successfully processed GitHub release: v{$version}");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Create mandatory update records for all active clinics.
     *
     * @param  int  $updateId
     * @return void
     */
    private function createMandatoryUpdatesForClinics($updateId)
    {
        // Get all active clinics
        $clinics = Clinic::where('approval_status', 'approved')
                        ->where('is_active', true)
                        ->get();
        
        foreach ($clinics as $clinic) {
            ClinicUpdate::create([
                'clinic_id' => $clinic->id,
                'system_update_id' => $updateId,
                'is_applied' => false,
                'is_dismissed' => false
            ]);
        }
        
        Log::info("Created mandatory update records for {$clinics->count()} clinics");
    }
    
    /**
     * Parse the GitHub release body into structured sections.
     *
     * @param  string  $body
     * @return array
     */
    private function parseReleaseBody($body)
    {
        // Initialize sections
        $sections = [
            'summary' => '',
            'changes' => '',
            'features' => '',
            'bug_fixes' => '',
            'is_critical' => false,
            'is_security' => false, 
            'is_mandatory' => false
        ];
        
        // Example parsing logic - adjust based on your release notes format
        if (preg_match('/## Summary(.*?)(?=##|$)/s', $body, $matches)) {
            $sections['summary'] = trim($matches[1]);
        }
        
        if (preg_match('/## Changes(.*?)(?=##|$)/s', $body, $matches)) {
            $sections['changes'] = trim($matches[1]);
        }
        
        if (preg_match('/## New Features(.*?)(?=##|$)/s', $body, $matches)) {
            $sections['features'] = trim($matches[1]);
        }
        
        if (preg_match('/## Bug Fixes(.*?)(?=##|$)/s', $body, $matches)) {
            $sections['bug_fixes'] = trim($matches[1]);
        }
        
        // Check for special tags
        $sections['is_critical'] = stripos($body, '#critical') !== false;
        $sections['is_security'] = stripos($body, '#security') !== false;
        $sections['is_mandatory'] = stripos($body, '#mandatory') !== false;
        
        return $sections;
    }
} 