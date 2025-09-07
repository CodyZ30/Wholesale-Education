<?php include 'includes/header.php'; ?>
<?php $guides = require __DIR__ . '/data/guides.php';
// Pull Quick Tips out for a dedicated bottom scroller
$quickTips = $guides['Quick Tips'] ?? [];
if (isset($guides['Quick Tips'])) unset($guides['Quick Tips']);
?>
<main class="container mx-auto px-4 md:px-6 py-10">
  <h1 class="text-3xl font-bold mb-6">Fishing Guides</h1>
  <div class="grid md:grid-cols-3 gap-8">
    <?php foreach ($guides as $category => $topics): ?>
      <section class="bg-white rounded-2xl border shadow-sm p-6">
        <h2 class="text-xl font-bold mb-3"><?php echo htmlspecialchars((string)$category); ?></h2>
        <ul class="space-y-2 list-disc list-inside">
          <?php foreach ($topics as $t): $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $t), '-')); ?>
            <li><a class="hover:underline" href="/guides/<?php echo $slug; ?>"><?php echo htmlspecialchars((string)$t); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($quickTips)): ?>
  <section class="mt-10">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-xl font-bold">Quick Tips <span class="text-sm text-gray-500">(<?php echo count($quickTips); ?>)</span></h2>
      <button id="qt-toggle" class="text-sm border rounded px-3 py-1 bg-white hover:bg-gray-50">Show All</button>
    </div>
    <div id="qt-row" class="overflow-x-auto">
      <div class="flex gap-2 whitespace-nowrap py-2">
        <?php foreach ($quickTips as $t): $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $t), '-')); ?>
          <a class="inline-block px-3 py-2 border rounded-full bg-white hover:bg-gray-50" href="/guides/<?php echo $slug; ?>"><?php echo htmlspecialchars((string)$t); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div id="qt-grid" class="hidden grid md:grid-cols-3 gap-2">
      <?php foreach ($quickTips as $t): $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $t), '-')); ?>
        <a class="block px-3 py-2 border rounded bg-white hover:bg-gray-50" href="/guides/<?php echo $slug; ?>"><?php echo htmlspecialchars((string)$t); ?></a>
      <?php endforeach; ?>
    </div>
    <script>
      (function(){
        const btn=document.getElementById('qt-toggle');
        const row=document.getElementById('qt-row');
        const grid=document.getElementById('qt-grid');
        btn?.addEventListener('click',()=>{
          const showingGrid = !grid.classList.contains('hidden');
          if (showingGrid){ grid.classList.add('hidden'); row.classList.remove('hidden'); btn.textContent='Show All'; }
          else { row.classList.add('hidden'); grid.classList.remove('hidden'); btn.textContent='Show Less'; }
        });
      })();
    </script>
  </section>
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>


