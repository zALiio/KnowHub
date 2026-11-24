<section class="page-intro"><p class="eyebrow">FOLLOW YOUR CURIOSITY</p><h1>Explore topics</h1><p>Find your people, discover a new idea, or ask the question you’ve been thinking about.</p></section><section class="category-grid"><?php foreach (
    $categories
    as $c
): ?><a class="category-card" href="<?= e(
    url("category", ["slug" => $c["slug"]]),
) ?>"><span class="category-icon"><?= e(
    mb_strtoupper(mb_substr($c["name"], 0, 1)),
) ?></span><span class="category-arrow">↗</span><h2><?= e(
    $c["name"],
) ?></h2><p><?= e(
    $c["description"],
) ?></p><span class="category-count"><?= (int) $c[
    "question_count"
] ?> <?= (int) $c["question_count"] === 1
     ? "question"
     : "questions" ?></span></a><?php endforeach; ?></section>
