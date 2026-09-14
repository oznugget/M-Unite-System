// map.js – handles Leaflet map, data fetching, filtering, and view toggles

// ------------------------------------------------------------------
// CONFIGURATION – point to your PHP backend
// ------------------------------------------------------------------
const API_BASE = 'map.php';  // same file handles both HTML and API

// ------------------------------------------------------------------
// GLOBALS
// ------------------------------------------------------------------
let map, pinLayer, heatLayers = {};
let allIssues = [];
let categoriesMap = {};   // id -> { name, color }
let currentView = 'pins'; // 'pins' | 'heat'
let currentFilters = {
    category_id: '',
    street: '',
    status: ''
};

// ------------------------------------------------------------------
// INIT MAP
// ------------------------------------------------------------------
function initMap() {
    map = L.map('issue-map', { scrollWheelZoom: false }).setView([-33.3080, 26.5340], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);
    pinLayer = L.layerGroup().addTo(map);
}

const geocodeCache = new Map();

async function geocodeAddress(street_number, street_name, suburb, town, postal_code, isFallback = false) {
    // Build address string. If it's the fallback attempt, ignore street_number and suburb.
    const parts = isFallback 
        ? [street_name, town] 
        : [street_number, street_name, suburb, town, postal_code].filter(p => p && p.trim() !== '');
        
    const address = parts.join(', ');
    if (!address) return null;

    if (geocodeCache.has(address)) {
        return geocodeCache.get(address);
    }

    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
    
    try {
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const data = await response.json();
        
        if (data && data.length > 0 && data[0].lat !== undefined && data[0].lon !== undefined) {
            const coords = { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
            geocodeCache.set(address, coords);
            return coords;
        } else {
            // If the strict address wasn't found, try again with just the street and town
            if (!isFallback && street_name && town) {
                console.warn(`Could not find precise address for "${address}". Trying broader search...`);
                // Wait 1.1s before firing the fallback request to respect limits
                await new Promise(resolve => setTimeout(resolve, 1100)); 
                return await geocodeAddress(null, street_name, null, town, null, true);
            }
            geocodeCache.set(address, null);
            return null;
        }
    } catch (error) {
        console.warn('Geocoding error for', address, error);
        geocodeCache.set(address, null);
        return null;
    }
}


// ------------------------------------------------------------------
// FETCH FILTER OPTIONS (categories, streets)
// ------------------------------------------------------------------
async function fetchFilterOptions() {
    try {
        // 1) Categories
        const catRes = await fetch(`${API_BASE}?action=get_categories`);
        const categories = await catRes.json();
        const catSelect = document.getElementById('filterCategory');
        catSelect.innerHTML = '<option value="">All Categories</option>';
        categories.forEach(c => {
            categoriesMap[c.id] = { name: c.name, color: c.color };
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            catSelect.appendChild(opt);
        });
        buildLegend(categories);

        // 2) Streets
        const streetRes = await fetch(`${API_BASE}?action=get_streets`);
        const streets = await streetRes.json();
        const streetSelect = document.getElementById('filterStreet');
        streetSelect.innerHTML = '<option value="">All Streets</option>';
        streets.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            streetSelect.appendChild(opt);
        });
    } catch (err) {
        console.warn('Could not fetch filter options, using fallback.', err);
        // Fallback static categories
        const fallback = [
            { id: 1, name: 'Water Leak', color: '#3399CC' },
            { id: 2, name: 'Road Defect', color: '#F36E39' },
            { id: 3, name: 'Electrical Fault', color: '#E3B505' },
            { id: 4, name: 'Sanitation', color: '#6B8E23' }
        ];
        const catSelect = document.getElementById('filterCategory');
        catSelect.innerHTML = '<option value="">All Categories</option>';
        fallback.forEach(c => {
            categoriesMap[c.id] = { name: c.name, color: c.color };
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            catSelect.appendChild(opt);
        });
        buildLegend(fallback);
    }
}

function buildLegend(categories) {
    const container = document.getElementById('mapLegend');
    container.innerHTML = '';
    categories.forEach(c => {
        const div = document.createElement('div');
        div.className = 'legend-item';
        div.innerHTML = `
            <span class="legend-dot" style="background:${c.color};"></span>
            ${c.name}
        `;
        container.appendChild(div);
    });
}

// ------------------------------------------------------------------
// FETCH REPORTS (with filters)
// ------------------------------------------------------------------
async function fetchReports(filters = {}) {
    const params = new URLSearchParams();
    params.append('action', 'get_reports');
    if (filters.category_id) params.append('category_id', filters.category_id);
    if (filters.street) params.append('street', filters.street);
    if (filters.status) params.append('status', filters.status);

    const url = `${API_BASE}?${params.toString()}`;
    try {
        const res = await fetch(url);
        const data = await res.json();
        return data;
    } catch (err) {
        console.warn('Could not fetch reports, using fallback.', err);
        return getFallbackReports(filters);
    }
}

// Fallback dummy data (only used if API fails)
function getFallbackReports(filters) {
    const dummy = [
        {id: 1, title: 'Burst water main', category_id: 1, latitude: -33.3120, longitude: 26.5225, street_name: 'High Street, CBD', status: 'Pending' },
        {id: 2, title: 'Large pothole', category_id: 2, latitude: -33.2980, longitude: 26.5470, street_name: 'Joza', status: 'In Progress' },
        {id: 3, title: 'Downed power line', category_id: 3, latitude: -33.3050, longitude: 26.5380, street_name: 'Fingo Village', status: 'Pending' },
        {id: 4, title: 'Leaking pipe', category_id: 1, latitude: -33.2900, longitude: 26.5520, street_name: 'Extension 6', status: 'Pending' },
        {id: 5, title: 'Collapsed storm drain', category_id: 2, latitude: -33.3115, longitude: 26.5200, street_name: 'Somerset Street', status: 'Resolved' },
        {id: 6, title: 'No water supply', category_id: 1, latitude: -33.2850, longitude: 26.5550, street_name: 'Vukani', status: 'Pending' },
        {id: 7, title: 'Flickering streetlights', category_id: 3, latitude: -33.3000, longitude: 26.5450, street_name: 'Grahamstown East', status: 'In Progress' },
        {id: 8, title: 'Road surface erosion', category_id: 2, latitude: -33.3180, longitude: 26.5150, street_name: 'Sunnyside', status: 'Pending' },
        {id: 9, title: 'Sewage overflow', category_id: 4, latitude: -33.3020, longitude: 26.5320, street_name: 'Tantyi', status: 'Pending' },
        {id: 10, title: 'Blocked drain', category_id: 4, latitude: -33.3200, longitude: 26.5100, street_name: 'Hooggenoeg', status: 'Resolved' },
        {id: 11, title: 'Overflowing bin area', category_id: 4, latitude: -33.2930, longitude: 26.5400, street_name: 'Extension 9', status: 'Pending' },
        {id: 12, title: 'Faulty traffic light', category_id: 3, latitude: -33.3070, longitude: 26.5260, street_name: 'Beaufort Street', status: 'In Progress' }
    ];
    let filtered = dummy;
    if (filters.category_id) {
        filtered = filtered.filter(r => r.category_id == filters.category_id);
    }
    if (filters.street) {
        filtered = filtered.filter(r => r.street_name && r.street_name.toLowerCase().includes(filters.street.toLowerCase()));
    }
    if (filters.status) {
        filtered = filtered.filter(r => r.status === filters.status);
    }
    return filtered;
}

// ------------------------------------------------------------------
// RENDER MAP
// ------------------------------------------------------------------
async function renderMap(issues) {
    // clear existing layers
    pinLayer.clearLayers();
    Object.values(heatLayers).forEach(layer => {
        if (map.hasLayer(layer)) map.removeLayer(layer);
    });
    heatLayers = {};

    if (!issues || issues.length === 0) {
        document.getElementById('resultCount').textContent = '0 issues';
        updateStats(issues);
        return;
    }

    // REMOVED the conflicting issues.map() block from here

    const issuesWithCoords = [];
    for (const issue of issues) {
        const coords = await geocodeAddress(
            issue.street_number,
            issue.street_name,
            issue.suburb,
            issue.town,
            issue.postal_code
        );
        issuesWithCoords.push({ ...issue, coords });
        // Wait 1.1 seconds between requests to respect Nominatim's rate limit
        await new Promise(resolve => setTimeout(resolve, 1100));
    }
    
    // Filter out those that couldn't be geocoded
    const validIssues = issuesWithCoords.filter(issue => issue.coords !== null);
    if (validIssues.length === 0) {
        document.getElementById('resultCount').textContent = '0 issues (geocoding failed)';
        updateStats(issues);
        return;
    }

    document.getElementById('resultCount').textContent = `${validIssues.length} issues`;

    // --- PIN VIEW ---
    validIssues.forEach(issue => {
        const cat = categoriesMap[issue.category_id] || { name: 'Unknown', color: '#999999' };
        // CHANGE issue.latitude / issue.longitude to issue.coords.lat / issue.coords.lng
        const marker = L.circleMarker([issue.coords.lat, issue.coords.lng], {
            radius: 9,
            fillColor: cat.color,
            color: '#ffffff',
            weight: 2,
            fillOpacity: 0.9
        }).bindPopup(`
            <strong>${issue.title}</strong><br>
            ${cat.name}<br>
            ${issue.street_name || 'No area'}<br>
            <span style="font-size:12px;color:#666;">Status: ${issue.status || 'Pending'}</span>
        `);
        pinLayer.addLayer(marker);
    });

    // --- HEATMAP VIEW (one layer per category) ---
    const cats = Object.keys(categoriesMap);
    cats.forEach(catId => {
        const cat = categoriesMap[catId];
        // CHANGE 'issues' to 'validIssues'
        const points = validIssues
            .filter(r => r.category_id == catId)
            // CHANGE r.latitude / r.longitude to r.coords.lat / r.coords.lng
            .map(r => [r.coords.lat, r.coords.lng, 0.9]);
        if (points.length === 0) return;
        const layer = L.heatLayer(points, {
            radius: 30,
            blur: 20,
            maxZoom: 15,
            gradient: { 0.4: cat.color, 0.8: cat.color, 1.0: cat.color }
        });
        heatLayers[catId] = layer;
    });

    // show the active view
    if (currentView === 'pins') {
        pinLayer.addTo(map);
        Object.values(heatLayers).forEach(l => { if (map.hasLayer(l)) map.removeLayer(l); });
    } else {
        if (map.hasLayer(pinLayer)) map.removeLayer(pinLayer);
        Object.values(heatLayers).forEach(l => l.addTo(map));
    }

    updateStats(issues);
}

// ------------------------------------------------------------------
// UPDATE STATS & SNAPSHOT
// ------------------------------------------------------------------
function updateStats(issues) {
    // Active reports (not Resolved)
    const active = issues.filter(r => r.status !== 'Resolved');
    document.getElementById('statActiveReports').textContent = active.length;

    // Resolved
    const resolved = issues.filter(r => r.status === 'Resolved');
    document.getElementById('statResolvedThisWeek').textContent = `${resolved.length} resolved`;

    // Category breakdown
    const breakdown = document.getElementById('categoryBreakdown');
    breakdown.innerHTML = '';
    const counts = {};
    issues.forEach(r => {
        const id = r.category_id;
        counts[id] = (counts[id] || 0) + 1;
    });
    const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]);
    if (sorted.length === 0) {
        breakdown.innerHTML = '<div class="info-row"><span>No reports</span><span>0</span></div>';
    } else {
        sorted.forEach(([catId, count]) => {
            const cat = categoriesMap[catId] || { name: 'Unknown' };
            const row = document.createElement('div');
            row.className = 'info-row';
            row.innerHTML = `<span>${cat.name}</span><span>${count}</span>`;
            breakdown.appendChild(row);
        });
    }

    // Update legend with counts
    const legendItems = document.querySelectorAll('.legend-item');
    legendItems.forEach(item => {
        const name = item.textContent.trim();
        for (const [id, cat] of Object.entries(categoriesMap)) {
            if (cat.name === name) {
                const count = counts[id] || 0;
                let countSpan = item.querySelector('.legend-count');
                if (!countSpan) {
                    countSpan = document.createElement('span');
                    countSpan.className = 'legend-count';
                    item.appendChild(countSpan);
                }
                countSpan.textContent = ` (${count})`;
                break;
            }
        }
    });
}

// ------------------------------------------------------------------
// APPLY FILTERS & REFRESH
// ------------------------------------------------------------------
async function applyFilters() {
    const category_id = document.getElementById('filterCategory').value;
    const street = document.getElementById('filterStreet').value;
    const status = document.getElementById('filterStatus').value;

    currentFilters = { category_id, street, status };

    const data = await fetchReports(currentFilters);
    allIssues = data;
    await renderMap(data);
}

// ------------------------------------------------------------------
// TOGGLE VIEW (pins / heatmap)
// ------------------------------------------------------------------
function setView(view) {
    currentView = view;
    document.getElementById('btn-pins').classList.toggle('active', view === 'pins');
    document.getElementById('btn-heat').classList.toggle('active', view === 'heat');

    if (view === 'pins') {
        pinLayer.addTo(map);
        Object.values(heatLayers).forEach(l => { if (map.hasLayer(l)) map.removeLayer(l); });
    } else {
        if (map.hasLayer(pinLayer)) map.removeLayer(pinLayer);
        Object.values(heatLayers).forEach(l => l.addTo(map));
    }
}

// ------------------------------------------------------------------
// BOOTSTRAP
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async function() {
    initMap();
    await fetchFilterOptions();
    await applyFilters();

    // Event listeners
    document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    document.getElementById('resetFiltersBtn').addEventListener('click', () => {
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterStreet').value = '';
        document.getElementById('filterStatus').value = '';
        applyFilters();
    });
    document.getElementById('btn-pins').addEventListener('click', () => setView('pins'));
    document.getElementById('btn-heat').addEventListener('click', () => setView('heat'));
});