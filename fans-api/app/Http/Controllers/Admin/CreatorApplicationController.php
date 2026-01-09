<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreatorApplication;
use App\Models\CreatorApplicationHistory;
use App\Models\User;
use App\Notifications\CreatorApplicationApprovedNotification;
use App\Notifications\CreatorApplicationRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreatorApplicationController extends Controller
{
    /**
     * Display a listing of creator applications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CreatorApplication::with('user');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('dateRange')) {
            $dates = explode(',', $request->dateRange);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', $dates);
            }
        }

        $perPage = $request->input('per_page', 10);
        $applications = $query->paginate($perPage);

        return response()->json([
            'data' => $applications->items(),
            'total' => $applications->total()
        ]);
    }

    public function show($id): JsonResponse
    {
        $application = CreatorApplication::with('user')
            ->findOrFail($id);
        
        return response()->json($application);
    }

    /**
     * Update the status of a creator application.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'feedback' => 'nullable|string',
            'processed_at' => 'required|date'
        ]);

        $application = CreatorApplication::findOrFail($id);

        // Update the application
        $application->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
            'processed_at' => $request->processed_at
        ]);

        // Create history record
        CreatorApplicationHistory::create([
            'application_id' => $id,
            'admin_id' => Auth::id(),
            'status' => $request->status,
            'feedback' => $request->feedback,
            'processed_at' => $request->processed_at
        ]);

        // Get the user
        $user = User::findOrFail($application->user_id);
        
        // Update user role based on status
        if ($request->status === 'approved') {
            $user->role = 'creator';
            $user->save();

            // Send approval notification
            $user->notify(new CreatorApplicationApprovedNotification(
                $application->id,
                $request->feedback
            ));
        } else if ($request->status === 'rejected') {
            // Always set role to 'user' when rejected, regardless of previous role
            $user->role = 'user';
            $user->save();

            // Send rejection notification
            $user->notify(new CreatorApplicationRejectedNotification(
                $application->id,
                $request->feedback
            ));
        }

        return response()->json(['message' => 'Application status updated successfully']);
    }

    /**
     * Get the history of a creator application.
     */
    public function history($id): JsonResponse
    {
        $application = CreatorApplication::findOrFail($id);
        
        \Log::info('Fetching history for application ID: ' . $application->id);
        
        $history = $application->history()
            ->with('admin:id,name,email')
            ->orderBy('processed_at', 'desc')
            ->get();
        
        \Log::info('Raw history records:', $history->toArray());
        
        $mappedHistory = $history->map(function ($record) {
            return [
                'id' => $record->id,
                'status' => $record->status,
                'feedback' => $record->feedback,
                'processed_at' => $record->processed_at,
                'admin' => [
                    'name' => $record->admin->name,
                    'email' => $record->admin->email
                ]
            ];
        });
        
        \Log::info('Mapped history records:', $mappedHistory->toArray());
        
        return response()->json($mappedHistory);
    }
} 