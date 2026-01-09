# Blocked Locations Implementation Plan

## Overview
This document outlines the implementation plan for the blocked locations feature, which allows users to block access to their profile, content, and visibility from specific geographic locations.

## Implementation Tasks

### Phase 1: Database & Basic Setup (2-3 days)
1. **Create Migration**
   ```bash
   php artisan make:migration create_blocked_locations_table
   ```

2. **Create Models & Services**
   ```bash
   php artisan make:model BlockedLocation
   php artisan make:service LocationService
   php artisan make:service ContentVisibilityService
   ```

3. **Create Controllers**
   ```bash
   php artisan make:controller BlockedLocationController
   ```

4. **Install Required Packages**
   ```bash
   composer require guzzlehttp/guzzle
   ```

### Phase 2: Location Detection (2-3 days)
1. **Implement LocationService**
   ```php
   class LocationService
   {
       public function getUserLocation($ip)
       {
           return Cache::remember("user_location_{$ip}", now()->addHours(24), function() use ($ip) {
               return Http::get("http://ip-api.com/json/{$ip}")->json();
           });
       }

       public function validateLocation($location)
       {
           // Validate location data
           // Return standardized location format
       }
   }
   ```

2. **Update Login Process**
   - Add location detection on login
   - Store location in session
   - Handle location updates

3. **Create Location Middleware**
   - Check user location
   - Update location if needed
   - Handle location errors

### Phase 3: Blocking Implementation (3-4 days)
1. **Create BlockedLocationController**
   ```php
   class BlockedLocationController extends Controller
   {
       public function index()
       {
           return auth()->user()->blockedLocations;
       }

       public function store(Request $request)
       {
           // Validate and store blocked location
       }

       public function destroy($id)
       {
           // Remove blocked location
       }
   }
   ```

2. **Implement Blocking Logic**
   - Add blocking methods to User model
   - Create blocking validation
   - Handle blocking conflicts

3. **Create Blocking Middleware**
   - Check if user is blocked
   - Handle blocked access
   - Log blocking events

### Phase 4: Content Visibility (4-5 days)
1. **Update Feed Queries**
   ```php
   // In PostController
   public function getFeed()
   {
       $userLocation = $this->locationService->getUserLocation(request()->ip());
       return $this->contentVisibilityService->filterContent(
           Post::query(),
           $userLocation
       )->latest()->paginate(20);
   }
   ```

2. **Update User Suggestions**
   ```php
   // In UserController
   public function getSuggestions()
   {
       $userLocation = $this->locationService->getUserLocation(request()->ip());
       return $this->contentVisibilityService->filterContent(
           User::query(),
           $userLocation
       )->inRandomOrder()->limit(10)->get();
   }
   ```

3. **Update Search Results**
   ```php
   // In SearchController
   public function search(Request $request)
   {
       $userLocation = $this->locationService->getUserLocation(request()->ip());
       return $this->contentVisibilityService->filterContent(
           User::query()->where('username', 'like', "%{$request->q}%"),
           $userLocation
       )->paginate(20);
   }
   ```

### Phase 5: Frontend Implementation (3-4 days)
1. **Create Components**
   - `BlockedLocations.vue`
   - `LocationSearch.vue`
   - `BlockedLocationList.vue`

2. **Update Store**
   ```javascript
   // settingsStore.js
   export const useSettingsStore = defineStore('settings', {
     state: () => ({
       blockedLocations: [],
       // ... other state
     }),
     actions: {
       async fetchBlockedLocations() {
         // Fetch blocked locations
       },
       async addBlockedLocation(location) {
         // Add blocked location
       },
       async removeBlockedLocation(id) {
         // Remove blocked location
       }
     }
   })
   ```

3. **Implement Location Search**
   - Add OpenStreetMap integration
   - Implement location autocomplete
   - Handle location selection

### Phase 6: Testing & Optimization (2-3 days)
1. **Unit Tests**
   - Test location detection
   - Test blocking logic
   - Test content filtering

2. **Integration Tests**
   - Test API endpoints
   - Test middleware
   - Test frontend components

3. **Performance Optimization**
   - Implement caching
   - Optimize database queries
   - Add necessary indexes

### Phase 7: Documentation & Deployment (1-2 days)
1. **API Documentation**
   - Document endpoints
   - Add usage examples
   - Include error handling

2. **User Documentation**
   - Create user guide
   - Document limitations
   - Add troubleshooting guide

3. **Deployment**
   - Update environment variables
   - Run migrations
   - Deploy frontend changes

## Required Packages

### Backend
1. **IP-API**
   - Purpose: IP-based geolocation
   - Cost: Free
   - Rate Limit: 45 requests per minute
   - Accuracy: Good for country/region level

2. **OpenStreetMap Nominatim**
   - Purpose: Location search and validation
   - Cost: Free
   - Rate Limit: 1 request per second
   - Can be self-hosted

### Frontend
1. **Leaflet with OpenStreetMap**
   - Purpose: Maps and location search
   - Cost: Free
   - Open source
   - Can be self-hosted

2. **Type Definitions**
   - Purpose: TypeScript support
   - Package: `@types/leaflet`

## Security Considerations
1. **Rate Limiting**
   - Implement rate limiting for location blocking
   - Cache IP-API responses
   - Implement request queuing for Nominatim

2. **Location Validation**
   - Validate location data before saving
   - Prevent duplicate entries
   - Sanitize location names

3. **Privacy**
   - Store only necessary location data
   - Implement proper data retention policies
   - Consider GDPR compliance

## Performance Considerations
1. **Caching**
   - Cache IP-API responses
   - Cache Nominatim responses
   - Implement Redis for frequently accessed locations
   - Cache filtered content queries

2. **Database Indexing**
   - Add appropriate indexes for location queries
   - Optimize location-based searches
   - Index blocked locations for faster filtering

3. **API Optimization**
   - Implement pagination for blocked locations list
   - Cache location data
   - Implement request queuing
   - Use eager loading for related data

## Timeline
1. **Phase 1 (Database & Basic Setup)**: 2-3 days
2. **Phase 2 (Location Detection)**: 2-3 days
3. **Phase 3 (Blocking Implementation)**: 3-4 days
4. **Phase 4 (Content Visibility)**: 4-5 days
5. **Phase 5 (Frontend Implementation)**: 3-4 days
6. **Phase 6 (Testing & Optimization)**: 2-3 days
7. **Phase 7 (Documentation & Deployment)**: 1-2 days

Total estimated time: 17-24 days 