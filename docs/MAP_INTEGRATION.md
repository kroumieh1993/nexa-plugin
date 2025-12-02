# Map Integration Guide

This document describes how to configure and use the map-based location features in the Nexa Real Estate plugin.

## Overview

The plugin supports displaying property locations on interactive maps. Properties can have their latitude and longitude stored, allowing agents to pin locations and users to view properties on maps.

## Supported Map Providers

### 1. Leaflet / OpenStreetMap (Default)

Leaflet with OpenStreetMap is the default map provider. It is:
- **Free to use** - No API key required
- **Open source** - Based on community-maintained map data
- **Full-featured** - Supports markers, popups, clustering, and more

### 2. Google Maps (Optional)

Google Maps can be used as an alternative provider if you prefer Google's map styling and features. This requires:
- A Google Maps API key
- A Google Cloud Platform account with billing enabled

## Configuration

### Setting Up Map Provider

1. Go to **Settings → Nexa Real Estate** in your WordPress admin
2. Scroll to the **Map Settings** section
3. Select your preferred **Map Provider**:
   - **Leaflet (OpenStreetMap)** - Free, no API key required
   - **Google Maps** - Requires API key

### Obtaining a Google Maps API Key

If you choose Google Maps:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - Maps JavaScript API
   - Places API (optional, for address search)
4. Go to **APIs & Services → Credentials**
5. Click **Create Credentials → API Key**
6. Copy the API key
7. Paste it in the **Google Maps API Key** field in Nexa settings

**Important**: Restrict your API key to your website's domain for security.

## Features

### Location Tab in Property Modal

When creating or editing a property, you'll find a **Location** tab where you can:

1. **Click on the map** to place a pin at the property's location
2. **Drag the marker** to adjust the position
3. **Enter coordinates manually** in the Latitude and Longitude fields

The coordinates are validated to ensure:
- Latitude is between -90 and 90
- Longitude is between -180 and 180

### Single Property Page

When a property has location data, a **Location** section appears on the single property page showing:
- The property address (if available)
- An interactive map with the property marker
- Users can zoom and pan to explore the area

### Properties List Page

The properties list page displays all properties on an interactive map with:
- Clustered markers for areas with multiple properties
- Clickable markers showing property popups
- Property information in popups with links to detail pages

## SaaS API Integration

### Field Names

The SaaS backend (nexa-saas-api) stores location data using these fields:

- `latitude` - Decimal value between -90 and 90
- `longitude` - Decimal value between -180 and 180

### API Endpoints

Location data is included in the standard property endpoints:

- `POST /api/properties` - Create property with location
- `PUT /api/properties/{id}` - Update property location
- `GET /api/properties` - Returns properties with location data
- `GET /api/properties/{id}` - Returns single property with location

### Example Payload

```json
{
  "title": "Modern Villa",
  "city": "Beirut",
  "category": "buy",
  "latitude": 33.8886,
  "longitude": 35.4955,
  "address": "123 Main Street, Beirut"
}
```

## Troubleshooting

### Map Not Displaying

1. Check browser console for JavaScript errors
2. Verify Leaflet assets are loading (check Network tab)
3. Ensure the map container has proper dimensions

### Markers Not Appearing

1. Verify properties have valid latitude/longitude values
2. Check that coordinates are within valid ranges
3. Ensure the SaaS API is returning location data

### Google Maps Not Working

1. Verify your API key is correct
2. Check that required APIs are enabled in Google Cloud Console
3. Ensure your domain is whitelisted for the API key
4. Check for billing issues on your Google Cloud account

## Technical Details

### CSS Classes

- `.nexa-map-container` - Base map container
- `.nexa-map-picker` - Map picker in forms
- `.nexa-map-single` - Map on single property page
- `.nexa-map-list` - Map on properties list page
- `.nexa-location-section` - Location section wrapper

### JavaScript Functions

- `initFrontMap()` - Initialize the frontend map picker
- `updateFrontMapMarker(lat, lng)` - Update/create map marker
- `validateCoordinates()` - Validate lat/lng input fields

## Future Enhancements

- Address autocomplete/geocoding
- Radius search (find properties within X km)
- Custom map markers based on property type
- Directions integration
- Street View support (Google Maps only)
