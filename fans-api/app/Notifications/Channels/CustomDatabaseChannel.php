<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CustomDatabaseChannel extends BaseDatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the data from the notification
        $data = $this->getData($notifiable, $notification);
        
        // Log the data before storage
        Log::info('Notification data before database storage', [
            'notification_class' => get_class($notification),
            'data_keys' => array_keys($data)
        ]);
        
        // Ensure the data is JSON serializable
        $serialized = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON serialization error', [
                'error' => json_last_error_msg(),
                'notification_class' => get_class($notification)
            ]);
            
            // Try to fix the data by removing problematic elements
            $data = $this->makeSerializable($data);
            $serialized = json_encode($data);
        }
        
        // Store the notification with properly serialized data
        $notification = $notifiable->routeNotificationFor('database', $notification)->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => get_class($notification),
            'data' => $serialized,  // Now properly storing the JSON string
            'read_at' => null,
        ]);
        
        // Verify what was saved
        Log::info('Notification saved to database', [
            'notification_id' => $notification->id,
            'data_size' => strlen($serialized)
        ]);
        
        return $notification;
    }
    
    /**
     * Get the data for the notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     */
    protected function getData($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toDatabase')) {
            $data = $notification->toDatabase($notifiable);
            Log::info('Retrieved data from toDatabase method', [
                'data_available' => !empty($data)
            ]);
            return $data;
        }
        
        if (method_exists($notification, 'toArray')) {
            $data = $notification->toArray($notifiable);
            Log::info('Retrieved data from toArray method', [
                'data_available' => !empty($data)
            ]);
            return $data;
        }
        
        Log::warning('No data retrieval method found on notification', [
            'notification_class' => get_class($notification)
        ]);
        
        return [];
    }
    
    /**
     * Make the data serializable by removing problematic elements.
     *
     * @param  array  $data
     * @return array
     */
    protected function makeSerializable($data)
    {
        Log::info('Attempting to make data serializable');
        
        // Convert to JSON and back to remove unserializable elements
        $json = json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
        $result = json_decode($json, true) ?: [];
        
        Log::info('Made data serializable', [
            'original_keys' => array_keys($data),
            'result_keys' => array_keys($result)
        ]);
        
        return $result;
    }
}