# Tracking Links Documentation

## Overview
The tracking links system allows creators to create and manage tracking links to monitor traffic, subscriptions, and purchases originating from their marketing efforts. Each tracking link can be used to track:
- Click events (with referrer information)
- Subscription events
- Purchase events
- Conversion rates and analytics

## Database Structure

### Tracking Links Table
```sql
tracking_links
- id
- creator_id (foreign key to users)
- name
- slug (unique)
- destination_url
- description (nullable)
- is_active (boolean)
- created_at
- updated_at
```

### Tracking Link Events Table
```sql
tracking_link_events
- id
- tracking_link_id (foreign key to tracking_links)
- event_type (click, subscription, purchase)
- ip_address
- user_agent
- referrer_url
- referrer_domain
- user_id (nullable, foreign key to users)
- subscription_id (nullable, foreign key to subscriptions)
- transaction_id (nullable, foreign key to transactions)
- metadata (json)
- created_at
- updated_at
```

## API Routes

### Authentication Required Routes

#### List Tracking Links
```http
GET /api/tracking-links
```
Response:
```json
{
    "data": [
        {
            "id": 1,
            "name": "My Tracking Link",
            "slug": "my-tracking-link-abc123",
            "destination_url": "https://example.com",
            "description": "Optional description",
            "is_active": true,
            "clicks_count": 150,
            "subscriptions_count": 10,
            "purchases_count": 5,
            "created_at": "2024-03-21T10:00:00Z",
            "updated_at": "2024-03-21T10:00:00Z"
        }
    ]
}
```

#### Create Tracking Link
```http
POST /api/tracking-links
```
Request Body:
```json
{
    "name": "My Tracking Link",
    "destination_url": "https://example.com",
    "description": "Optional description"
}
```
Response:
```json
{
    "id": 1,
    "name": "My Tracking Link",
    "slug": "my-tracking-link-abc123",
    "destination_url": "https://example.com",
    "description": "Optional description",
    "is_active": true,
    "created_at": "2024-03-21T10:00:00Z",
    "updated_at": "2024-03-21T10:00:00Z"
}
```

#### Get Tracking Link Details
```http
GET /api/tracking-links/{trackingLink}
```
Response:
```json
{
    "id": 1,
    "name": "My Tracking Link",
    "slug": "my-tracking-link-abc123",
    "destination_url": "https://example.com",
    "description": "Optional description",
    "is_active": true,
    "clicks": [
        {
            "id": 1,
            "ip_address": "192.168.1.1",
            "user_agent": "Mozilla/5.0...",
            "referrer_url": "https://twitter.com",
            "referrer_domain": "twitter.com",
            "created_at": "2024-03-21T10:00:00Z"
        }
    ],
    "subscriptions": [...],
    "purchases": [...],
    "created_at": "2024-03-21T10:00:00Z",
    "updated_at": "2024-03-21T10:00:00Z"
}
```

#### Update Tracking Link
```http
PUT /api/tracking-links/{trackingLink}
```
Request Body:
```json
{
    "name": "Updated Name",
    "destination_url": "https://new-example.com",
    "description": "Updated description",
    "is_active": true
}
```

#### Delete Tracking Link
```http
DELETE /api/tracking-links/{trackingLink}
```

#### Get Tracking Link Statistics
```http
GET /api/tracking-links/{trackingLink}/statistics
```
Response:
```json
{
    "total_clicks": 150,
    "total_subscriptions": 10,
    "total_purchases": 5,
    "clicks_by_date": [
        {
            "date": "2024-03-21",
            "count": 50
        }
    ],
    "clicks_by_referrer": [
        {
            "referrer_domain": "twitter.com",
            "count": 75
        }
    ],
    "subscriptions_by_date": [...],
    "purchases_by_date": [...],
    "conversion_rate": 10.0
}
```

### Public Route (No Authentication Required)

#### Track Link Click
```http
GET /api/track/{slug}
```
This route will:
1. Record the click event
2. Store the tracking link ID in the user's session
3. Redirect to the destination URL

## Frontend Integration

### Creating a Tracking Link
```javascript
// Example using axios
const createTrackingLink = async (data) => {
    try {
        const response = await axios.post('/api/tracking-links', {
            name: data.name,
            destination_url: data.destinationUrl,
            description: data.description
        });
        return response.data;
    } catch (error) {
        console.error('Error creating tracking link:', error);
        throw error;
    }
};
```

### Displaying Tracking Links List
```javascript
// Example using React
const TrackingLinksList = () => {
    const [trackingLinks, setTrackingLinks] = useState([]);

    useEffect(() => {
        const fetchTrackingLinks = async () => {
            try {
                const response = await axios.get('/api/tracking-links');
                setTrackingLinks(response.data.data);
            } catch (error) {
                console.error('Error fetching tracking links:', error);
            }
        };

        fetchTrackingLinks();
    }, []);

    return (
        <div>
            {trackingLinks.map(link => (
                <div key={link.id} className="tracking-link-card">
                    <h3>{link.name}</h3>
                    <p>Destination: {link.destination_url}</p>
                    <p>Clicks: {link.clicks_count}</p>
                    <p>Subscriptions: {link.subscriptions_count}</p>
                    <p>Purchases: {link.purchases_count}</p>
                    <a href={`/api/track/${link.slug}`} target="_blank">
                        {`${window.location.origin}/api/track/${link.slug}`}
                    </a>
                </div>
            ))}
        </div>
    );
};
```

### Displaying Statistics
```javascript
// Example using React and Chart.js
const TrackingLinkStats = ({ trackingLinkId }) => {
    const [stats, setStats] = useState(null);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const response = await axios.get(`/api/tracking-links/${trackingLinkId}/statistics`);
                setStats(response.data);
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        };

        fetchStats();
    }, [trackingLinkId]);

    if (!stats) return <div>Loading...</div>;

    return (
        <div>
            <div className="stats-summary">
                <div className="stat-card">
                    <h4>Total Clicks</h4>
                    <p>{stats.total_clicks}</p>
                </div>
                <div className="stat-card">
                    <h4>Total Subscriptions</h4>
                    <p>{stats.total_subscriptions}</p>
                </div>
                <div className="stat-card">
                    <h4>Total Purchases</h4>
                    <p>{stats.total_purchases}</p>
                </div>
                <div className="stat-card">
                    <h4>Conversion Rate</h4>
                    <p>{stats.conversion_rate}%</p>
                </div>
            </div>

            <div className="charts">
                <Line
                    data={{
                        labels: stats.clicks_by_date.map(d => d.date),
                        datasets: [{
                            label: 'Clicks',
                            data: stats.clicks_by_date.map(d => d.count)
                        }]
                    }}
                />

                <Pie
                    data={{
                        labels: stats.clicks_by_referrer.map(r => r.referrer_domain),
                        datasets: [{
                            data: stats.clicks_by_referrer.map(r => r.count)
                        }]
                    }}
                />
            </div>
        </div>
    );
};
```

## Best Practices

1. **Link Management**
   - Use descriptive names for tracking links
   - Keep track of where each link is being used
   - Regularly review and clean up unused links

2. **Analytics**
   - Monitor conversion rates regularly
   - Compare performance across different marketing channels
   - Use the data to optimize marketing strategies

3. **Security**
   - Always validate and sanitize URLs
   - Implement rate limiting for click tracking
   - Monitor for suspicious activity

4. **User Experience**
   - Use short, memorable slugs
   - Consider using a URL shortener for cleaner links
   - Provide clear analytics visualization

## Error Handling

The API will return appropriate HTTP status codes and error messages:

- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

Example error response:
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Error details"]
    }
}
``` 