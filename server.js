const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const bodyParser = require('body-parser');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const session = require('express-session');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(helmet());
app.use(compression());
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static('public'));

// Session middleware
app.use(session({
    secret: 'wholesale-education-secret-key-2025',
    resave: false,
    saveUninitialized: false,
    cookie: { 
        secure: false, // Set to true in production with HTTPS
        maxAge: 24 * 60 * 60 * 1000 // 24 hours
    }
}));

// Set EJS as templating engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Database setup
const db = new sqlite3.Database('./database.sqlite');

// Initialize database tables
db.serialize(() => {
    // Users table
    db.run(`CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        first_name TEXT,
        last_name TEXT,
        business_type TEXT,
        company_name TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT 1,
        subscription_type TEXT DEFAULT 'free'
    )`);

    // Pages table
    db.run(`CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        content TEXT,
        category_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        published BOOLEAN DEFAULT 1,
        views INTEGER DEFAULT 0
    )`);

    // Categories table
    db.run(`CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        description TEXT,
        parent_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`);

    // Suppliers table
    db.run(`CREATE TABLE IF NOT EXISTS suppliers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT,
        phone TEXT,
        website TEXT,
        category TEXT,
        country TEXT,
        description TEXT,
        rating REAL DEFAULT 0,
        verified BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`);

    // Marketplaces table
    db.run(`CREATE TABLE IF NOT EXISTS marketplaces (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        category TEXT,
        region TEXT,
        description TEXT,
        fee_structure TEXT,
        monthly_traffic INTEGER,
        best_use_cases TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )`);

    // User progress table
    db.run(`CREATE TABLE IF NOT EXISTS user_progress (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        page_id INTEGER,
        completed BOOLEAN DEFAULT 0,
        progress_percentage REAL DEFAULT 0,
        last_accessed DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id),
        FOREIGN KEY (page_id) REFERENCES pages (id)
    )`);

    // Analytics table
    db.run(`CREATE TABLE IF NOT EXISTS analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        page_id INTEGER,
        user_id INTEGER,
        event_type TEXT,
        event_data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (page_id) REFERENCES pages (id),
        FOREIGN KEY (user_id) REFERENCES users (id)
    )`);

    // Create indexes for performance
    db.run(`CREATE INDEX IF NOT EXISTS idx_pages_slug ON pages(slug)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_pages_category ON pages(category_id)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_pages_published ON pages(published)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug)`);
});

// Routes

// Home page
app.get('/', (req, res) => {
    db.all(`SELECT p.*, c.name as category_name 
            FROM pages p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.published = 1 
            ORDER BY p.created_at DESC 
            LIMIT 20`, (err, pages) => {
        if (err) {
            console.error(err);
            return res.status(500).send('Database error');
        }
        res.render('index', { pages, title: 'Home' });
    });
});

// Category listing
app.get('/category/:slug', (req, res) => {
    const { slug } = req.params;
    const page = parseInt(req.query.page) || 1;
    const limit = 50;
    const offset = (page - 1) * limit;

    db.get(`SELECT * FROM categories WHERE slug = ?`, [slug], (err, category) => {
        if (err || !category) {
            return res.status(404).render('404', { title: 'Category Not Found' });
        }

        db.all(`SELECT p.*, c.name as category_name 
                FROM pages p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND p.published = 1 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?`, [category.id, limit, offset], (err, pages) => {
            if (err) {
                console.error(err);
                return res.status(500).send('Database error');
            }

            // Get total count for pagination
            db.get(`SELECT COUNT(*) as total FROM pages WHERE category_id = ? AND published = 1`, 
                [category.id], (err, countResult) => {
                const totalPages = Math.ceil(countResult.total / limit);
                res.render('category', { 
                    category, 
                    pages, 
                    currentPage: page,
                    totalPages,
                    title: category.name 
                });
            });
        });
    });
});

// Individual page
app.get('/page/:slug', (req, res) => {
    const { slug } = req.params;
    
    db.get(`SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM pages p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.slug = ? AND p.published = 1`, [slug], (err, page) => {
        if (err) {
            console.error(err);
            return res.status(500).send('Database error');
        }
        
        if (!page) {
            return res.status(404).render('404', { title: 'Page Not Found' });
        }

        // Increment view count
        db.run(`UPDATE pages SET views = views + 1 WHERE id = ?`, [page.id]);

        // Get related pages
        db.all(`SELECT * FROM pages 
                WHERE category_id = ? AND id != ? AND published = 1 
                ORDER BY RANDOM() LIMIT 5`, [page.category_id, page.id], (err, relatedPages) => {
            res.render('page', { page, relatedPages, title: page.title });
        });
    });
});

// Search functionality
app.get('/search', (req, res) => {
    const { q, page = 1 } = req.query;
    const limit = 20;
    const offset = (page - 1) * limit;

    if (!q) {
        return res.render('search', { results: [], query: '', title: 'Search' });
    }

    db.all(`SELECT p.*, c.name as category_name 
            FROM pages p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.published = 1 AND (p.title LIKE ? OR p.content LIKE ?)
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?`, [`%${q}%`, `%${q}%`, limit, offset], (err, results) => {
        if (err) {
            console.error(err);
            return res.status(500).send('Database error');
        }

        // Get total count
        db.get(`SELECT COUNT(*) as total FROM pages 
                WHERE published = 1 AND (title LIKE ? OR content LIKE ?)`, 
                [`%${q}%`, `%${q}%`], (err, countResult) => {
            const totalPages = Math.ceil(countResult.total / limit);
            res.render('search', { 
                results, 
                query: q, 
                currentPage: parseInt(page),
                totalPages,
                title: `Search: ${q}` 
            });
        });
    });
});

// API endpoints for dynamic content loading
app.get('/api/pages', (req, res) => {
    const { category, page = 1, limit = 20 } = req.query;
    const offset = (page - 1) * limit;
    
    let query = `SELECT p.*, c.name as category_name 
                 FROM pages p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.published = 1`;
    let params = [];

    if (category) {
        query += ` AND c.slug = ?`;
        params.push(category);
    }

    query += ` ORDER BY p.created_at DESC LIMIT ? OFFSET ?`;
    params.push(limit, offset);

    db.all(query, params, (err, pages) => {
        if (err) {
            return res.status(500).json({ error: 'Database error' });
        }
        res.json(pages);
    });
});

// Marketplace routes
app.get('/marketplace', (req, res) => {
    res.render('marketplace/overview', {
        title: 'Marketplace Mastery - Wholesale.Education'
    });
});

app.get('/marketplace/directory', (req, res) => {
    const fs = require('fs');
    const path = require('path');
    
    try {
        const marketplaceData = JSON.parse(fs.readFileSync(path.join(__dirname, 'data', 'marketplaces.json'), 'utf8'));
        res.render('marketplace/directory', {
            title: 'Marketplace Directory - Wholesale.Education',
            marketplaces: marketplaceData.marketplaces
        });
    } catch (error) {
        console.error('Error loading marketplace data:', error);
        res.render('marketplace/directory', {
            title: 'Marketplace Directory - Wholesale.Education',
            marketplaces: []
        });
    }
});

// Admin routes (basic)
app.get('/admin', (req, res) => {
    res.render('admin/sb-admin-dashboard', { 
        title: 'Wholesale Education Admin Dashboard',
        totalPages: 1247,
        totalCategories: 89,
        totalSuppliers: 156,
        monthlyRevenue: 18000
    });
});

// Content Generator Admin Route
app.get('/admin/content-generator', (req, res) => {
    res.render('admin/content-generator', { title: 'Content Generator' });
});

// API endpoint for content generation
app.post('/api/generate-content', (req, res) => {
    const { count, type, industries, regions } = req.body;
    
    // Import the content generator
    const WholesaleContentGenerator = require('./content-generator');
    const generator = new WholesaleContentGenerator();
    
    // Generate content
    generator.generateAndSaveBulkContent(parseInt(count))
        .then(savedCount => {
            res.json({ 
                success: true, 
                message: `Successfully generated ${savedCount} content pieces`,
                count: savedCount
            });
        })
        .catch(error => {
            res.status(500).json({ 
                success: false, 
                message: 'Error generating content',
                error: error.message
            });
        });
});

// 404 handler
app.use((req, res) => {
    res.status(404).render('404', { title: 'Page Not Found' });
});

// Error handler
// Authentication middleware
const requireAuth = (req, res, next) => {
    if (req.session && req.session.userId) {
        return next();
    } else {
        res.redirect('/auth/login');
    }
};

// Authentication routes
app.get('/auth/login', (req, res) => {
    res.render('auth/login');
});

app.get('/auth/register', (req, res) => {
    res.render('auth/register');
});

app.post('/auth/login', (req, res) => {
    const { email, password } = req.body;
    
    db.get('SELECT * FROM users WHERE email = ? AND is_active = 1', [email], (err, user) => {
        if (err) {
            return res.status(500).json({ error: 'Database error' });
        }
        
        if (!user) {
            return res.status(401).json({ error: 'Invalid credentials' });
        }
        
        // In a real app, you'd hash and compare passwords
        if (user.password === password) {
            req.session.userId = user.id;
            req.session.user = user;
            res.redirect('/dashboard');
        } else {
            res.status(401).json({ error: 'Invalid credentials' });
        }
    });
});

app.post('/auth/register', (req, res) => {
    const { email, password, firstName, lastName, businessType, companyName } = req.body;
    
    db.run('INSERT INTO users (email, password, first_name, last_name, business_type, company_name) VALUES (?, ?, ?, ?, ?, ?)',
        [email, password, firstName, lastName, businessType, companyName], function(err) {
            if (err) {
                return res.status(500).json({ error: 'Registration failed' });
            }
            
            req.session.userId = this.lastID;
            res.redirect('/dashboard');
        });
});

app.post('/auth/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/');
});

// Dashboard routes
app.get('/dashboard', requireAuth, (req, res) => {
    res.render('dashboard/user-dashboard', { user: req.session.user });
});

app.get('/dashboard/learning', requireAuth, (req, res) => {
    res.render('dashboard/learning-path', { user: req.session.user });
});

app.get('/dashboard/suppliers', requireAuth, (req, res) => {
    res.render('dashboard/suppliers', { user: req.session.user });
});

app.get('/dashboard/marketplaces', requireAuth, (req, res) => {
    res.render('dashboard/marketplaces', { user: req.session.user });
});

app.get('/dashboard/tools', requireAuth, (req, res) => {
    res.render('dashboard/tools', { user: req.session.user });
});

app.get('/dashboard/analytics', requireAuth, (req, res) => {
    res.render('dashboard/analytics', { user: req.session.user });
});

app.get('/dashboard/profile', requireAuth, (req, res) => {
    res.render('dashboard/profile', { user: req.session.user });
});

// Tools routes
app.get('/tools', (req, res) => {
    res.render('tools/calculators');
});

app.get('/tools/calculators', (req, res) => {
    res.render('tools/calculators');
});

// API routes
app.get('/api/suppliers', (req, res) => {
    const { category, country, search } = req.query;
    let query = 'SELECT * FROM suppliers WHERE 1=1';
    let params = [];
    
    if (category) {
        query += ' AND category = ?';
        params.push(category);
    }
    
    if (country) {
        query += ' AND country = ?';
        params.push(country);
    }
    
    if (search) {
        query += ' AND (name LIKE ? OR description LIKE ?)';
        params.push(`%${search}%`, `%${search}%`);
    }
    
    query += ' ORDER BY rating DESC, name ASC';
    
    db.all(query, params, (err, suppliers) => {
        if (err) {
            return res.status(500).json({ error: 'Database error' });
        }
        res.json(suppliers);
    });
});

app.get('/api/marketplaces', (req, res) => {
    const { category, region } = req.query;
    let query = 'SELECT * FROM marketplaces WHERE 1=1';
    let params = [];
    
    if (category) {
        query += ' AND category = ?';
        params.push(category);
    }
    
    if (region) {
        query += ' AND region = ?';
        params.push(region);
    }
    
    query += ' ORDER BY monthly_traffic DESC';
    
    db.all(query, params, (err, marketplaces) => {
        if (err) {
            return res.status(500).json({ error: 'Database error' });
        }
        res.json(marketplaces);
    });
});

app.get('/api/analytics', requireAuth, (req, res) => {
    const { period = '30' } = req.query;
    
    const queries = {
        pageViews: `SELECT COUNT(*) as count FROM analytics WHERE event_type = 'page_view' AND created_at >= datetime('now', '-${period} days')`,
        userActivity: `SELECT COUNT(*) as count FROM analytics WHERE user_id = ? AND created_at >= datetime('now', '-${period} days')`,
        topPages: `SELECT page_id, COUNT(*) as views FROM analytics WHERE event_type = 'page_view' AND created_at >= datetime('now', '-${period} days') GROUP BY page_id ORDER BY views DESC LIMIT 10`
    };
    
    db.get(queries.pageViews, [], (err, pageViews) => {
        if (err) return res.status(500).json({ error: 'Database error' });
        
        db.get(queries.userActivity, [req.session.userId], (err, userActivity) => {
            if (err) return res.status(500).json({ error: 'Database error' });
            
            db.all(queries.topPages, [], (err, topPages) => {
                if (err) return res.status(500).json({ error: 'Database error' });
                
                res.json({
                    pageViews: pageViews.count,
                    userActivity: userActivity.count,
                    topPages: topPages
                });
            });
        });
    });
});

app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).render('error', { title: 'Server Error' });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Server running on http://localhost:${PORT}`);
    console.log(`📊 Ready to handle millions of pages!`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down server...');
    db.close((err) => {
        if (err) {
            console.error(err.message);
        }
        console.log('📁 Database connection closed.');
        process.exit(0);
    });
});
