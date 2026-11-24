<?php $cardVote = current_user()
    ? user_vote("question", (int) $q["id"], (int) current_user()["id"])
    : 0; ?><article class="question-card"><div class="vote-rail"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="vote"><input type="hidden" name="kind" value="question"><input type="hidden" name="id" value="<?= (int) $q[
    "id"
] ?>"><input type="hidden" name="question_id" value="<?= (int) $q[
    "id"
] ?>"><button class="vote-button <?= $cardVote === 1
    ? "voted"
    : "" ?>" name="value" value="1" aria-label="Upvote">↑</button></form><strong><?= (int) $q[
    "vote_score"
] ?></strong><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="vote"><input type="hidden" name="kind" value="question"><input type="hidden" name="id" value="<?= (int) $q[
    "id"
] ?>"><input type="hidden" name="question_id" value="<?= (int) $q[
    "id"
] ?>"><button class="vote-button down <?= $cardVote === -1
    ? "voted"
    : "" ?>" name="value" value="-1" aria-label="Downvote">↓</button></form></div><div class="question-main"><div class="card-meta"><a class="category-pill" href="<?= e(
    url("category", ["slug" => $q["category_slug"]]),
) ?>"><?= e(
    $q["category_name"],
) ?></a><span class="meta-dot">·</span><span><?= e(
    nice_date($q["created_at"]),
) ?></span><?php if (
    (int) $q["answer_count"] > 0
): ?><span class="answered-label">✓ Answered</span><?php endif; ?></div><h3><a href="<?= e(
    url("question", ["id" => $q["id"]]),
) ?>"><?= e($q["title"]) ?></a></h3><p class="question-excerpt"><?= e(
    excerpt($q["body"]),
) ?></p><?php
$cardTags = question_tags((int) $q["id"]);
if ($cardTags): ?><div class="tag-row"><?php foreach (
    array_slice($cardTags, 0, 3)
    as $tag
): ?><span class="tag">#<?= e(
    $tag["name"],
) ?></span><?php endforeach; ?></div><?php endif;
?><div class="card-footer"><a class="author-mini" href="<?= e(
    url("profile", ["username" => $q["username"]]),
) ?>"><span class="avatar avatar-sm"><?= e(
    initials($q["full_name"]),
) ?></span><span><?= e(
    $q["full_name"],
) ?></span></a><a class="answer-count" href="<?= e(
    url("question", ["id" => $q["id"]]),
) ?>"><span>▤</span><?= (int) $q[
    "answer_count"
] ?> answers <span class="arrow">→</span></a></div></div></article>
