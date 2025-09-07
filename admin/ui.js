/* admin/ui.js
   - Center Banner alerts (800x400)
   - Real-time sales notifications poller
*/

(function(){
  if (window.__GF_ADMIN_UI) return; window.__GF_ADMIN_UI = true;

  // ===== Center Banner =====
  const banner = document.createElement('div');
  banner.id = 'gf-center-banner';
  banner.setAttribute('role','dialog');
  banner.setAttribute('aria-modal','true');
  banner.style.cssText = [
    'position:fixed','inset:0','display:none','align-items:center','justify-content:center',
    'background:rgba(0,0,0,.45)','z-index:10000'
  ].join(';');
  banner.innerHTML = `
    <div id="gf-banner-card" style="width:800px;height:400px;max-width:90vw;max-height:80vh;background:#fff;border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,.25);display:flex;flex-direction:column;overflow:hidden">
      <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid #e5e7eb;background:linear-gradient(90deg,#00a651,#1cc97a);color:#fff;">
        <img src="/images/white-logo.png" alt="logo" style="width:28px;height:28px;object-fit:contain;border-radius:6px;background:#000;padding:2px">
        <div style="font-weight:800;letter-spacing:.2px">Gotta.Fish — Admin</div>
        <button id="gf-banner-close" aria-label="Close" style="margin-left:auto;background:transparent;border:0;color:#fff;font-size:18px;cursor:pointer">✕</button>
      </div>
      <div id="gf-banner-body" style="flex:1;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px">
        <div>
          <div id="gf-banner-title" style="font-size:28px;font-weight:800;color:#111;margin-bottom:8px">Success</div>
          <div id="gf-banner-desc" style="font-size:16px;color:#4b5563">Action completed.</div>
        </div>
      </div>
      <div style="padding:14px 18px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:10px;background:#fafafa">
        <button id="gf-banner-ok" style="background:#111;color:#fff;border:0;border-radius:9999px;font-weight:700;padding:8px 16px;cursor:pointer">OK</button>
      </div>
    </div>`;
  document.addEventListener('DOMContentLoaded', ()=> document.body.appendChild(banner));

  function hideBanner(){ banner.style.display='none'; document.body.style.overflow=''; }
  function showBanner({title, message}){
    document.getElementById('gf-banner-title').textContent = title || 'Notice';
    document.getElementById('gf-banner-desc').textContent = message || '';
    banner.style.display='flex';
    document.body.style.overflow='hidden';
  }
  window.GFAdminBanner = { show: showBanner, hide: hideBanner };
  document.addEventListener('click', (e)=>{
    if (e.target?.id === 'gf-banner-close' || e.target?.id === 'gf-banner-ok') hideBanner();
    if (e.target === banner) hideBanner();
  });

  // ===== Sales Notifications =====
  const FEED_URL = '/admin/notifications_feed.php';
  const SEEN_URL = '/admin/notifications_seen.php';

  let polling = false;
  let lastPollOk = true;
  const POLL_MS = 10000;

  function renderAdminBell(){
    if (document.getElementById('gf-admin-bell')) return;
    const bell = document.createElement('div');
    bell.id='gf-admin-bell';
    bell.style.cssText='position:fixed;right:18px;bottom:18px;z-index:9999';
    bell.innerHTML = `
      <button id="gf-bell-btn" style="position:relative;width:52px;height:52px;border-radius:9999px;border:0;background:#00a651;color:#fff;box-shadow:0 12px 28px rgba(0,0,0,.15);cursor:pointer">
        🔔
        <span id="gf-bell-badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:9999px;font-size:12px;font-weight:800;min-width:22px;height:22px;line-height:22px;text-align:center;padding:0 6px"></span>
      </button>
    `;
    document.body.appendChild(bell);
  }

  function updateBadge(n){
    const badge = document.getElementById('gf-bell-badge');
    if (!badge) return;
    if (n > 0) { badge.style.display='block'; badge.textContent = String(n); }
    else { badge.style.display='none'; }
  }

  async function markSeen(ids){
    try {
      await fetch(SEEN_URL, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ ids }) });
    } catch(_){ }
  }

  function renderNotifications(list){
    if (!Array.isArray(list) || !list.length) return;
    renderAdminBell();
    updateBadge(list.length);
    // Show banner aggregation
    const groups = list.map(x=>`${x.count>1? x.count+' people purchased':'Purchase'}: ${x.product_name} ($${Number(x.total).toFixed(2)})`).join('\n');
    GFAdminBanner.show({ title: 'New Sales', message: groups });
    const ids = list.map(x=>x.id);
    document.getElementById('gf-banner-ok')?.addEventListener('click', ()=> markSeen(ids), { once:true });
  }

  async function poll(){
    if (polling) return; polling = true;
    try {
      const res = await fetch(FEED_URL, { cache:'no-store' });
      if (!res.ok) throw new Error('feed error');
      const data = await res.json();
      lastPollOk = true;
      renderNotifications(data?.unseen || []);
    } catch(_){ lastPollOk = false; }
    finally { polling = false; setTimeout(poll, POLL_MS); }
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    renderAdminBell();
    setTimeout(poll, 1200);
  });
})();


