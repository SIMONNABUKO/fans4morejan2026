<?php

namespace App\Services;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Model;

class StatsService
{
    public function incrementLikes(Model $model)
    {
        $this->getOrCreateStats($model)->increment('total_likes');
    }

    public function decrementLikes(Model $model)
    {
        $this->getOrCreateStats($model)->decrement('total_likes');
    }

    public function incrementViews(Model $model)
    {
        $this->getOrCreateStats($model)->increment('total_views');
    }

    public function incrementBookmarks(Model $model)
    {
        $this->getOrCreateStats($model)->increment('total_bookmarks');
    }

    public function decrementBookmarks(Model $model)
    {
        $this->getOrCreateStats($model)->decrement('total_bookmarks');
    }

    public function incrementComments(Model $model)
    {
        $this->getOrCreateStats($model)->increment('total_comments');
    }

    public function decrementComments(Model $model)
    {
        $this->getOrCreateStats($model)->decrement('total_comments');
    }

    public function addTip(Model $model, float $amount)
    {
        $stats = $this->getOrCreateStats($model);
        $stats->increment('total_tips');
        $stats->increment('total_tip_amount', $amount);
    }

    private function getOrCreateStats(Model $model)
    {
        $stats = $model->stats()->first();
        if (!$stats) {
            $stats = new Stat();
            $stats->statable()->associate($model);
            $stats->save();
        }
        return $stats;
    }
}

