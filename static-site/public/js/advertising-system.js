// Premium Advertising System for Wholesale Education
class WholesaleAdvertisingSystem {
    constructor() {
        this.adConfig = {
            // Programmatic Ads (Google AdSense, Ezoic, etc.)
            programmatic: {
                enabled: true,
                providers: ['adsense', 'ezoic', 'mediavine'],
                placements: ['header', 'sidebar', 'inline', 'footer']
            },
            
            // Direct Supplier Advertising
            direct: {
                enabled: true,
                placements: {
                    'homepage-hero': { price: 5000, duration: 'month' },
                    'category-sidebar': { price: 500, duration: 'month' },
                    'article-inline': { price: 200, duration: 'month' },
                    'supplier-directory': { price: 1000, duration: 'month' },
                    'newsletter-sponsor': { price: 300, duration: 'month' }
                }
            },
            
            // Affiliate Marketing
            affiliate: {
                enabled: true,
                partners: {
                    'alibaba': { commission: 8, category: 'marketplace' },
                    'shopify': { commission: 2, category: 'ecommerce' },
                    'amazon-business': { commission: 4, category: 'marketplace' },
                    'flexport': { commission: 5, category: 'logistics' },
                    'dhgate': { commission: 6, category: 'marketplace' }
                }
            }
        };
        
        this.init();
    }
    
    init() {
        this.loadAdPlacements();
        this.setupAdTracking();
        this.initializeProgrammaticAds();
        this.setupAffiliateLinks();
    }
    
    // Load and display ad placements
    loadAdPlacements() {
        // Homepage Hero Ad
        this.createHeroAd();
        
        // Sidebar Ads
        this.createSidebarAds();
        
        // Inline Article Ads
        this.createInlineAds();
        
        // Supplier Directory Ads
        this.createSupplierDirectoryAds();
        
        // Footer Ads
        this.createFooterAds();
    }
    
    createHeroAd() {
        const heroAd = document.createElement('div');
        heroAd.className = 'premium-hero-ad';
        heroAd.innerHTML = `
            <div class="hero-ad-content">
                <div class="ad-badge">Sponsored</div>
                <h3>Find Premium Wholesale Suppliers</h3>
                <p>Connect with verified suppliers across 100+ industries. Get instant quotes and exclusive deals.</p>
                <div class="ad-cta">
                    <a href="/suppliers" class="btn btn-primary">Browse Suppliers</a>
                    <a href="/advertise" class="btn btn-secondary">Advertise Here</a>
                </div>
            </div>
            <div class="hero-ad-visual">
                <div class="supplier-logos">
                    <div class="logo-item">🏭</div>
                    <div class="logo-item">📦</div>
                    <div class="logo-item">🚚</div>
                    <div class="logo-item">💰</div>
                </div>
            </div>
        `;
        
        const heroSection = document.querySelector('.hero');
        if (heroSection) {
            heroSection.appendChild(heroAd);
        }
    }
    
    createSidebarAds() {
        const sidebarAd = document.createElement('div');
        sidebarAd.className = 'premium-sidebar-ad';
        sidebarAd.innerHTML = `
            <div class="sidebar-ad-header">
                <h4>Featured Suppliers</h4>
                <span class="ad-badge">Sponsored</span>
            </div>
            <div class="supplier-list">
                <div class="supplier-item">
                    <div class="supplier-logo">🏭</div>
                    <div class="supplier-info">
                        <h5>Global Electronics Co.</h5>
                        <p>Premium electronics wholesale</p>
                        <div class="supplier-rating">⭐⭐⭐⭐⭐</div>
                    </div>
                </div>
                <div class="supplier-item">
                    <div class="supplier-logo">☕</div>
                    <div class="supplier-info">
                        <h5>Coffee Bean Direct</h5>
                        <p>Bulk coffee suppliers</p>
                        <div class="supplier-rating">⭐⭐⭐⭐⭐</div>
                    </div>
                </div>
                <div class="supplier-item">
                    <div class="supplier-logo">👕</div>
                    <div class="supplier-info">
                        <h5>Fashion Wholesale Pro</h5>
                        <p>Trendy apparel wholesale</p>
                        <div class="supplier-rating">⭐⭐⭐⭐⭐</div>
                    </div>
                </div>
            </div>
            <div class="sidebar-ad-footer">
                <a href="/suppliers" class="btn btn-primary btn-sm">View All Suppliers</a>
            </div>
        `;
        
        // Add to sidebar or create sidebar if it doesn't exist
        let sidebar = document.querySelector('.sidebar');
        if (!sidebar) {
            sidebar = document.createElement('div');
            sidebar.className = 'sidebar';
            document.querySelector('.main').appendChild(sidebar);
        }
        sidebar.appendChild(sidebarAd);
    }
    
    createInlineAds() {
        const articles = document.querySelectorAll('.page-content, .page-card');
        articles.forEach((article, index) => {
            if (index % 3 === 0) { // Every 3rd article
                const inlineAd = document.createElement('div');
                inlineAd.className = 'premium-inline-ad';
                inlineAd.innerHTML = `
                    <div class="inline-ad-content">
                        <div class="ad-badge">Sponsored Content</div>
                        <h4>Wholesale Success Stories</h4>
                        <p>Learn how businesses scaled from $0 to $1M+ using our supplier network.</p>
                        <div class="ad-features">
                            <div class="feature-item">✅ Verified Suppliers</div>
                            <div class="feature-item">✅ Instant Quotes</div>
                            <div class="feature-item">✅ Quality Guarantee</div>
                        </div>
                        <a href="/success-stories" class="btn btn-primary">Read Success Stories</a>
                    </div>
                `;
                article.appendChild(inlineAd);
            }
        });
    }
    
    createSupplierDirectoryAds() {
        const supplierDirectory = document.createElement('div');
        supplierDirectory.className = 'premium-supplier-directory';
        supplierDirectory.innerHTML = `
            <div class="directory-header">
                <h3>Premium Supplier Directory</h3>
                <p>Connect with verified wholesale suppliers across all industries</p>
            </div>
            <div class="directory-categories">
                <div class="category-item">
                    <div class="category-icon">🏭</div>
                    <h5>Electronics</h5>
                    <p>500+ suppliers</p>
                </div>
                <div class="category-item">
                    <div class="category-icon">☕</div>
                    <h5>Food & Beverage</h5>
                    <p>300+ suppliers</p>
                </div>
                <div class="category-item">
                    <div class="category-icon">👕</div>
                    <h5>Fashion</h5>
                    <p>400+ suppliers</p>
                </div>
                <div class="category-item">
                    <div class="category-icon">🏠</div>
                    <h5>Home & Garden</h5>
                    <p>250+ suppliers</p>
                </div>
            </div>
            <div class="directory-cta">
                <a href="/suppliers" class="btn btn-primary">Browse All Suppliers</a>
                <a href="/advertise" class="btn btn-secondary">List Your Business</a>
            </div>
        `;
        
        const mainContent = document.querySelector('.main');
        if (mainContent) {
            mainContent.appendChild(supplierDirectory);
        }
    }
    
    createFooterAds() {
        const footerAd = document.createElement('div');
        footerAd.className = 'premium-footer-ad';
        footerAd.innerHTML = `
            <div class="footer-ad-content">
                <div class="ad-section">
                    <h4>Wholesale Tools & Resources</h4>
                    <div class="tool-links">
                        <a href="/profit-calculator" class="tool-link">💰 Profit Calculator</a>
                        <a href="/shipping-calculator" class="tool-link">🚚 Shipping Calculator</a>
                        <a href="/supplier-templates" class="tool-link">📋 Supplier Templates</a>
                    </div>
                </div>
                <div class="ad-section">
                    <h4>Partner Marketplaces</h4>
                    <div class="marketplace-links">
                        <a href="https://alibaba.com" class="marketplace-link" data-affiliate="alibaba">Alibaba</a>
                        <a href="https://dhgate.com" class="marketplace-link" data-affiliate="dhgate">DHGate</a>
                        <a href="https://amazon.com/business" class="marketplace-link" data-affiliate="amazon-business">Amazon Business</a>
                    </div>
                </div>
            </div>
        `;
        
        const footer = document.querySelector('.footer');
        if (footer) {
            footer.insertBefore(footerAd, footer.firstChild);
        }
    }
    
    // Initialize programmatic ads
    initializeProgrammaticAds() {
        // Google AdSense
        if (this.adConfig.programmatic.enabled) {
            this.loadGoogleAdSense();
        }
        
        // Ezoic
        this.loadEzoicAds();
    }
    
    loadGoogleAdSense() {
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
        script.setAttribute('data-ad-client', 'ca-pub-XXXXXXXXXX'); // Replace with your AdSense ID
        document.head.appendChild(script);
        
        // Create ad units
        this.createAdSenseUnits();
    }
    
    createAdSenseUnits() {
        const adUnits = [
            { id: 'header-banner', size: '728x90', placement: 'header' },
            { id: 'sidebar-rectangle', size: '300x250', placement: 'sidebar' },
            { id: 'article-inline', size: '336x280', placement: 'inline' },
            { id: 'footer-banner', size: '728x90', placement: 'footer' }
        ];
        
        adUnits.forEach(unit => {
            const adElement = document.createElement('ins');
            adElement.className = 'adsbygoogle';
            adElement.style.display = 'block';
            adElement.setAttribute('data-ad-client', 'ca-pub-XXXXXXXXXX');
            adElement.setAttribute('data-ad-slot', unit.id);
            adElement.setAttribute('data-ad-format', 'auto');
            
            const container = document.querySelector(`.${unit.placement}-ad-container`);
            if (container) {
                container.appendChild(adElement);
            }
        });
    }
    
    loadEzoicAds() {
        // Ezoic integration
        const script = document.createElement('script');
        script.src = 'https://go.ezodn.com/ads.js';
        document.head.appendChild(script);
    }
    
    // Setup affiliate links
    setupAffiliateLinks() {
        const affiliateLinks = document.querySelectorAll('[data-affiliate]');
        affiliateLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const affiliate = e.target.getAttribute('data-affiliate');
                this.trackAffiliateClick(affiliate);
            });
        });
    }
    
    trackAffiliateClick(affiliate) {
        // Track affiliate clicks
        if (typeof gtag !== 'undefined') {
            gtag('event', 'affiliate_click', {
                'affiliate_partner': affiliate,
                'page_location': window.location.href
            });
        }
        
        // Store in localStorage for conversion tracking
        localStorage.setItem('affiliate_click', JSON.stringify({
            partner: affiliate,
            timestamp: Date.now(),
            page: window.location.href
        }));
    }
    
    // Ad tracking and analytics
    setupAdTracking() {
        // Track ad impressions
        this.trackAdImpressions();
        
        // Track ad clicks
        this.trackAdClicks();
        
        // Track conversions
        this.trackConversions();
    }
    
    trackAdImpressions() {
        const ads = document.querySelectorAll('.premium-hero-ad, .premium-sidebar-ad, .premium-inline-ad');
        ads.forEach(ad => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.sendAdImpression(entry.target);
                    }
                });
            });
            observer.observe(ad);
        });
    }
    
    trackAdClicks() {
        document.addEventListener('click', (e) => {
            const adElement = e.target.closest('.premium-hero-ad, .premium-sidebar-ad, .premium-inline-ad');
            if (adElement) {
                this.sendAdClick(adElement);
            }
        });
    }
    
    sendAdImpression(adElement) {
        // Send impression data to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'ad_impression', {
                'ad_type': adElement.className,
                'page_location': window.location.href
            });
        }
    }
    
    sendAdClick(adElement) {
        // Send click data to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'ad_click', {
                'ad_type': adElement.className,
                'page_location': window.location.href
            });
        }
    }
    
    trackConversions() {
        // Track conversions from ads
        const conversionEvents = ['purchase', 'signup', 'contact'];
        conversionEvents.forEach(event => {
            document.addEventListener(event, () => {
                this.sendConversion(event);
            });
        });
    }
    
    sendConversion(event) {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'conversion', {
                'event_category': 'advertising',
                'event_label': event,
                'value': 1
            });
        }
    }
}

// Initialize the advertising system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new WholesaleAdvertisingSystem();
});

// Export for use in other modules
window.WholesaleAdvertisingSystem = WholesaleAdvertisingSystem;
