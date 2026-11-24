<?php require_login();
$stmt = $pdo->prepare("SELECT full_name,bio FROM users WHERE id=?");
$stmt->execute([current_user()["id"]]);
$editUser = $stmt->fetch();
?><section class="form-page"><a class="back-link" href="<?= e(
    url("profile", ["username" => current_user()["username"]]),
) ?>">← Back to profile</a><p class="eyebrow">MAKE IT YOURS</p><h1>Edit your profile</h1><p class="form-subtitle">Help other students get to know you.</p><form class="surface-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile_update"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="100" required value="<?= e(
    $editUser["full_name"],
) ?>"><label for="bio">About you <span class="optional">Optional</span></label><textarea id="bio" name="bio" maxlength="500" rows="5" placeholder="What are you learning or exploring?"><?= e(
    $editUser["bio"],
) ?></textarea><div class="field-bottom"><span></span><small data-count="bio">0 / 500</small></div><div class="form-actions"><a class="text-link" href="<?= e(
    url("profile", ["username" => current_user()["username"]]),
) ?>">Cancel</a><button class="button button-primary">Save profile</button></div></form></section>
