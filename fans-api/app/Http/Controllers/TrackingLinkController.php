<?php

namespace App\Http\Controllers;

use App\Models\TrackingLink;
use App\Models\TrackingLinkEvent;
use App\Models\TrackingLinkAction;
use App\Services\TrackingLinkActionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class TrackingLinkController extends Controller
{
    use AuthorizesRequests;

    protected $trackingLinkActionService;

    public function __construct(TrackingLinkActionService $trackingLinkActionService)
    {
        $this->trackingLinkActionService = $trackingLinkActionService;
    }

    public function index()
    {
        $trackingLinks = TrackingLink::with(['creator' => function ($query) {
                $query->select('id', 'username', 'display_name', 'avatar');
            }])
            ->where('creator_id', auth()->id())
            ->withCount([
                'events as clicks_count' => function ($query) {
                    $query->where('event_type', 'click');
                },
                'actions as signups_count' => function ($query) {
                    $query->where('action_type', 'signup');
                },
                'actions as subscriptions_count' => function ($query) {
                    $query->where('action_type', 'subscription');
                },
                'actions as purchases_count' => function ($query) {
                    $query->where('action_type', 'purchase');
                },
                'actions as tips_count' => function ($query) {
                    $query->where('action_type', 'tip');
                }
            ])
            ->get();

        return response()->json($trackingLinks);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $creator = $request->user();
        $data['creator_id'] = $creator->id;
        $data['is_active'] = true;

        // Generate a unique slug
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);

        // Generate the full URL
        $baseUrl = config('app.frontend_url');
        $data['full_url'] = "{$baseUrl}/{$creator->username}/" . $data['slug'];

        $trackingLink = TrackingLink::create($data);

        return response()->json($trackingLink, 201);
    }

    public function show(TrackingLink $trackingLink)
    {
        $this->authorize('view', $trackingLink);

        $trackingLink->load(['clicks', 'subscriptions', 'purchases']);
        $trackingLink->full_url = config('app.url') . '/track/' . $trackingLink->slug;

        return response()->json($trackingLink);
    }

    public function update(Request $request, TrackingLink $trackingLink)
    {
        Log::info('Update request received', [
            'tracking_link_id' => $trackingLink->id,
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->role
        ]);

        // Check if user is a creator and owns the tracking link
        if ($request->user()->role !== 'creator' || $request->user()->id !== $trackingLink->creator_id) {
            Log::warning('Unauthorized update attempt', [
                'tracking_link_id' => $trackingLink->id,
                'user_id' => $request->user()->id,
                'user_role' => $request->user()->role
            ]);
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Log::info('Authorization successful, proceeding with update', [
            'tracking_link_id' => $trackingLink->id,
            'update_data' => $request->all()
        ]);

        $trackingLink->update($request->all());
        $trackingLink->full_url = config('app.url') . '/track/' . $trackingLink->slug;

        Log::info('Tracking link updated successfully', [
            'tracking_link_id' => $trackingLink->id
        ]);

        return response()->json($trackingLink);
    }

    public function destroy(Request $request, $id)
    {
        Log::info('Delete request received', [
            'id' => $id,
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->role
        ]);

        // Find the tracking link first
        $trackingLink = TrackingLink::find($id);
        
        if (!$trackingLink) {
            Log::warning('Tracking link not found', ['id' => $id]);
            return response()->json(['message' => 'Tracking link not found'], 404);
        }

        Log::info('Found tracking link', [
            'tracking_link_id' => $trackingLink->id,
            'creator_id' => $trackingLink->creator_id,
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->role
        ]);

        try {
            // Check if user is a creator and owns the tracking link
            if ($request->user()->role !== 'creator' || $request->user()->id !== $trackingLink->creator_id) {
                Log::warning('Unauthorized deletion attempt', [
                    'tracking_link_id' => $trackingLink->id,
                    'user_id' => $request->user()->id,
                    'user_role' => $request->user()->role
                ]);
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }
            
            Log::info('Authorization successful, proceeding with deletion', [
                'tracking_link_id' => $trackingLink->id
            ]);

            $trackingLink->delete();

            Log::info('Tracking link deleted successfully', [
                'tracking_link_id' => $trackingLink->id
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error during tracking link deletion', [
                'tracking_link_id' => $trackingLink->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function track(Request $request, string $slug)
    {
        $trackingLink = TrackingLink::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Record the click event
        $this->trackingLinkActionService->trackAction(
            $trackingLink,
            'click',
            auth()->id(),
            null,
            $request
        );

        // Store the tracking link ID in the session
        session(['tracking_link_id' => $trackingLink->id]);

        // Redirect to the creator's profile
        return redirect()->route('creator.profile', ['username' => $trackingLink->creator->username]);
    }

    public function getEvents(Request $request, TrackingLink $trackingLink)
    {
        $request->validate([
            'event_type' => 'required|string|in:click',
        ]);

        $events = TrackingLinkEvent::where('tracking_link_id', $trackingLink->id)
            ->where('event_type', $request->event_type)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($events);
    }

    public function getActions(Request $request, $id)
    {
        \Log::info('Request data', [
            'all_params' => $request->all(),
            'route_params' => $request->route()->parameters(),
            'id' => $id
        ]);

        $request->validate([
            'action_type' => 'required|string|in:signup,subscription,purchase,tip',
        ]);

        $trackingLink = TrackingLink::findOrFail($id);

        \Log::info('Fetching actions for tracking link', [
            'tracking_link_id' => $trackingLink->id,
            'action_type' => $request->action_type
        ]);

        $actions = TrackingLinkAction::with(['user' => function ($query) {
                $query->select('id', 'username', 'display_name', 'avatar');
            }])
            ->where('tracking_link_id', $trackingLink->id)
            ->where('action_type', $request->action_type)
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('Found actions', [
            'count' => $actions->count(),
            'actions' => $actions->toArray()
        ]);

        return response()->json($actions);
    }

    public function getStats(TrackingLink $trackingLink)
    {
        $stats = [
            'total_clicks' => $trackingLink->events()->where('event_type', 'click')->count(),
            'total_signups' => $trackingLink->actions()->where('action_type', 'signup')->count(),
            'total_subscriptions' => $trackingLink->actions()->where('action_type', 'subscription')->count(),
            'total_purchases' => $trackingLink->actions()->where('action_type', 'purchase')->count(),
            'total_tips' => $trackingLink->actions()->where('action_type', 'tip')->count(),
            'clicks_by_referrer' => $trackingLink->events()
                ->where('event_type', 'click')
                ->select('referrer_domain', DB::raw('count(*) as count'))
                ->groupBy('referrer_domain')
                ->get(),
        ];

        $stats['conversion_rate'] = $stats['total_clicks'] > 0 
            ? (($stats['total_signups'] + $stats['total_subscriptions'] + $stats['total_purchases'] + $stats['total_tips']) / $stats['total_clicks']) * 100 
            : 0;

        return response()->json($stats);
    }

    /**
     * Record a click event for a tracking link, unique per IP address.
     */
    public function recordClick(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|exists:users,username',
            'slug' => 'required|string|exists:tracking_links,slug',
            'ip_address' => 'nullable|string',
            'user_agent' => 'nullable|string',
            'referrer_url' => 'nullable|string',
            'referrer_domain' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $trackingLink = \App\Models\TrackingLink::where('slug', $data['slug'])->firstOrFail();
        $ip = $data['ip_address'] ?? $request->ip();

        // Check for existing click from this IP for this link
        $existing = \App\Models\TrackingLinkEvent::where('tracking_link_id', $trackingLink->id)
            ->where('event_type', 'click')
            ->where('ip_address', $ip)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Click already recorded for this IP address.',
                'tracking_link_id' => $trackingLink->id,
                'event_id' => $existing->id,
            ], 200);
        }

        $event = \App\Models\TrackingLinkEvent::create([
            'tracking_link_id' => $trackingLink->id,
            'event_type' => 'click',
            'ip_address' => $ip,
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            'referrer_url' => $data['referrer_url'] ?? null,
            'referrer_domain' => $data['referrer_domain'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'tracking_link_id' => $trackingLink->id,
            'event_id' => $event->id,
        ]);
    }
} 