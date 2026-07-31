<?php
declare(strict_types=1);

/**
 * HTMLへ安全に文字を表示する
 */
function h(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

/**
 * CSRF対策用トークンを作成する
 */
function getCsrfToken(): string
{
    if (
        !isset($_SESSION['csrf_token'])
        || !is_string($_SESSION['csrf_token'])
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * 送信されたCSRFトークンを確認する
 */
function verifyCsrfToken(?string $token): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($token)
        || !is_string($sessionToken)
        || $sessionToken === ''
        || !hash_equals($sessionToken, $token)
    ) {
        http_response_code(403);
        exit('不正なリクエストです。');
    }
}