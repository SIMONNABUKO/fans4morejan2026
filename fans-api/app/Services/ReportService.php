<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Database\Eloquent\Model;

class ReportService
{
    public function createReport(
        int $userId,
        string $contentType,
        int $contentId,
        string $reason,
        ?string $additionalInfo = null
    ): array {
        // Check if user has already reported this content
        $existingReport = Report::where([
            'user_id' => $userId,
            'content_type' => $contentType,
            'content_id' => $contentId,
        ])->first();

        if ($existingReport) {
            return [
                'success' => false,
                'message' => 'You have already reported this content.'
            ];
        }

        // Create the report
        $report = Report::create([
            'user_id' => $userId,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'reason' => $reason,
            'additional_info' => $additionalInfo,
            'status' => Report::STATUS_PENDING,
        ]);

        return [
            'success' => true,
            'message' => 'Report submitted successfully',
            'data' => $report
        ];
    }

    public function getReportableModel(string $contentType, int $contentId): array
    {
        $modelClass = match ($contentType) {
            'post' => \App\Models\Post::class,
            'comment' => \App\Models\Comment::class,
            'media' => \App\Models\Media::class,
            'user' => \App\Models\User::class,
            default => null,
        };

        if (!$modelClass) {
            return [
                'success' => false,
                'message' => 'Invalid content type.'
            ];
        }

        $model = $modelClass::find($contentId);

        if (!$model) {
            return [
                'success' => false,
                'message' => 'Content not found.'
            ];
        }

        return [
            'success' => true,
            'data' => $model
        ];
    }
}