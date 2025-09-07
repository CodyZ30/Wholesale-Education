const express = require('express');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;

// Serve static files
app.use(express.static('.'));

// Route for homepage
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

// Route for marketplace overview
app.get('/marketplace', (req, res) => {
    res.sendFile(path.join(__dirname, 'views/marketplace/overview.ejs'));
});

// Route for marketplace directory
app.get('/marketplace/directory', (req, res) => {
    res.sendFile(path.join(__dirname, 'views/marketplace/directory.ejs'));
});

// Catch all other routes and serve index.html
app.get('*', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.listen(PORT, () => {
    console.log(`🚀 Wholesale.Education server running on port ${PORT}`);
    console.log(`📱 Open http://localhost:${PORT} to view your site`);
});
