<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/layout.php';

$trafficFile = __DIR__ . '/../data/traffic.json';
$traffic = [];
if (file_exists($trafficFile)) {
  $raw = file_get_contents($trafficFile);
  $traffic = json_decode($raw, true);
  if (!is_array($traffic)) $traffic = [];
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

admin_layout_start('Traffic', 'traffic');
?>

<div class="col-span-12">
  <div class="card">
    <div class="card-header flex items-center justify-between">
      <span>Quick Actions</span>
      <button id="toggle-utm-builder" class="btn-secondary text-sm">UTM Builder</button>
    </div>
    <div id="utm-builder-section" class="hidden space-y-4">
      <form id="utm-form" class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Source</label>
            <select id="utm_source" class="form-input">
              <optgroup label="Search">
                <option value="google">Google</option>
                <option value="bing">Bing</option>
                <option value="yahoo">Yahoo</option>
                <option value="duckduckgo">DuckDuckGo</option>
              </optgroup>
              <optgroup label="Social">
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="x">X</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
              </optgroup>
              <optgroup label="Email/SMS">
                <option value="email">Email</option>
                <option value="sms">SMS</option>
              </optgroup>
              <option value="direct">Direct</option>
            </select>
          </div>
          <div>
            <label class="form-label">Medium</label>
            <select id="utm_medium" class="form-input">
              <option value="search">search</option>
              <option value="social">social</option>
              <option value="email">email</option>
              <option value="sms">sms</option>
              <option value="cpc">cpc</option>
              <option value="referral">referral</option>
              <option value="direct">direct</option>
            </select>
          </div>
          <div>
            <label class="form-label">Campaign</label>
            <input id="utm_campaign" type="text" class="form-input" placeholder="spring_sale_2025">
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Term (optional)</label>
            <input id="utm_term" type="text" class="form-input" placeholder="bass lures">
          </div>
          <div>
            <label class="form-label">Content (optional)</label>
            <input id="utm_content" type="text" class="form-input" placeholder="video_ad_a">
          </div>
          <div>
            <label class="form-label">Base URL</label>
            <input id="utm_base" type="text" class="form-input" placeholder="https://gotta.fish/shop">
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button type="button" id="build-utm" class="btn">Generate URL</button>
          <input id="utm_result" type="text" class="form-input flex-1" placeholder="Generated URL will appear here" readonly>
          <button type="button" id="copy-utm" class="btn-secondary">Copy</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="col-span-12">
  <div class="card">
    <div class="card-header">Traffic Analytics</div>
    <div class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
          <label class="form-label">Filter Source</label>
          <select id="filter_source" class="form-input">
            <option value="">All</option>
            <option value="google">Google</option>
            <option value="bing">Bing</option>
            <option value="yahoo">Yahoo</option>
            <option value="duckduckgo">DuckDuckGo</option>
            <option value="facebook">Facebook</option>
            <option value="instagram">Instagram</option>
            <option value="x">X</option>
            <option value="youtube">YouTube</option>
            <option value="tiktok">TikTok</option>
            <option value="email">Email</option>
            <option value="sms">SMS</option>
            <option value="direct">Direct</option>
          </select>
        </div>
        <div>
          <label class="form-label">Filter Medium</label>
          <select id="filter_medium" class="form-input">
            <option value="">All</option>
            <option value="search">search</option>
            <option value="social">social</option>
            <option value="email">email</option>
            <option value="sms">sms</option>
            <option value="cpc">cpc</option>
            <option value="referral">referral</option>
            <option value="direct">direct</option>
          </select>
        </div>
        <div>
          <label class="form-label">Device Type</label>
          <select id="filter_device" class="form-input">
            <option value="">All</option>
            <option value="desktop">Desktop</option>
            <option value="mobile">Mobile</option>
            <option value="tablet">Tablet</option>
            <option value="bot">Bot</option>
          </select>
        </div>
        <div>
          <label class="form-label">Country</label>
          <select id="filter_country" class="form-input">
            <option value="">All</option>
            <option value="United States">United States</option>
            <option value="Canada">Canada</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Australia">Australia</option>
            <option value="Germany">Germany</option>
            <option value="France">France</option>
            <option value="Local">Local</option>
          </select>
        </div>
        <div>
          <label class="form-label">From</label>
          <input id="filter_from" type="date" class="form-input">
        </div>
        <div>
          <label class="form-label">To</label>
          <input id="filter_to" type="date" class="form-input">
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button id="apply_filters" class="btn">Apply</button>
        <button id="reset_filters" class="btn-secondary">Reset</button>
        <button id="export_csv" class="btn-secondary">Export CSV</button>
        <button id="block_ip" class="btn-secondary bg-red-600 hover:bg-red-700 text-white">Block IP</button>
        <div class="text-sm text-gray-400" id="result_count"></div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card">
          <div class="card-header">Visits by Source</div>
          <div class="p-4">
            <canvas id="traffic_chart" height="200"></canvas>
          </div>
        </div>
        <div class="card">
          <div class="card-header">Device Types</div>
          <div class="p-4">
            <canvas id="device_chart" height="200"></canvas>
          </div>
        </div>
        <div class="card">
          <div class="card-header">Top Countries</div>
          <div class="p-4">
            <canvas id="country_chart" height="200"></canvas>
          </div>
        </div>
      </div>

      <div class="overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Time (UTC)</th>
              <th>IP Address</th>
              <th>Location</th>
              <th>Device</th>
              <th>Browser</th>
              <th>Source</th>
              <th>Campaign</th>
              <th>Path</th>
              <th>Session Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="traffic_tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Blocked IPs Management -->
<div class="col-span-12">
  <div class="card">
    <div class="card-header">Blocked IP Addresses</div>
    <div class="space-y-4">
      <div class="flex items-center gap-3">
        <input id="new_blocked_ip" type="text" class="form-input flex-1" placeholder="Enter IP address to block (e.g., 192.168.1.1)">
        <input id="block_reason" type="text" class="form-input flex-1" placeholder="Reason for blocking (optional)">
        <button id="add_blocked_ip" class="btn bg-red-600 hover:bg-red-700 text-white">Block IP</button>
      </div>
      <div class="overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>IP Address</th>
              <th>Reason</th>
              <th>Blocked Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="blocked_ips_tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const qs = (s)=>document.querySelector(s);
  // --- UTM Builder ---
  function buildUTM(){
    const base = (qs('#utm_base').value || '').trim();
    if (!base) { qs('#utm_result').value = ''; return; }
    const u = new URL(base, window.location.origin);
    const add = (k,v)=>{ if(v) u.searchParams.set(k,v); };
    add('utm_source', qs('#utm_source').value);
    add('utm_medium', qs('#utm_medium').value);
    add('utm_campaign', qs('#utm_campaign').value.trim());
    add('utm_term', qs('#utm_term').value.trim());
    add('utm_content', qs('#utm_content').value.trim());
    qs('#utm_result').value = u.toString();
  }
  qs('#build-utm').addEventListener('click', buildUTM);
  qs('#copy-utm').addEventListener('click', ()=>{
    const el = qs('#utm_result');
    el.select();
    document.execCommand('copy');
  });
  qs('#toggle-utm-builder').addEventListener('click', ()=>{
    const section = qs('#utm-builder-section');
    const btn = qs('#toggle-utm-builder');
    if (section.classList.contains('hidden')) {
      section.classList.remove('hidden');
      btn.textContent = 'Hide UTM Builder';
    } else {
      section.classList.add('hidden');
      btn.textContent = 'UTM Builder';
    }
  });

  // --- Traffic Analytics ---
  const rows = <?php echo json_encode($traffic, JSON_UNESCAPED_SLASHES); ?>;

  function parseDate(s){
    const d = new Date(s);
    return isNaN(d.getTime()) ? null : d;
  }

  function applyFilters(){
    const src = (qs('#filter_source').value || '').toLowerCase();
    const med = (qs('#filter_medium').value || '').toLowerCase();
    const device = (qs('#filter_device').value || '').toLowerCase();
    const country = (qs('#filter_country').value || '').toLowerCase();
    const from = qs('#filter_from').value ? new Date(qs('#filter_from').value + 'T00:00:00Z') : null;
    const to   = qs('#filter_to').value ? new Date(qs('#filter_to').value + 'T23:59:59Z') : null;

    const list = rows.filter(r=>{
      const rsrc = (r?.utm?.source||'').toLowerCase();
      const rmed = (r?.utm?.medium||'').toLowerCase();
      const rdevice = (r?.device?.device_type||'').toLowerCase();
      const rcountry = (r?.location?.country||'').toLowerCase();
      const ts = parseDate(r.ts);
      if (src && rsrc !== src) return false;
      if (med && rmed !== med) return false;
      if (device && rdevice !== device) return false;
      if (country && rcountry !== country) return false;
      if (from && (!ts || ts < from)) return false;
      if (to && (!ts || ts > to)) return false;
      return true;
    });
    return list;
  }

  function renderTable(list){
    const tbody = qs('#traffic_tbody');
    tbody.innerHTML = list.slice().reverse().map(r=>{
      const ts = r.ts || '';
      const ip = r.ip || '';
      const country = (r.location && r.location.country) || 'Unknown';
      const city = (r.location && r.location.city) || '';
      const location = city ? `${city}, ${country}` : country;
      const device = (r.device && r.device.device_type) || 'Unknown';
      const browser = (r.device && r.device.browser) || 'Unknown';
      const src = (r.utm && r.utm.source) || 'Direct';
      const camp = (r.utm && r.utm.campaign) || '';
      const path = r.path || '/';
      
      // Determine session status based on path and recent activity
      const sessionStatus = getSessionStatus(r, list);
      
      return `<tr>
        <td class="text-xs">${escapeHtml(ts)}</td>
        <td class="text-xs font-mono">${escapeHtml(ip)}</td>
        <td class="text-xs">${escapeHtml(location)}</td>
        <td class="text-xs">${escapeHtml(device)}</td>
        <td class="text-xs">${escapeHtml(browser)}</td>
        <td class="text-xs">${escapeHtml(src)}</td>
        <td class="text-xs">${escapeHtml(camp)}</td>
        <td class="text-xs truncate max-w-[200px]" title="${escapeHtml(path)}">${escapeHtml(path)}</td>
        <td class="text-xs">
          <span class="px-2 py-1 rounded text-xs ${sessionStatus.class}">${sessionStatus.text}</span>
        </td>
        <td class="text-xs">
          <div class="flex gap-2">
            <button class="block-ip-btn text-red-500 hover:text-red-700 underline" data-ip="${escapeHtml(ip)}">Block</button>
            <button class="clear-session-btn text-blue-500 hover:text-blue-700 underline" data-ip="${escapeHtml(ip)}">Clear Session</button>
          </div>
        </td>
      </tr>`;
    }).join('');
    qs('#result_count').textContent = `${list.length} results`;
  }

  function groupBy(list, keyPath){
    const buckets = Object.create(null);
    for (const r of list){
      const key = keyPath(r) || 'unknown';
      buckets[key] = (buckets[key]||0) + 1;
    }
    return buckets;
  }

  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, (c)=>({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'
    })[c]);
  }
  
  function getSessionStatus(record, allRecords) {
    const ip = record.ip;
    const path = record.path || '/';
    const timestamp = parseDate(record.ts);
    
    // Check if this IP has recent activity (within last 30 minutes)
    const now = new Date();
    const thirtyMinutesAgo = new Date(now.getTime() - 30 * 60 * 1000);
    
    const recentActivity = allRecords.filter(r => 
      r.ip === ip && 
      parseDate(r.ts) && 
      parseDate(r.ts) > thirtyMinutesAgo
    );
    
    // Determine status based on path and activity
    if (path.includes('/checkout')) {
      return { text: 'Checkout', class: 'bg-yellow-100 text-yellow-800' };
    } else if (path.includes('/cart')) {
      return { text: 'Cart', class: 'bg-blue-100 text-blue-800' };
    } else if (path.includes('/shop') || path.includes('/product')) {
      return { text: 'Shopping', class: 'bg-green-100 text-green-800' };
    } else if (recentActivity.length > 1) {
      return { text: 'Active', class: 'bg-green-100 text-green-800' };
    } else if (recentActivity.length === 1) {
      return { text: 'Recent', class: 'bg-gray-100 text-gray-800' };
    } else {
      return { text: 'Inactive', class: 'bg-red-100 text-red-800' };
    }
  }

  function renderBarChart(canvasId, buckets){
    const canvas = qs(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const keys = Object.keys(buckets);
    const values = keys.map(k=>buckets[k]);
    const max = Math.max(1, ...values);
    const W = canvas.width = canvas.clientWidth;
    const H = canvas.height; // fixed by attribute
    ctx.clearRect(0,0,W,H);
    const pad = 30;
    const barW = Math.max(12, (W - pad*2) / Math.max(keys.length, 1) * 0.6);
    const gap = Math.max(8, (W - pad*2) / Math.max(keys.length, 1) - barW);
    let x = pad + (gap/2);
    ctx.font = '12px system-ui, -apple-system, Segoe UI, Roboto';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    for (let i=0;i<keys.length;i++){
      const v = values[i];
      const h = Math.round((H - pad*2) * (v / max));
      const y = H - pad - h;
      ctx.fillStyle = '#10b981';
      ctx.fillRect(x, y, barW, h);
      ctx.fillStyle = '#6b7280';
      const label = keys[i].length>12 ? keys[i].slice(0,11)+'…' : keys[i];
      ctx.fillText(label, x + barW/2, H - pad + 6);
      ctx.fillStyle = '#111827';
      ctx.fillText(String(v), x + barW/2, y - 16);
      x += barW + gap;
    }
    // axes
    ctx.strokeStyle = '#e5e7eb';
    ctx.beginPath();
    ctx.moveTo(pad, H - pad);
    ctx.lineTo(W - pad, H - pad);
    ctx.stroke();
  }

  function renderDailyChart(canvasId, list){
    const canvas = qs(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    // Group by date and campaign
    const dailyData = {};
    for (const r of list){
      const d = parseDate(r.ts);
      if (!d) continue;
      const dateKey = d.toISOString().split('T')[0]; // YYYY-MM-DD
      const campaign = (r.utm && r.utm.campaign) || 'no-campaign';
      
      if (!dailyData[dateKey]) dailyData[dateKey] = {};
      dailyData[dateKey][campaign] = (dailyData[dateKey][campaign] || 0) + 1;
    }
    
    const dates = Object.keys(dailyData).sort();
    const campaigns = [...new Set(Object.values(dailyData).flatMap(d => Object.keys(d)))];
    const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
    
    const W = canvas.width = canvas.clientWidth;
    const H = canvas.height;
    ctx.clearRect(0,0,W,H);
    
    const pad = 40;
    const chartW = W - pad*2;
    const chartH = H - pad*2;
    
    // Draw bars
    const barW = Math.max(8, chartW / Math.max(dates.length, 1) * 0.8);
    const gap = Math.max(4, chartW / Math.max(dates.length, 1) - barW);
    
    let maxTotal = 0;
    for (const date of dates){
      const total = Object.values(dailyData[date]).reduce((a,b) => a+b, 0);
      maxTotal = Math.max(maxTotal, total);
    }
    
    ctx.font = '10px system-ui, -apple-system, Segoe UI, Roboto';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    
    let x = pad + (gap/2);
    for (let i=0; i<dates.length; i++){
      const date = dates[i];
      const data = dailyData[date];
      let y = pad + chartH;
      
      // Draw stacked bars
      for (let j=0; j<campaigns.length; j++){
        const campaign = campaigns[j];
        const count = data[campaign] || 0;
        if (count === 0) continue;
        
        const h = Math.round(chartH * (count / maxTotal));
        y -= h;
        
        ctx.fillStyle = colors[j % colors.length];
        ctx.fillRect(x, y, barW, h);
      }
      
      // Date label
      ctx.fillStyle = '#6b7280';
      const dateLabel = new Date(date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'});
      ctx.fillText(dateLabel, x + barW/2, pad + chartH + 8);
      
      x += barW + gap;
    }
    
    // Legend
    ctx.font = '11px system-ui, -apple-system, Segoe UI, Roboto';
    ctx.textAlign = 'left';
    let legendX = pad;
    let legendY = 20;
    for (let i=0; i<campaigns.length; i++){
      const campaign = campaigns[i];
      const color = colors[i % colors.length];
      ctx.fillStyle = color;
      ctx.fillRect(legendX, legendY, 12, 12);
      ctx.fillStyle = '#111827';
      const label = campaign.length > 15 ? campaign.slice(0,14) + '…' : campaign;
      ctx.fillText(label, legendX + 16, legendY + 8);
      legendX += 120;
      if (legendX > W - 100) {
        legendX = pad;
        legendY += 20;
      }
    }
    
    // Axes
    ctx.strokeStyle = '#e5e7eb';
    ctx.beginPath();
    ctx.moveTo(pad, pad);
    ctx.lineTo(pad, pad + chartH);
    ctx.lineTo(pad + chartW, pad + chartH);
    ctx.stroke();
  }

  function exportCSV(list){
    const headers = ['Timestamp', 'IP Address', 'Country', 'Region', 'City', 'Device Type', 'Browser', 'OS', 'Source', 'Medium', 'Campaign', 'Path', 'Referrer', 'User Agent'];
    const rows = [headers];
    
    for (const r of list){
      rows.push([
        r.ts || '',
        r.ip || '',
        (r.location && r.location.country) || '',
        (r.location && r.location.region) || '',
        (r.location && r.location.city) || '',
        (r.device && r.device.device_type) || '',
        (r.device && r.device.browser) || '',
        (r.device && r.device.os) || '',
        (r.utm && r.utm.source) || '',
        (r.utm && r.utm.medium) || '',
        (r.utm && r.utm.campaign) || '',
        r.path || '',
        r.referrer || '',
        r.ua || ''
      ]);
    }
    
    const csv = rows.map(row => 
      row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `traffic-export-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  function refresh(){
    const list = applyFilters();
    renderTable(list);
    const bySource = groupBy(list, r=> (r.utm&&r.utm.source)||'');
    const byDevice = groupBy(list, r=> (r.device&&r.device.device_type)||'');
    const byCountry = groupBy(list, r=> (r.location&&r.location.country)||'');
    renderBarChart('#traffic_chart', bySource);
    renderBarChart('#device_chart', byDevice);
    renderBarChart('#country_chart', byCountry);
  }

  qs('#apply_filters').addEventListener('click', refresh);
  qs('#reset_filters').addEventListener('click', ()=>{
    qs('#filter_source').value='';
    qs('#filter_medium').value='';
    qs('#filter_device').value='';
    qs('#filter_country').value='';
    qs('#filter_from').value='';
    qs('#filter_to').value='';
    refresh();
  });
  qs('#export_csv').addEventListener('click', ()=>{
    const list = applyFilters();
    exportCSV(list);
  });

  // --- IP Blocking System ---
  let blockedIPs = [];
  
  function loadBlockedIPs() {
    fetch('/admin/get_blocked_ips.php')
      .then(response => response.json())
      .then(data => {
        blockedIPs = data || [];
        renderBlockedIPs();
      })
      .catch(err => console.error('Error loading blocked IPs:', err));
  }
  
  function renderBlockedIPs() {
    const tbody = qs('#blocked_ips_tbody');
    tbody.innerHTML = blockedIPs.map(block => `
      <tr>
        <td class="text-xs font-mono">${escapeHtml(block.ip)}</td>
        <td class="text-xs">${escapeHtml(block.reason || 'No reason provided')}</td>
        <td class="text-xs">${escapeHtml(block.blocked_date)}</td>
        <td class="text-xs">
          <button class="unblock-ip-btn text-green-500 hover:text-green-700 underline" data-ip="${escapeHtml(block.ip)}">Unblock</button>
        </td>
      </tr>
    `).join('');
  }
  
  function blockIP(ip, reason = '') {
    if (!ip || !isValidIP(ip)) {
      alert('Please enter a valid IP address');
      return;
    }
    
    fetch('/admin/block_ip.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ip, reason })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadBlockedIPs();
        alert(`IP ${ip} has been blocked successfully`);
      } else {
        alert('Error blocking IP: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error('Error blocking IP:', err);
      alert('Error blocking IP');
    });
  }
  
  function unblockIP(ip) {
    if (!confirm(`Are you sure you want to unblock IP ${ip}?`)) return;
    
    fetch('/admin/unblock_ip.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ip })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadBlockedIPs();
        alert(`IP ${ip} has been unblocked successfully`);
      } else {
        alert('Error unblocking IP: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error('Error unblocking IP:', err);
      alert('Error unblocking IP');
    });
  }
  
  function clearSession(ip) {
    if (!confirm(`Are you sure you want to clear the session for IP ${ip}?\n\nThis will:\n- Clear their cart\n- Clear any stored preferences\n- Force them to start fresh\n\nUse this when customers call with checkout issues.`)) return;
    
    fetch('/admin/clear_session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ip })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(`Session cleared successfully for IP ${ip}\n\nCustomer should now be able to checkout normally.`);
      } else {
        alert('Error clearing session: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error('Error clearing session:', err);
      alert('Error clearing session');
    });
  }
  
  function isValidIP(ip) {
    const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
    return ipRegex.test(ip);
  }
  
  // Event listeners for IP blocking
  qs('#add_blocked_ip').addEventListener('click', () => {
    const ip = qs('#new_blocked_ip').value.trim();
    const reason = qs('#block_reason').value.trim();
    if (ip) {
      blockIP(ip, reason);
      qs('#new_blocked_ip').value = '';
      qs('#block_reason').value = '';
    }
  });
  
  qs('#block_ip').addEventListener('click', () => {
    const selectedIPs = Array.from(document.querySelectorAll('.block-ip-btn')).map(btn => btn.dataset.ip);
    if (selectedIPs.length === 0) {
      alert('Please select an IP from the traffic table to block');
      return;
    }
    const ip = prompt('Enter IP address to block:', selectedIPs[0]);
    const reason = prompt('Reason for blocking (optional):', '');
    if (ip) {
      blockIP(ip, reason);
    }
  });
  
  // Event delegation for dynamic buttons
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('block-ip-btn')) {
      const ip = e.target.dataset.ip;
      const reason = prompt(`Reason for blocking IP ${ip} (optional):`, '');
      blockIP(ip, reason);
    }
    if (e.target.classList.contains('unblock-ip-btn')) {
      const ip = e.target.dataset.ip;
      unblockIP(ip);
    }
    if (e.target.classList.contains('clear-session-btn')) {
      const ip = e.target.dataset.ip;
      clearSession(ip);
    }
  });

  // Initial render
  refresh();
  loadBlockedIPs();
})();
</script>

<?php admin_layout_end(); ?>


