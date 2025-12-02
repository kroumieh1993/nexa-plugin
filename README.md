# Nexa Real Estate Plugin

A WordPress plugin that connects your site to the Nexa real estate SaaS and displays properties via shortcodes.

## Features

- **Property Management**: Create, edit, and delete properties through the admin interface or frontend dashboard
- **Property Display**: Show properties using the `[nexa_properties]` shortcode
- **Single Property Pages**: Dedicated pages for each property with image gallery, details, and floor plans
- **Interactive Maps**: Display property locations on maps using Leaflet (OpenStreetMap) or Google Maps
- **Location Picker**: Pin property locations on a map when creating/editing properties
- **Map View**: Two-column layout showing properties list alongside an interactive map with clustered markers
- **Filtering**: Advanced search and filter functionality for properties

## Installation

1. Upload the plugin files to `/wp-content/plugins/nexa-real-estate/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Nexa Real Estate to configure your API token
4. Configure map settings (optional - Leaflet works without any API key)

## Configuration

### API Token
Get your API token from the Nexa Property Suite and paste it in Settings → Nexa Real Estate.

### Map Settings
Choose between Leaflet (free, no API key required) or Google Maps (requires API key).
See [docs/MAP_INTEGRATION.md](docs/MAP_INTEGRATION.md) for detailed configuration instructions.

## Shortcodes

### Properties List
```
[nexa_properties]
```

**Attributes:**
- `city` - Filter by city
- `category` - Filter by category (rent/buy)
- `property_type` - Filter by property type
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `per_page` - Number of properties per page
- `show_filter` - Show/hide filter form (true/false)
- `show_map` - Show/hide map view (true/false)

### Agency Dashboard
```
[nexa_agency_dashboard]
```
Displays a full agency dashboard with property management capabilities.

## Documentation

- [Map Integration Guide](docs/MAP_INTEGRATION.md) - How to configure and use map features

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Active Nexa Property Suite subscription

## License

MIT License
