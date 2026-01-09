<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Get user location from IP address
     *
     * @param string $ip
     * @return array|null
     */
    public function getUserLocation(string $ip): ?array
    {
        // Skip location detection for local IPs
        if ($this->isLocalIp($ip)) {
            Log::info('Local IP detected, skipping location detection', ['ip' => $ip]);
            return null;
        }

        return Cache::remember("user_location_{$ip}", now()->addHours(24), function() use ($ip) {
            try {
                Log::info('Fetching location for IP', ['ip' => $ip]);
                
                // Try ipapi.co first (more precise)
                $response = Http::timeout(5)->get("https://ipapi.co/{$ip}/json/");

                if (!$response->successful()) {
                    // Fallback to ip-api.com
                    $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country,countryCode,region,regionName,city,lat,lon,timezone'
                    ]);
                }

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Handle ipapi.co response format
                    if (isset($data['country_name'])) {
                        Log::info('Location detected successfully (ipapi.co)', [
                            'ip' => $ip,
                            'country' => $data['country_name'] ?? 'Unknown',
                            'country_code' => $data['country_code'] ?? 'Unknown',
                            'region' => $data['region'] ?? 'Unknown',
                            'city' => $data['city'] ?? 'Unknown'
                        ]);
                        
                        $locationData = [
                            'country' => $data['country_name'] ?? 'Unknown',
                            'country_code' => $data['country_code'] ?? 'Unknown',
                            'region' => $data['region'] ?? 'Unknown',
                            'city' => $data['city'] ?? 'Unknown',
                            'latitude' => $data['latitude'] ?? null,
                            'longitude' => $data['longitude'] ?? null,
                            'timezone' => $data['timezone'] ?? null,
                        ];
                    }
                    // Handle ip-api.com response format
                    elseif (isset($data['status']) && $data['status'] === 'success') {
                        Log::info('Location detected successfully (ip-api.com)', [
                            'ip' => $ip,
                            'country' => $data['country'] ?? 'Unknown',
                            'country_code' => $data['countryCode'] ?? 'Unknown'
                        ]);
                        
                        $locationData = [
                            'country' => $data['country'] ?? 'Unknown',
                            'country_code' => $data['countryCode'] ?? 'Unknown',
                            'region' => $data['regionName'] ?? 'Unknown',
                            'city' => $data['city'] ?? 'Unknown',
                            'latitude' => $data['lat'] ?? null,
                            'longitude' => $data['lon'] ?? null,
                            'timezone' => $data['timezone'] ?? null,
                        ];
                    } else {
                        Log::warning('Invalid response format from IP geolocation service', [
                            'ip' => $ip,
                            'data' => $data
                        ]);
                        return null;
                    }

                    Log::info('Location data processed', [
                        'ip' => $ip,
                        'raw_data' => $data,
                        'processed_data' => $locationData
                    ]);

                    return $locationData;
                }

                Log::warning('Failed to get location from IP-API', [
                    'ip' => $ip,
                    'response' => $response->body()
                ]);
                
                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching user location', [
                    'ip' => $ip,
                    'error' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }

    /**
     * Check if IP is local/private
     *
     * @param string $ip
     * @return bool
     */
    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) || 
               filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Validate location data
     *
     * @param array $location
     * @return array
     */
    public function validateLocation(array $location): array
    {
        return [
            'country' => $location['country'] ?? 'Unknown',
            'country_code' => strtoupper($location['country_code'] ?? 'XX'),
            'region' => $location['region'] ?? 'Unknown',
            'city' => $location['city'] ?? 'Unknown',
        ];
    }

    /**
     * Search for locations using OpenStreetMap Nominatim
     *
     * @param string $query
     * @return array
     */
    public function searchLocations(string $query): array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'FansApp/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'limit' => 10,
                    'countrycodes' => '', // Allow all countries
                ]);

            if ($response->successful()) {
                $results = $response->json();
                
                return collect($results)->map(function ($result) {
                    return [
                        'display_name' => $result['display_name'],
                        'country' => $result['address']['country'] ?? 'Unknown',
                        'country_code' => strtoupper($result['address']['country_code'] ?? 'XX'),
                        'region' => $result['address']['state'] ?? $result['address']['region'] ?? 'Unknown',
                        'city' => $result['address']['city'] ?? $result['address']['town'] ?? 'Unknown',
                        'latitude' => $result['lat'] ?? null,
                        'longitude' => $result['lon'] ?? null,
                    ];
                })->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error searching locations', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Reverse geocode coordinates to get location details
     *
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        return Cache::remember("reverse_geocode_{$latitude}_{$longitude}", now()->addHours(24), function() use ($latitude, $longitude) {
            try {
                Log::info('Reverse geocoding coordinates', ['lat' => $latitude, 'lon' => $longitude]);
                
                $response = Http::timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'json',
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $address = $data['address'] ?? [];
                    
                    Log::info('Reverse geocoding successful', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'country' => $address['country'] ?? 'Unknown'
                    ]);
                    
                    return [
                        'country' => $address['country'] ?? 'Unknown',
                        'country_code' => strtoupper($address['country_code'] ?? 'XX'),
                        'region' => $address['state'] ?? $address['region'] ?? 'Unknown',
                        'city' => $address['city'] ?? $address['town'] ?? 'Unknown',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ];
                }

                Log::warning('Failed to reverse geocode', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'response' => $response->body()
                ]);
                
                return null;
            } catch (\Exception $e) {
                Log::error('Error reverse geocoding', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'error' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }

    /**
     * Get country list for location blocking
     *
     * @return array
     */
    public function getCountryList(): array
    {
        return Cache::remember('country_list', now()->addDays(30), function() {
            // This is a simplified list. In production, you might want to use a more comprehensive list
            return [
                'AD' => 'Andorra', 'AE' => 'United Arab Emirates', 'AF' => 'Afghanistan', 'AG' => 'Antigua and Barbuda',
                'AI' => 'Anguilla', 'AL' => 'Albania', 'AM' => 'Armenia', 'AO' => 'Angola', 'AQ' => 'Antarctica',
                'AR' => 'Argentina', 'AS' => 'American Samoa', 'AT' => 'Austria', 'AU' => 'Australia', 'AW' => 'Aruba',
                'AX' => 'Åland Islands', 'AZ' => 'Azerbaijan', 'BA' => 'Bosnia and Herzegovina', 'BB' => 'Barbados',
                'BD' => 'Bangladesh', 'BE' => 'Belgium', 'BF' => 'Burkina Faso', 'BG' => 'Bulgaria', 'BH' => 'Bahrain',
                'BI' => 'Burundi', 'BJ' => 'Benin', 'BL' => 'Saint Barthélemy', 'BM' => 'Bermuda', 'BN' => 'Brunei',
                'BO' => 'Bolivia', 'BQ' => 'Caribbean Netherlands', 'BR' => 'Brazil', 'BS' => 'Bahamas', 'BT' => 'Bhutan',
                'BV' => 'Bouvet Island', 'BW' => 'Botswana', 'BY' => 'Belarus', 'BZ' => 'Belize', 'CA' => 'Canada',
                'CC' => 'Cocos Islands', 'CD' => 'DR Congo', 'CF' => 'Central African Republic', 'CG' => 'Republic of the Congo',
                'CH' => 'Switzerland', 'CI' => 'Côte d\'Ivoire', 'CK' => 'Cook Islands', 'CL' => 'Chile', 'CM' => 'Cameroon',
                'CN' => 'China', 'CO' => 'Colombia', 'CR' => 'Costa Rica', 'CU' => 'Cuba', 'CV' => 'Cape Verde',
                'CW' => 'Curaçao', 'CX' => 'Christmas Island', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DE' => 'Germany',
                'DJ' => 'Djibouti', 'DK' => 'Denmark', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'DZ' => 'Algeria',
                'EC' => 'Ecuador', 'EE' => 'Estonia', 'EG' => 'Egypt', 'EH' => 'Western Sahara', 'ER' => 'Eritrea',
                'ES' => 'Spain', 'ET' => 'Ethiopia', 'FI' => 'Finland', 'FJ' => 'Fiji', 'FK' => 'Falkland Islands',
                'FM' => 'Micronesia', 'FO' => 'Faroe Islands', 'FR' => 'France', 'GA' => 'Gabon', 'GB' => 'United Kingdom',
                'GD' => 'Grenada', 'GE' => 'Georgia', 'GF' => 'French Guiana', 'GG' => 'Guernsey', 'GH' => 'Ghana',
                'GI' => 'Gibraltar', 'GL' => 'Greenland', 'GM' => 'Gambia', 'GN' => 'Guinea', 'GP' => 'Guadeloupe',
                'GQ' => 'Equatorial Guinea', 'GR' => 'Greece', 'GS' => 'South Georgia', 'GT' => 'Guatemala', 'GU' => 'Guam',
                'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HK' => 'Hong Kong', 'HM' => 'Heard Island and McDonald Islands',
                'HN' => 'Honduras', 'HR' => 'Croatia', 'HT' => 'Haiti', 'HU' => 'Hungary', 'ID' => 'Indonesia',
                'IE' => 'Ireland', 'IL' => 'Israel', 'IM' => 'Isle of Man', 'IN' => 'India', 'IO' => 'British Indian Ocean Territory',
                'IQ' => 'Iraq', 'IR' => 'Iran', 'IS' => 'Iceland', 'IT' => 'Italy', 'JE' => 'Jersey', 'JM' => 'Jamaica',
                'JO' => 'Jordan', 'JP' => 'Japan', 'KE' => 'Kenya', 'KG' => 'Kyrgyzstan', 'KH' => 'Cambodia',
                'KI' => 'Kiribati', 'KM' => 'Comoros', 'KN' => 'Saint Kitts and Nevis', 'KP' => 'North Korea',
                'KR' => 'South Korea', 'KW' => 'Kuwait', 'KY' => 'Cayman Islands', 'KZ' => 'Kazakhstan', 'LA' => 'Laos',
                'LB' => 'Lebanon', 'LC' => 'Saint Lucia', 'LI' => 'Liechtenstein', 'LK' => 'Sri Lanka', 'LR' => 'Liberia',
                'LS' => 'Lesotho', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia', 'LY' => 'Libya',
                'MA' => 'Morocco', 'MC' => 'Monaco', 'MD' => 'Moldova', 'ME' => 'Montenegro', 'MF' => 'Saint Martin',
                'MG' => 'Madagascar', 'MH' => 'Marshall Islands', 'MK' => 'North Macedonia', 'ML' => 'Mali', 'MM' => 'Myanmar',
                'MN' => 'Mongolia', 'MO' => 'Macao', 'MP' => 'Northern Mariana Islands', 'MQ' => 'Martinique',
                'MR' => 'Mauritania', 'MS' => 'Montserrat', 'MT' => 'Malta', 'MU' => 'Mauritius', 'MV' => 'Maldives',
                'MW' => 'Malawi', 'MX' => 'Mexico', 'MY' => 'Malaysia', 'MZ' => 'Mozambique', 'NA' => 'Namibia',
                'NC' => 'New Caledonia', 'NE' => 'Niger', 'NF' => 'Norfolk Island', 'NG' => 'Nigeria', 'NI' => 'Nicaragua',
                'NL' => 'Netherlands', 'NO' => 'Norway', 'NP' => 'Nepal', 'NR' => 'Nauru', 'NU' => 'Niue',
                'NZ' => 'New Zealand', 'OM' => 'Oman', 'PA' => 'Panama', 'PE' => 'Peru', 'PF' => 'French Polynesia',
                'PG' => 'Papua New Guinea', 'PH' => 'Philippines', 'PK' => 'Pakistan', 'PL' => 'Poland',
                'PM' => 'Saint Pierre and Miquelon', 'PN' => 'Pitcairn Islands', 'PR' => 'Puerto Rico', 'PS' => 'Palestine',
                'PT' => 'Portugal', 'PW' => 'Palau', 'PY' => 'Paraguay', 'QA' => 'Qatar', 'RE' => 'Réunion',
                'RO' => 'Romania', 'RS' => 'Serbia', 'RU' => 'Russia', 'RW' => 'Rwanda', 'SA' => 'Saudi Arabia',
                'SB' => 'Solomon Islands', 'SC' => 'Seychelles', 'SD' => 'Sudan', 'SE' => 'Sweden', 'SG' => 'Singapore',
                'SH' => 'Saint Helena', 'SI' => 'Slovenia', 'SJ' => 'Svalbard and Jan Mayen', 'SK' => 'Slovakia',
                'SL' => 'Sierra Leone', 'SM' => 'San Marino', 'SN' => 'Senegal', 'SO' => 'Somalia', 'SR' => 'Suriname',
                'SS' => 'South Sudan', 'ST' => 'São Tomé and Príncipe', 'SV' => 'El Salvador', 'SX' => 'Sint Maarten',
                'SY' => 'Syria', 'SZ' => 'Eswatini', 'TC' => 'Turks and Caicos Islands', 'TD' => 'Chad',
                'TF' => 'French Southern Territories', 'TG' => 'Togo', 'TH' => 'Thailand', 'TJ' => 'Tajikistan',
                'TK' => 'Tokelau', 'TL' => 'East Timor', 'TM' => 'Turkmenistan', 'TN' => 'Tunisia', 'TO' => 'Tonga',
                'TR' => 'Turkey', 'TT' => 'Trinidad and Tobago', 'TV' => 'Tuvalu', 'TW' => 'Taiwan', 'TZ' => 'Tanzania',
                'UA' => 'Ukraine', 'UG' => 'Uganda', 'UM' => 'United States Minor Outlying Islands', 'US' => 'United States',
                'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VA' => 'Vatican City', 'VC' => 'Saint Vincent and the Grenadines',
                'VE' => 'Venezuela', 'VG' => 'British Virgin Islands', 'VI' => 'United States Virgin Islands',
                'VN' => 'Vietnam', 'VU' => 'Vanuatu', 'WF' => 'Wallis and Futuna', 'WS' => 'Samoa', 'YE' => 'Yemen',
                'YT' => 'Mayotte', 'ZA' => 'South Africa', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe'
            ];
        });
    }
} 