<?php require_login();
$old = $_SESSION["old"] ?? [];
unset($_SESSION["old"]);
?><section class="form-page"><a class="back-link" href="<?= e(
    url(),
) ?>">← Back to questions</a><p class="eyebrow">SHARE WHAT YOU’RE WONDERING</p><h1>Ask a question</h1><p class="form-subtitle">A thoughtful question is the start of a great conversation.</p><form class="surface-form" method="post" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="question_create"><label for="title">Question title <span class="required">*</span></label><input id="title" name="title" maxlength="180" minlength="12" required placeholder="Be specific and imagine you’re asking a classmate" value="<?= e(
    $old["title"] ?? "",
) ?>"><small class="field-hint">Use a clear title that describes what you want to understand.</small><label for="body">Details <span class="required">*</span></label><textarea id="body" name="body" rows="8" minlength="20" maxlength="12000" required placeholder="Share what you’ve tried, what you understand so far, and where you got stuck."><?= e(
    $old["body"] ?? "",
) ?></textarea><div class="field-bottom"><small>Include enough context for someone to give a useful answer.</small><small data-count="body">0 / 12,000</small></div><label for="category">Topic <span class="required">*</span></label><select id="category" name="category_id" required><option value="">Choose the closest topic</option><?php foreach (
    $categories
    as $c
): ?><option value="<?= (int) $c["id"] ?>" <?= (int) ($old["category_id"] ??
    0) === (int) $c["id"]
    ? "selected"
    : "" ?>><?= e(
    $c["name"],
) ?></option><?php endforeach; ?></select><label for="tags">Tags <span class="optional">Optional</span></label><input id="tags" name="tags" maxlength="200" placeholder="python, debugging, recursion"><small class="field-hint">Separate up to 5 short tags with commas.</small><div class="form-actions"><a class="text-link" href="<?= e(
    url(),
) ?>">Cancel</a><button class="button button-primary">Publish question <span>↗</span></button></div></form></section>
