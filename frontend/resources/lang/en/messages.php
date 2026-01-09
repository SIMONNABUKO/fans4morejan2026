<?php

return [
    // Authentication
    'auth' => [
        'failed' => 'These credentials do not match our records.',
        'password' => 'The provided password is incorrect.',
        'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    ],

    // Validation
    'validation' => [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'min' => [
            'string' => 'The :attribute must be at least :min characters.',
        ],
        'max' => [
            'string' => 'The :attribute may not be greater than :max characters.',
        ],
        'unique' => 'The :attribute has already been taken.',
    ],

    // Profile
    'profile' => [
        'updated' => 'Profile updated successfully.',
        'not_found' => 'Profile not found.',
    ],

    // Subscription
    'subscription' => [
        'created' => 'Subscription created successfully.',
        'updated' => 'Subscription updated successfully.',
        'deleted' => 'Subscription deleted successfully.',
        'not_found' => 'Subscription not found.',
    ],

    // Media
    'media' => [
        'uploaded' => 'Media uploaded successfully.',
        'deleted' => 'Media deleted successfully.',
        'not_found' => 'Media not found.',
    ],

    // Payment
    'payment' => [
        'success' => 'Payment processed successfully.',
        'failed' => 'Payment failed. Please try again.',
        'insufficient_balance' => 'Insufficient wallet balance.',
    ],

    // General
    'success' => 'Operation completed successfully.',
    'error' => 'An error occurred. Please try again.',
    'not_found' => 'Resource not found.',
    'unauthorized' => 'You are not authorized to perform this action.',
]; 