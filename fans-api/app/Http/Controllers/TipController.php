<?php

namespace App\Http\Controllers;

use App\Services\TipService;
use App\Models\Post;
use App\Models\Media;
use App\Models\MediaPreview;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TipController extends Controller
{
    protected $tipService;

    public function __construct(TipService $tipService)
    {
        $this->tipService = $tipService;
    }

    public function sendTip(Request $request): JsonResponse
    {
        $request->validate([
            'tippable_type' => 'required|in:post,media,media_preview,message',
            'tippable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $tippable = $this->getTippable($request->tippable_type, $request->tippable_id);

        if (!$tippable) {
            return response()->json(['error' => 'Tippable item not found'], 404);
        }

        // Get tracking link ID from session if available
        $trackingLinkId = session('tracking_link_id');
        if ($trackingLinkId) {
            Log::info('Adding tracking link ID to tip', [
                'tracking_link_id' => $trackingLinkId,
                'tippable_type' => $request->tippable_type,
                'tippable_id' => $request->tippable_id
            ]);
        }

        $tip = $this->tipService->sendTip(
            $request->user(), 
            $tippable, 
            $request->amount,
            $trackingLinkId
        );

        return response()->json(['message' => 'Tip sent successfully', 'tip' => $tip]);
    }

    private function getTippable(string $type, int $id)
    {
        switch ($type) {
            case 'post':
                return Post::find($id);
            case 'media':
                return Media::find($id);
            case 'media_preview':
                return MediaPreview::find($id);
            case 'message':
                return Message::find($id);
            default:
                return null;
        }
    }
}

