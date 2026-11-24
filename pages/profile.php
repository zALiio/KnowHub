<?php if (
    empty($profile)
): ?><section class="empty-state"><h1>Profile not found</h1><p>We couldn’t find that student profile.</p><a class="button button-primary" href="<?= e(
    url(),
) ?>">Back to KnowHub</a></section><?php else: ?><section class="profile-hero"><div class="profile-avatar"><?= e(
    initials($profile["full_name"]),
) ?></div><div class="profile-info"><p class="eyebrow">KNOWHUB MEMBER</p><h1><?= e(
    $profile["full_name"],
) ?></h1><p class="profile-handle">@<?= e(
    $profile["username"],
) ?> <span>·</span> Joined <?= e(
     nice_date($profile["created_at"]),
 ) ?></p><?php if ($profile["bio"]): ?><p class="profile-bio"><?= e(
    $profile["bio"],
) ?></p><?php else: ?><p class="profile-bio muted">This student hasn’t added a bio yet.</p><?php endif; ?></div><?php if (
    current_user() &&
    (int) current_user()["id"] === (int) $profile["id"]
): ?><a class="button button-outline profile-edit" href="<?= e(
    url("edit-profile"),
) ?>">Edit profile</a><?php endif; ?></section><section class="stats-row"><div><strong><?= (int) $profile[
    "reputation"
] ?></strong><span>Reputation</span></div><div><strong><?= (int) $profile[
    "question_count"
] ?></strong><span>Questions</span></div><div><strong><?= (int) $profile[
    "answer_count"
] ?></strong><span>Answers</span></div></section><section class="profile-content"><div><div class="section-heading"><h2>Questions</h2></div><?php if (
    $profileQuestions
):
    foreach ($profileQuestions as $q) {
        include __DIR__ . "/../includes/question_card.php";
    }
else:
     ?><div class="empty-state compact-empty"><p>No questions from <?= e(
    $profile["full_name"],
) ?> yet.</p></div><?php
endif; ?></div><aside class="profile-answers"><div class="section-heading"><h2>Recent answers</h2></div><?php
foreach ($profileAnswers as $a): ?><a class="profile-answer" href="<?= e(
    url("question", ["id" => $a["question_id"]]),
) ?>"><span class="answer-label">ANSWERED</span><b><?= e(
    $a["question_title"],
) ?></b><small><?= e(
    nice_date($a["created_at"]),
) ?></small></a><?php endforeach;
if (!$profileAnswers): ?><p class="muted">No answers shared yet.</p><?php endif;
?></aside></section><?php endif; ?>
