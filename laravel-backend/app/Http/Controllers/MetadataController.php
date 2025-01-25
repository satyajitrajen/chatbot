<?php

namespace App\Http\Controllers;

use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetadataController extends Controller
{
  
public function store(Request $request)
{
    Log::info('Received request to store metadata', $request->all());

    try {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'ip_address' => 'nullable|string',
            'user_agent' => 'nullable|string',
            'timestamp' => 'nullable|date',
            'location' => 'nullable|array',
        ]);

        Log::info('Validated metadata input', $validated);

        // Extract location details
        $location = $validated['location'] ?? [];
        $metadata = [
            'user_id' => $validated['user_id'],
            'ip_address' => $validated['ip_address'],
            'user_agent' => $validated['user_agent'],
            'timestamp' => $validated['timestamp'],
            'country' => $location['country'] ?? null,
            'region' => $location['region'] ?? null,
            'city' => $location['city'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
        ];

        Log::info('Prepared metadata for storage', $metadata);

        Metadata::create($metadata);

        Log::info('Metadata stored successfully in the database', ['user_id' => $validated['user_id']]);

        return response()->json(['success' => true, 'message' => 'Metadata stored successfully.']);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation failed while storing metadata', ['errors' => $e->errors()]);
        return response()->json(['success' => false, 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Unexpected error while storing metadata', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'error' => 'Failed to store metadata.'], 500);
    }
}


public function getMetadata($userId)
{
    $metadata = Metadata::where('user_id', $userId)->first();

    if ($metadata) {
        return response()->json([
            'success' => true,
            'metadata' => $metadata,
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Metadata not found',
        ]);
    }
}

}
