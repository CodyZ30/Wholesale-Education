<?php
// Simple test page to verify knowledge base loading
$knowledgeFile = __DIR__ . '/data/knowledge_base.json';
$knowledge_articles = [];

echo "<h1>Knowledge Base Test</h1>";

if (file_exists($knowledgeFile)) {
    $knowledge_articles = json_decode(file_get_contents($knowledgeFile), true) ?: [];
    echo "<p>✅ Knowledge base file found: " . $knowledgeFile . "</p>";
    echo "<p>📚 Total articles loaded: " . count($knowledge_articles) . "</p>";
    
    if (!empty($knowledge_articles)) {
        echo "<h2>First 5 Articles:</h2>";
        echo "<ul>";
        for ($i = 0; $i < min(5, count($knowledge_articles)); $i++) {
            $article = $knowledge_articles[$i];
            echo "<li><strong>" . htmlspecialchars($article['title'] ?? 'No title') . "</strong> - " . htmlspecialchars($article['category'] ?? 'No category') . "</li>";
        }
        echo "</ul>";
        
        // Show categories
        $categories = array_unique(array_column($knowledge_articles, 'category'));
        echo "<h2>Categories (" . count($categories) . "):</h2>";
        echo "<ul>";
        foreach ($categories as $category) {
            $count = count(array_filter($knowledge_articles, fn($a) => $a['category'] === $category));
            echo "<li>" . htmlspecialchars($category) . " (" . $count . " articles)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ No articles found in knowledge base</p>";
    }
} else {
    echo "<p>❌ Knowledge base file not found: " . $knowledgeFile . "</p>";
}

// Test search endpoint
echo "<h2>Search Endpoint Test</h2>";
$searchFile = __DIR__ . '/kb_search.php';
if (file_exists($searchFile)) {
    echo "<p>✅ Search endpoint file exists: " . $searchFile . "</p>";
} else {
    echo "<p>❌ Search endpoint file not found: " . $searchFile . "</p>";
}
?>
