<?php
declare(strict_types=1);
require __DIR__ . "/includes/bootstrap.php";
define("KNOWHUB_FRONT_CONTROLLER", true);

$page = (string) ($_GET["page"] ?? "home");
$allowedPages = [
    "home",
    "ask",
    "question",
    "login",
    "register",
    "categories",
    "category",
    "search",
    "profile",
    "edit-profile",
    "bookmarks",
];
if (!in_array($page, $allowedPages, true)) {
    $page = "home";
}
try {
    $pdo = db();
    $pdo->query("SELECT 1 FROM categories LIMIT 0");
} catch (Throwable) {
    http_response_code(503);
    require __DIR__ . "/pages/setup.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require __DIR__ . "/actions/handle.php";
}
$categories = $pdo
    ->query(
        "SELECT c.*, (SELECT COUNT(*) FROM questions q WHERE q.category_id=c.id) question_count FROM categories c ORDER BY c.name",
    )
    ->fetchAll();
$sort = (string) ($_GET["sort"] ?? "newest");
$order =
    [
        "newest" => "q.created_at DESC",
        "popular" => "vote_score DESC, q.created_at DESC",
        "answered" => "answer_count DESC, q.created_at DESC",
    ][$sort] ?? "q.created_at DESC";
$questions = [];
$total = 0;
$perPage = 8;
$pageNum = max(1, (int) ($_GET["p"] ?? 1));
$offset = ($pageNum - 1) * $perPage;
$baseSelect =
    "SELECT q.*,u.full_name,u.username,c.name category_name,c.slug category_slug,(SELECT COUNT(*) FROM answers a WHERE a.question_id=q.id) answer_count,(SELECT COALESCE(SUM(v.vote_value),0) FROM question_votes v WHERE v.question_id=q.id) vote_score";
if ($page === "home") {
    $total = (int) $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    $questions = $pdo
        ->query(
            $baseSelect .
                " FROM questions q JOIN users u ON u.id=q.user_id JOIN categories c ON c.id=q.category_id ORDER BY " .
                $order .
                " LIMIT " .
                (int) $perPage .
                " OFFSET " .
                (int) $offset,
        )
        ->fetchAll();
} elseif ($page === "category") {
    $slug = (string) ($_GET["slug"] ?? "");
    $cat = $pdo->prepare("SELECT * FROM categories WHERE slug=?");
    $cat->execute([$slug]);
    $activeCategory = $cat->fetch();
    if ($activeCategory) {
        $s = $pdo->prepare(
            "SELECT COUNT(*) FROM questions WHERE category_id=?",
        );
        $s->execute([$activeCategory["id"]]);
        $total = (int) $s->fetchColumn();
        $s = $pdo->prepare(
            $baseSelect .
                " FROM questions q JOIN users u ON u.id=q.user_id JOIN categories c ON c.id=q.category_id WHERE c.id=? ORDER BY " .
                str_replace("q.created_at DESC", "q.created_at DESC", $order) .
                " LIMIT " .
                (int) $perPage .
                " OFFSET " .
                (int) $offset,
        );
        $s->execute([$activeCategory["id"]]);
        $questions = $s->fetchAll();
    }
} elseif ($page === "search") {
    $term = trim((string) ($_GET["q"] ?? ""));
    if ($term !== "") {
        $like = "%" . addcslashes($term, "%_\\") . "%";
        $where =
            "(q.title LIKE ? OR q.body LIKE ? OR c.name LIKE ? OR EXISTS(SELECT 1 FROM question_tags qt JOIN tags t ON t.id=qt.tag_id WHERE qt.question_id=q.id AND t.name LIKE ?))";
        $s = $pdo->prepare(
            "SELECT COUNT(*) FROM questions q JOIN categories c ON c.id=q.category_id WHERE " .
                $where,
        );
        $s->execute([$like, $like, $like, $like]);
        $total = (int) $s->fetchColumn();
        $s = $pdo->prepare(
            $baseSelect .
                " FROM questions q JOIN users u ON u.id=q.user_id JOIN categories c ON c.id=q.category_id WHERE " .
                $where .
                " ORDER BY " .
                $order .
                " LIMIT " .
                (int) $perPage .
                " OFFSET " .
                (int) $offset,
        );
        $s->execute([$like, $like, $like, $like]);
        $questions = $s->fetchAll();
    }
}

$title = ucfirst($page);
$contentFile = __DIR__ . "/pages/" . $page . ".php";
if ($page === "question") {
    $s = $pdo->prepare(
        $baseSelect .
            ",qv.vote_value my_vote FROM questions q JOIN users u ON u.id=q.user_id JOIN categories c ON c.id=q.category_id LEFT JOIN question_votes qv ON qv.question_id=q.id AND qv.user_id=? WHERE q.id=?",
    );
    $s->execute([current_user()["id"] ?? 0, (int) ($_GET["id"] ?? 0)]);
    $question = $s->fetch();
    if (!$question) {
        http_response_code(404);
        $title = "Question not found";
    } else {
        $title = $question["title"];
        $a = $pdo->prepare(
            "SELECT a.*,u.full_name,u.username,av.vote_value my_vote,(SELECT COALESCE(SUM(v.vote_value),0) FROM answer_votes v WHERE v.answer_id=a.id) vote_score FROM answers a JOIN users u ON u.id=a.user_id LEFT JOIN answer_votes av ON av.answer_id=a.id AND av.user_id=? WHERE a.question_id=? ORDER BY (a.id=?) DESC,a.created_at ASC",
        );
        $a->execute([
            current_user()["id"] ?? 0,
            $question["id"],
            $question["accepted_answer_id"] ?? 0,
        ]);
        $answers = $a->fetchAll();
        $tags = question_tags((int) $question["id"]);
        $isBookmarked = false;
        if (current_user()) {
            $b = $pdo->prepare(
                "SELECT 1 FROM bookmarks WHERE user_id=? AND question_id=?",
            );
            $b->execute([current_user()["id"], $question["id"]]);
            $isBookmarked = (bool) $b->fetchColumn();
        }
    }
}
if ($page === "profile") {
    $s = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $s->execute([
        (string) ($_GET["username"] ?? (current_user()["username"] ?? "")),
    ]);
    $profile = $s->fetch();
    if ($profile) {
        $profile["reputation"] = reputation_for((int) $profile["id"]);
        $s = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE user_id=?");
        $s->execute([$profile["id"]]);
        $profile["question_count"] = (int) $s->fetchColumn();
        $s = $pdo->prepare("SELECT COUNT(*) FROM answers WHERE user_id=?");
        $s->execute([$profile["id"]]);
        $profile["answer_count"] = (int) $s->fetchColumn();
        $s = $pdo->prepare(
            $baseSelect .
                " FROM questions q JOIN users u ON u.id=q.user_id JOIN categories c ON c.id=q.category_id WHERE q.user_id=? ORDER BY q.created_at DESC LIMIT 6",
        );
        $s->execute([$profile["id"]]);
        $profileQuestions = $s->fetchAll();
        $s = $pdo->prepare(
            "SELECT a.*,q.title question_title,q.id question_id FROM answers a JOIN questions q ON q.id=a.question_id WHERE a.user_id=? ORDER BY a.created_at DESC LIMIT 6",
        );
        $s->execute([$profile["id"]]);
        $profileAnswers = $s->fetchAll();
    } else {
        $title = "Profile not found";
        http_response_code(404);
    }
}
if ($page === "ask") {
    $title = "Ask a question";
}
if ($page === "login") {
    $title = "Welcome back";
}
if ($page === "register") {
    $title = "Join KnowHub";
}
if ($page === "categories") {
    $title = "Explore categories";
}
if ($page === "edit-profile") {
    $title = "Edit your profile";
}
if (in_array($page, ["ask", "edit-profile"], true)) {
    require_login();
}
if ($page === "bookmarks") {
    $title = "Saved questions";
    require_login();
    $s = $pdo->prepare(
        $baseSelect .
            " FROM bookmarks b JOIN questions q ON q.id=b.question_id JOIN users u ON u.id=q.user_id JOIN categories c ON c.id=q.category_id WHERE b.user_id=? ORDER BY b.created_at DESC",
    );
    $s->execute([current_user()["id"]]);
    $questions = $s->fetchAll();
}
include __DIR__ . "/includes/header.php";
if (is_file($contentFile)) {
    include $contentFile;
} else {
    include __DIR__ . "/pages/not-found.php";
}
include __DIR__ . "/includes/footer.php";
