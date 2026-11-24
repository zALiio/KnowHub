<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function url(string $page = "home", array $params = []): string
{
    return "index.php?" . http_build_query(["page" => $page] + $params);
}
function redirect(string $page = "home", array $params = []): never
{
    header("Location: " . url($page, $params));
    exit();
}
function current_user(): ?array
{
    return $_SESSION["user"] ?? null;
}
function require_login(): void
{
    if (!current_user()) {
        $_SESSION["flash"] = [
            "type" => "info",
            "message" => "Sign in to continue.",
        ];
        redirect("login", ["return" => $_SERVER["REQUEST_URI"] ?? url()]);
    }
}
function flash(string $type, string $message): void
{
    $_SESSION["flash"] = compact("type", "message");
}
function take_flash(): ?array
{
    $value = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);
    return $value;
}
function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST["csrf"] ?? ""))) {
        http_response_code(419);
        exit("Your session expired. Please go back, refresh, and try again.");
    }
}
function post_string(string $key, int $max = 10000): string
{
    return trim(mb_substr((string) ($_POST[$key] ?? ""), 0, $max));
}
function excerpt(string $text, int $length = 180): string
{
    $text = trim(preg_replace("/\s+/", " ", strip_tags($text)) ?? "");
    return mb_strlen($text) > $length
        ? mb_substr($text, 0, $length) . "…"
        : $text;
}
function initials(string $name): string
{
    $parts = preg_split("/\s+/", trim($name)) ?: [];
    return strtoupper(
        mb_substr($parts[0] ?? "K", 0, 1) .
            (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ""),
    );
}
function nice_date(string $date): string
{
    return date("M j, Y", strtotime($date));
}
function reputation_for(int $userId): int
{
    $q = db()->prepare(
        "SELECT COALESCE(SUM(CASE WHEN v.vote_value=1 THEN 10 ELSE -2 END),0) AS score FROM question_votes v JOIN questions q ON q.id=v.question_id WHERE q.user_id=?",
    );
    $q->execute([$userId]);
    $questions = (int) $q->fetchColumn();
    $a = db()->prepare(
        "SELECT COALESCE(SUM(CASE WHEN v.vote_value=1 THEN 10 ELSE -2 END),0) AS score FROM answer_votes v JOIN answers a ON a.id=v.answer_id WHERE a.user_id=?",
    );
    $a->execute([$userId]);
    return $questions + (int) $a->fetchColumn();
}
function vote_score(string $kind, int $id): int
{
    $table = $kind === "answer" ? "answer_votes" : "question_votes";
    $column = $kind === "answer" ? "answer_id" : "question_id";
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(vote_value),0) FROM {$table} WHERE {$column}=?",
    );
    $stmt->execute([$id]);
    return (int) $stmt->fetchColumn();
}
function user_vote(string $kind, int $id, int $userId): int
{
    $table = $kind === "answer" ? "answer_votes" : "question_votes";
    $column = $kind === "answer" ? "answer_id" : "question_id";
    $stmt = db()->prepare(
        "SELECT vote_value FROM {$table} WHERE {$column}=? AND user_id=?",
    );
    $stmt->execute([$id, $userId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}
function question_tags(int $id): array
{
    $s = db()->prepare(
        "SELECT t.name,t.slug FROM tags t JOIN question_tags qt ON qt.tag_id=t.id WHERE qt.question_id=? ORDER BY t.name",
    );
    $s->execute([$id]);
    return $s->fetchAll();
}
function parse_tags(string $text): array
{
    $items = array_unique(
        array_filter(
            array_map(fn($v) => mb_strtolower(trim($v)), explode(",", $text)),
        ),
    );
    return array_slice(
        array_filter($items, fn($v) => $v !== "" && mb_strlen($v) <= 30),
        0,
        5,
    );
}
function sync_tags(int $questionId, string $text): void
{
    $pdo = db();
    $pdo->prepare("DELETE FROM question_tags WHERE question_id=?")->execute([
        $questionId,
    ]);
    foreach (parse_tags($text) as $name) {
        $slug = preg_replace(
            "/[^a-z0-9]+/",
            "-",
            iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $name) ?: $name,
        );
        $slug = trim($slug, "-") ?: substr(hash("sha256", $name), 0, 12);
        $pdo->prepare(
            "INSERT INTO tags(name,slug) VALUES(?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
        )->execute([$name, $slug]);
        $tagId = (int) $pdo->lastInsertId();
        if (!$tagId) {
            $s = $pdo->prepare("SELECT id FROM tags WHERE name=?");
            $s->execute([$name]);
            $tagId = (int) $s->fetchColumn();
        }
        $pdo->prepare(
            "INSERT IGNORE INTO question_tags(question_id,tag_id) VALUES(?,?)",
        )->execute([$questionId, $tagId]);
    }
}
function safe_return(string $return, string $fallback = "home"): never
{
    if (
        str_starts_with($return, "index.php?") &&
        !str_contains($return, "\r") &&
        !str_contains($return, "\n")
    ) {
        header("Location: " . $return);
        exit();
    }
    redirect($fallback);
}
