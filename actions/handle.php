<?php
declare(strict_types=1);

if (!defined("KNOWHUB_FRONT_CONTROLLER")) {
    http_response_code(404);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $action = (string) ($_POST["action"] ?? "");
    $pdo = db();
    try {
        switch ($action) {
            case "register":
                $name = post_string("full_name", 100);
                $username = post_string("username", 30);
                $email = mb_strtolower(post_string("email", 190));
                $password = (string) ($_POST["password"] ?? "");
                $confirm = (string) ($_POST["password_confirm"] ?? "");
                if (
                    mb_strlen($name) < 2 ||
                    !preg_match('/^[A-Za-z0-9_]{3,30}$/', $username) ||
                    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                    strlen($password) < 8 ||
                    $password !== $confirm
                ) {
                    flash(
                        "error",
                        "Check your details: use a 3–30 character username, valid email, matching passwords, and a password of at least 8 characters.",
                    );
                    $_SESSION["old"] = $_POST;
                    redirect("register");
                }
                $stmt = $pdo->prepare(
                    "INSERT INTO users(full_name,username,email,password_hash) VALUES(?,?,?,?)",
                );
                $stmt->execute([
                    $name,
                    $username,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);
                session_regenerate_id(true);
                $_SESSION["user"] = [
                    "id" => (int) $pdo->lastInsertId(),
                    "full_name" => $name,
                    "username" => $username,
                ];
                flash("success", "Welcome to KnowHub! Your account is ready.");
                redirect("home");
            case "login":
                $identity = mb_strtolower(post_string("identity", 190));
                $stmt = $pdo->prepare(
                    "SELECT id,full_name,username,password_hash FROM users WHERE email=? OR username=? LIMIT 1",
                );
                $stmt->execute([$identity, $identity]);
                $user = $stmt->fetch();
                if (
                    !$user ||
                    !password_verify(
                        (string) ($_POST["password"] ?? ""),
                        $user["password_hash"],
                    )
                ) {
                    flash(
                        "error",
                        "That sign-in did not match an account. Check your details and try again.",
                    );
                    redirect("login");
                }
                session_regenerate_id(true);
                $_SESSION["user"] = [
                    "id" => (int) $user["id"],
                    "full_name" => $user["full_name"],
                    "username" => $user["username"],
                ];
                flash("success", "You are signed in.");
                safe_return((string) ($_POST["return"] ?? ""));
            case "logout":
                $_SESSION = [];
                session_regenerate_id(true);
                flash("success", "You have signed out.");
                redirect("home");
            case "question_create":
                require_login();
                $title = post_string("title", 180);
                $body = post_string("body", 12000);
                $category = (int) ($_POST["category_id"] ?? 0);
                if (
                    mb_strlen($title) < 12 ||
                    mb_strlen($body) < 20 ||
                    !$category
                ) {
                    flash(
                        "error",
                        "Add a clear title (at least 12 characters), details (at least 20 characters), and a category.",
                    );
                    $_SESSION["old"] = $_POST;
                    redirect("ask");
                }
                $c = $pdo->prepare("SELECT id FROM categories WHERE id=?");
                $c->execute([$category]);
                if (!$c->fetchColumn()) {
                    flash("error", "Choose a valid category.");
                    redirect("ask");
                }
                $pdo->beginTransaction();
                $pdo->prepare(
                    "INSERT INTO questions(user_id,category_id,title,body) VALUES(?,?,?,?)",
                )->execute([current_user()["id"], $category, $title, $body]);
                $id = (int) $pdo->lastInsertId();
                sync_tags($id, post_string("tags", 200));
                $pdo->commit();
                flash("success", "Your question is live.");
                redirect("question", ["id" => $id]);
            case "question_update":
                require_login();
                $id = (int) ($_POST["id"] ?? 0);
                $s = $pdo->prepare("SELECT user_id FROM questions WHERE id=?");
                $s->execute([$id]);
                $owner = $s->fetchColumn();
                if ((int) $owner !== (int) current_user()["id"]) {
                    http_response_code(403);
                    exit("You cannot edit this question.");
                }
                $title = post_string("title", 180);
                $body = post_string("body", 12000);
                $category = (int) ($_POST["category_id"] ?? 0);
                if (mb_strlen($title) < 12 || mb_strlen($body) < 20) {
                    flash(
                        "error",
                        "The title needs 12 characters and the details need 20.",
                    );
                    redirect("question", ["id" => $id, "edit" => 1]);
                }
                $pdo->beginTransaction();
                $pdo->prepare(
                    "UPDATE questions SET title=?,body=?,category_id=? WHERE id=?",
                )->execute([$title, $body, $category, $id]);
                sync_tags($id, post_string("tags", 200));
                $pdo->commit();
                flash("success", "Question updated.");
                redirect("question", ["id" => $id]);
            case "question_delete":
                require_login();
                $id = (int) ($_POST["id"] ?? 0);
                $s = $pdo->prepare(
                    "DELETE FROM questions WHERE id=? AND user_id=?",
                );
                $s->execute([$id, current_user()["id"]]);
                flash(
                    $s->rowCount() ? "success" : "error",
                    $s->rowCount()
                        ? "Question deleted."
                        : "You can only delete your own question.",
                );
                redirect("home");
            case "answer_create":
                require_login();
                $id = (int) ($_POST["question_id"] ?? 0);
                $body = post_string("body", 12000);
                if (mb_strlen($body) < 10) {
                    flash(
                        "error",
                        "Please write an answer with at least 10 characters.",
                    );
                    redirect("question", ["id" => $id]);
                }
                $pdo->prepare(
                    "INSERT INTO answers(question_id,user_id,body) VALUES(?,?,?)",
                )->execute([$id, current_user()["id"], $body]);
                flash("success", "Your answer has been posted.");
                redirect("question", ["id" => $id]);
            case "answer_update":
                require_login();
                $id = (int) ($_POST["id"] ?? 0);
                $body = post_string("body", 12000);
                if (mb_strlen($body) < 10) {
                    flash("error", "An answer needs at least 10 characters.");
                    redirect("question", [
                        "id" => (int) ($_POST["question_id"] ?? 0),
                    ]);
                }
                $s = $pdo->prepare(
                    "UPDATE answers SET body=? WHERE id=? AND user_id=?",
                );
                $s->execute([$body, $id, current_user()["id"]]);
                flash(
                    $s->rowCount() ? "success" : "info",
                    $s->rowCount()
                        ? "Answer updated."
                        : "No changes were needed.",
                );
                redirect("question", ["id" => (int) $_POST["question_id"]]);
            case "answer_delete":
                require_login();
                $id = (int) ($_POST["id"] ?? 0);
                $qid = (int) ($_POST["question_id"] ?? 0);
                $s = $pdo->prepare(
                    "DELETE FROM answers WHERE id=? AND user_id=?",
                );
                $s->execute([$id, current_user()["id"]]);
                flash(
                    $s->rowCount() ? "success" : "error",
                    $s->rowCount()
                        ? "Answer deleted."
                        : "You can only delete your own answer.",
                );
                redirect("question", ["id" => $qid]);
            case "vote":
                require_login();
                $kind = (string) ($_POST["kind"] ?? "question");
                $id = (int) ($_POST["id"] ?? 0);
                $value = (int) ($_POST["value"] ?? 0);
                if (
                    !in_array($kind, ["question", "answer"], true) ||
                    !in_array($value, [-1, 1], true)
                ) {
                    flash("error", "That vote is not valid.");
                    redirect("home");
                }
                $table = $kind === "answer" ? "answers" : "questions";
                $voteTable =
                    $kind === "answer" ? "answer_votes" : "question_votes";
                $idCol = $kind === "answer" ? "answer_id" : "question_id";
                $owner = $pdo->prepare(
                    "SELECT user_id FROM {$table} WHERE id=?",
                );
                $owner->execute([$id]);
                $author = $owner->fetchColumn();
                if (!$author) {
                    flash("error", "That post is no longer available.");
                    redirect("home");
                }
                if ((int) $author === (int) current_user()["id"]) {
                    flash("error", "You cannot vote on your own post.");
                    redirect("question", [
                        "id" => (int) ($_POST["question_id"] ?? $id),
                    ]);
                }
                $pdo->beginTransaction();
                $previous = $pdo->prepare(
                    "SELECT vote_value FROM {$voteTable} WHERE {$idCol}=? AND user_id=? FOR UPDATE",
                );
                $previous->execute([$id, current_user()["id"]]);
                $old = $previous->fetchColumn();
                if ((int) $old === $value) {
                    $pdo->prepare(
                        "DELETE FROM {$voteTable} WHERE {$idCol}=? AND user_id=?",
                    )->execute([$id, current_user()["id"]]);
                } else {
                    $pdo->prepare(
                        "INSERT INTO {$voteTable}({$idCol},user_id,vote_value) VALUES(?,?,?) ON DUPLICATE KEY UPDATE vote_value=VALUES(vote_value)",
                    )->execute([$id, current_user()["id"], $value]);
                }
                $pdo->commit();
                redirect("question", [
                    "id" => (int) ($_POST["question_id"] ?? $id),
                ]);
            case "accept_answer":
                require_login();
                $qid = (int) ($_POST["question_id"] ?? 0);
                $aid = (int) ($_POST["answer_id"] ?? 0);
                $own = $pdo->prepare(
                    "SELECT user_id FROM questions WHERE id=?",
                );
                $own->execute([$qid]);
                if ((int) $own->fetchColumn() !== (int) current_user()["id"]) {
                    http_response_code(403);
                    exit(
                        "Only the question author can choose the accepted answer."
                    );
                }
                $check = $pdo->prepare(
                    "SELECT id FROM answers WHERE id=? AND question_id=?",
                );
                $check->execute([$aid, $qid]);
                if (!$check->fetchColumn()) {
                    flash(
                        "error",
                        "That answer does not belong to this question.",
                    );
                    redirect("question", ["id" => $qid]);
                }
                $pdo->prepare(
                    "UPDATE questions SET accepted_answer_id=IF(accepted_answer_id=?,NULL,?) WHERE id=?",
                )->execute([$aid, $aid, $qid]);
                flash("success", "Accepted answer updated.");
                redirect("question", ["id" => $qid]);
            case "bookmark":
                require_login();
                $qid = (int) ($_POST["question_id"] ?? 0);
                $has = $pdo->prepare(
                    "SELECT 1 FROM bookmarks WHERE user_id=? AND question_id=?",
                );
                $has->execute([current_user()["id"], $qid]);
                if ($has->fetchColumn()) {
                    $pdo->prepare(
                        "DELETE FROM bookmarks WHERE user_id=? AND question_id=?",
                    )->execute([current_user()["id"], $qid]);
                    flash("info", "Removed from your saved questions.");
                } else {
                    $pdo->prepare(
                        "INSERT IGNORE INTO bookmarks(user_id,question_id) VALUES(?,?)",
                    )->execute([current_user()["id"], $qid]);
                    flash("success", "Question saved to your bookmarks.");
                }
                redirect("question", ["id" => $qid]);
            case "profile_update":
                require_login();
                $name = post_string("full_name", 100);
                $bio = post_string("bio", 500);
                if (mb_strlen($name) < 2) {
                    flash(
                        "error",
                        "Your name must contain at least 2 characters.",
                    );
                    redirect("edit-profile");
                }
                $pdo->prepare(
                    "UPDATE users SET full_name=?,bio=? WHERE id=?",
                )->execute([$name, $bio, current_user()["id"]]);
                $_SESSION["user"]["full_name"] = $name;
                flash("success", "Your profile is updated.");
                redirect("profile", ["username" => current_user()["username"]]);
            default:
                flash("error", "That action is not available.");
                redirect("home");
        }
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($action === "register" && (string) $ex->getCode() === "23000") {
            flash("error", "That username or email is already registered.");
            redirect("register");
        }
        error_log("KnowHub database error: " . $ex->getMessage());
        flash(
            "error",
            "We could not save that just now. Please check your input and try again.",
        );
        redirect($page, isset($_GET["id"]) ? ["id" => (int) $_GET["id"]] : []);
    }
}
