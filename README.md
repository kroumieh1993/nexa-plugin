# Nexa Real Estate Plugin

A WordPress plugin that connects your site to the Nexa real estate SaaS and displays properties via shortcodes.

## Features

- **Property Display**: Show properties using the `[nexa_properties]` shortcode
- **Property Search Bar**: Quick search bar shortcode for homepages that redirects to filtered property listings
- **Single Property Pages**: Dedicated pages for each property with image gallery, details, and floor plans
- **Interactive Maps**: Display property locations on maps using Leaflet (OpenStreetMap) or Google Maps
- **Map View**: Two-column layout showing properties list alongside an interactive map with clustered markers
- **Filtering**: Advanced search and filter functionality for properties
- **Centralized Configuration**: Shortcode appearance and settings are managed through the SaaS dashboard
- **Image API**: REST API endpoints for image management from the SaaS platform

## Installation

1. Upload the plugin files to `/wp-content/plugins/nexa-real-estate/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Nexa Real Estate to configure your API token
4. Configure map settings (optional - Leaflet works without any API key)

## Configuration

### API Token
Get your API token from the Nexa Property Suite SaaS dashboard and paste it in Settings → Nexa Real Estate.

### Map Settings
Choose between Leaflet (free, no API key required) or Google Maps (requires API key).
See [docs/MAP_INTEGRATION.md](docs/MAP_INTEGRATION.md) for detailed configuration instructions.

### SaaS Dashboard
Property management, custom parameters (cities, property types), and shortcode configurations are managed through the SaaS dashboard at [saas.nexapropertysuite.com](https://saas.nexapropertysuite.com).

## Shortcodes

### Properties List
```
[nexa_properties]
```

Displays a grid of properties with optional filtering and map view. Configuration (layout, columns, colors, visible fields) is fetched from the SaaS API.

**Attributes:**
- `city` - Filter by city
- `category` - Filter by category (rent/buy)
- `property_type` - Filter by property type
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `per_page` - Number of properties per page
- `show_filter` - Show/hide filter form (true/false)
- `show_map` - Show/hide map view (true/false)

### Property Search Bar
```
[nexa_property_search]
```

A quick search bar that can be placed on any page (e.g., homepage) to allow users to filter properties and be redirected to the properties listing page with the selected filters applied.

**Attributes:**
- `properties_page` - URL of the page containing the `[nexa_properties]` shortcode (required for proper redirection)
- `show_city` - Show/hide city field (true/false, default: true)
- `show_category` - Show/hide category field (true/false, default: true)
- `show_type` - Show/hide property type field (true/false, default: true)
- `show_price` - Show/hide price range fields (true/false, default: true)
- `show_bedrooms` - Show/hide bedrooms field (true/false, default: true)
- `show_bathrooms` - Show/hide bathrooms field (true/false, default: true)

**Example:**
```
[nexa_property_search properties_page="/properties/"]
```

## REST API Endpoints

The plugin provides REST API endpoints for the SaaS platform to manage images:

### Upload Image
`POST /wp-json/nexa-plugin/v1/upload-image`

Uploads an image to the WordPress media library.

**Headers:**
- `X-AGENCY-TOKEN` - Your API token (required)

**Body:**
- `image` - The image file (multipart/form-data)

**Response:**
```json
{
  "success": true,
  "attachment_id": 123,
  "url": "https://example.com/wp-content/uploads/2024/01/image.jpg"
}
```

### Delete Image
`DELETE /wp-json/nexa-plugin/v1/delete-image`

Deletes an image from the WordPress media library.

**Headers:**
- `X-AGENCY-TOKEN` - Your API token (required)

**Parameters:**
- `url` - The URL of the image to delete

**Response:**
```json
{
  "success": true,
  "message": "Image deleted successfully."
}
```

## Documentation

- [Shortcodes Guide](docs/SHORTCODES.md) - Complete documentation for all shortcodes including attributes and examples
- [Map Integration Guide](docs/MAP_INTEGRATION.md) - How to configure and use map features

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Active Nexa Property Suite subscription

## License

MIT License
