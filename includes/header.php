<?php $flash = take_flash();
$me = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="A student community for sharing computer science and AI knowledge.">
  <title><?= e($title) ?> · KnowHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar"><div class="nav-shell">
  <a class="brand" href="<?= e(
      url(),
  ) ?>"><span class="brand-mark">K</span><span>know<span class="brand-blue">hub</span><small>LEARN TOGETHER</small></span></a>
  <button class="mobile-menu" aria-label="Open navigation" aria-expanded="false" data-menu-toggle><span></span><span></span><span></span></button>
  <nav class="nav-links" data-nav><a class="<?= $page === "home"
      ? "active"
      : "" ?>" href="<?= e(url()) ?>">Discover</a><a class="<?= in_array(
    $page,
    ["categories", "category"],
    true,
)
    ? "active"
    : "" ?>" href="<?= e(url("categories")) ?>">Topics</a></nav>
  <form class="nav-search" action="index.php" method="get"><input type="hidden" name="page" value="search"><label class="sr-only" for="nav-q">Search questions</label><span class="search-icon" aria-hidden="true">⌕</span><input id="nav-q" name="q" type="search" placeholder="Search questions..." value="<?= $page ===
  "search"
      ? e($_GET["q"] ?? "")
      : "" ?>"><kbd>⌘ K</kbd></form>
  <div class="nav-actions"><a class="button button-primary button-ask" href="<?= e(
      url("ask"),
  ) ?>"><span>＋</span> Ask a question</a>
  <?php if (
      $me
  ): ?><div class="user-menu"><button class="nav-avatar" type="button" aria-label="Open account menu" aria-expanded="false" data-user-toggle><?= e(
    initials($me["full_name"]),
) ?></button><div class="user-dropdown"><a href="<?= e(
    url("profile", ["username" => $me["username"]]),
) ?>">My profile</a><a href="<?= e(
    url("bookmarks"),
) ?>">Saved questions</a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button>Sign out</button></form></div></div>
  <?php else: ?><a class="login-link" href="<?= e(
    url("login"),
) ?>">Sign in</a><a class="button button-quiet" href="<?= e(
    url("register"),
) ?>">Join free</a><?php endif; ?></div>
</div></header>
<main class="page-shell">
<?php if ($flash): ?><div class="alert alert-<?= e(
    $flash["type"],
) ?>" role="status"><span><?= e(
    $flash["message"],
) ?></span><button type="button" aria-label="Dismiss" data-dismiss>×</button></div><?php endif; ?>
