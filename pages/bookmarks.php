<section class="page-intro"><p class="eyebrow">YOUR READING LIST</p><h1>Saved questions</h1><p>Keep thoughtful questions close and come back when you have time to explore.</p></section><div class="feed-column saved-feed"><?php if (
    $questions
):
    foreach ($questions as $q) {
        include __DIR__ . "/../includes/question_card.php";
    }
else:
     ?><div class="empty-state"><span class="empty-icon">◇</span><h3>Your saved list is empty</h3><p>Save questions that you want to read again.</p><a class="button button-primary" href="<?= e(
    url(),
) ?>">Explore the feed</a></div><?php
endif; ?></div>
