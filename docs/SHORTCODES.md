# Shortcodes Guide

This document provides comprehensive documentation for all shortcodes available in the Nexa Real Estate plugin.

## Overview

Shortcodes allow you to embed Nexa Real Estate features anywhere on your WordPress site. Simply add the shortcode to any page, post, or widget area.

## Available Shortcodes

### 1. Properties List - `[nexa_properties]`

Displays a grid of properties with optional filtering and map view.

```
[nexa_properties]
```

#### Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `city` | string | `""` | Pre-filter by city name |
| `category` | string | `""` | Pre-filter by category (`rent` or `buy`) |
| `property_type` | string | `""` | Pre-filter by property type (e.g., `Apartment`, `Villa`) |
| `min_price` | number | `""` | Pre-filter by minimum price |
| `max_price` | number | `""` | Pre-filter by maximum price |
| `per_page` | number | `10` | Number of properties displayed per page |
| `show_filter` | boolean | `true` | Show/hide the advanced search filter panel |
| `show_map` | boolean | `true` | Show/hide the interactive map view |

#### Examples

**Basic usage:**
```
[nexa_properties]
```

**Show only rental properties:**
```
[nexa_properties category="rent"]
```

**Show properties in a specific city with price range:**
```
[nexa_properties city="Beirut" min_price="100000" max_price="500000"]
```

**Hide the filter panel:**
```
[nexa_properties show_filter="false"]
```

**Hide the map view:**
```
[nexa_properties show_map="false"]
```

#### URL Parameters

The shortcode also responds to URL parameters, allowing users to filter properties via the address bar or form submissions:

- `nexa_city` - Filter by city
- `nexa_category` - Filter by category (rent/buy)
- `nexa_type` - Filter by property type
- `nexa_min_price` - Minimum price
- `nexa_max_price` - Maximum price
- `nexa_bedrooms` - Minimum number of bedrooms
- `nexa_bathrooms` - Minimum number of bathrooms

**Example URL:**
```
/properties/?nexa_city=Beirut&nexa_category=buy&nexa_bedrooms=3
```

---

### 2. Property Search Bar - `[nexa_property_search]`

Displays a compact search bar that can be placed on any page (e.g., homepage) to allow users to quickly search for properties. When submitted, it redirects to the properties listing page with the selected filters applied.

```
[nexa_property_search]
```

#### Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `properties_page` | string | `/` | URL of the page containing the `[nexa_properties]` shortcode |
| `show_city` | boolean | `true` | Show/hide the city input field |
| `show_category` | boolean | `true` | Show/hide the category dropdown (rent/buy) |
| `show_type` | boolean | `true` | Show/hide the property type input field |
| `show_price` | boolean | `true` | Show/hide the price range fields |
| `show_bedrooms` | boolean | `true` | Show/hide the bedrooms dropdown |
| `show_bathrooms` | boolean | `true` | Show/hide the bathrooms dropdown |

#### Examples

**Basic usage (requires properties_page to be set):**
```
[nexa_property_search properties_page="/properties/"]
```

**Show only essential fields:**
```
[nexa_property_search properties_page="/properties/" show_bathrooms="false" show_bedrooms="false"]
```

**Minimal search (category and city only):**
```
[nexa_property_search properties_page="/properties/" show_type="false" show_price="false" show_bedrooms="false" show_bathrooms="false"]
```

#### How It Works

1. Place the `[nexa_property_search]` shortcode on your homepage or any landing page
2. Users fill in their search criteria (city, category, price range, etc.)
3. When they click "Search Properties", they are redirected to the properties page
4. The URL includes the filter parameters (e.g., `?nexa_city=Beirut&nexa_category=rent`)
5. The `[nexa_properties]` shortcode on the target page reads these parameters and displays filtered results

#### Integration with Properties List

The search bar uses the same URL parameter names as the properties list shortcode, ensuring seamless integration:

| Search Bar Field | URL Parameter | Properties List Response |
|------------------|---------------|--------------------------|
| City | `nexa_city` | Filters by city |
| Category | `nexa_category` | Filters by rent/buy |
| Property Type | `nexa_type` | Filters by property type |
| Min Price | `nexa_min_price` | Filters by minimum price |
| Max Price | `nexa_max_price` | Filters by maximum price |
| Bedrooms | `nexa_bedrooms` | Filters by minimum bedrooms |
| Bathrooms | `nexa_bathrooms` | Filters by minimum bathrooms |

---

### 3. Agency Dashboard - `[nexa_agency_dashboard]`

Displays a full-featured dashboard for managing properties. Only accessible to logged-in users with appropriate permissions.

```
[nexa_agency_dashboard]
```

#### Requirements

- User must be logged in
- User must have either:
  - The `manage_nexa_properties` capability, OR
  - The `manage_options` capability (WordPress administrators)

#### Features

- View all agency properties
- Create new properties
- Edit existing properties
- Delete properties
- Upload property images
- Add floor plans
- Set property locations on a map

---

## Best Practices

### Homepage Setup

For an effective homepage search experience:

1. Create a page for your properties (e.g., `/properties/`)
2. Add `[nexa_properties]` to that page
3. On your homepage, add `[nexa_property_search properties_page="/properties/"]`

### Performance Tips

- Use `show_map="false"` on pages where map view is not needed
- Pre-filter properties using shortcode attributes to reduce API calls
- Use appropriate `per_page` values based on your design

### Styling

All shortcodes include built-in responsive CSS. Classes follow the `nexa-` prefix convention:

- `.nexa-properties-wrapper` - Main properties container
- `.nexa-properties-grid` - Property cards grid
- `.nexa-property-card` - Individual property card
- `.nexa-property-search-bar` - Search bar container
- `.nexa-search-form` - Search form element
- `.nexa-search-field` - Individual search field

---

## Troubleshooting

### Properties Not Displaying

1. Verify API token is configured in **Settings → Nexa Real Estate**
2. Check browser console for JavaScript errors
3. Ensure the API endpoint is accessible

### Search Not Redirecting

1. Verify the `properties_page` attribute is set correctly
2. Ensure the target page exists and contains `[nexa_properties]`
3. Check that the URL is relative (e.g., `/properties/`) or absolute

### Filters Not Working

1. Verify URL parameters are being passed correctly
2. Check that property data matches filter criteria (case-sensitive)
3. Ensure the API supports the filter parameters being used
