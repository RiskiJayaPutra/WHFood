<?php

/**
 * ============================================================================
 * WHFood - Helper Functions
 * ============================================================================
 * 
 * Kumpulan fungsi utility untuk aplikasi WHFood.
 * 
 * @package     WHFood
 * @subpackage  Helpers
 * @author      WHFood Development Team
 * @version     1.0.0
 */

declare(strict_types=1);

// ============================================================================
// URL & REDIRECT HELPERS
// ============================================================================

/**
 * Mendapatkan base URL aplikasi
 * 
 * @return string Base URL
 */
function baseUrl(): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$protocol}://{$host}/whfood";
}

/**
 * Generate URL lengkap
 * 
 * @param string $path Path relatif (contoh: 'masuk', 'produk/1')
 * @return string URL lengkap
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return baseUrl() . ($path ? "/{$path}" : '');
}

/**
 * Generate URL untuk asset
 * 
 * @param string $path Path ke asset (contoh: 'css/style.css')
 * @return string URL asset
 */
function asset(string $path): string
{
    return url("assets/{$path}");
}

/**
 * Redirect ke URL tertentu
 * 
 * @param string $path Path tujuan
 * @param int    $code HTTP status code
 */
function redirect(string $path, int $code = 302): void
{
    header("Location: " . url($path), true, $code);
    exit;
}

/**
 * Redirect kembali ke halaman sebelumnya
 */
function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    header("Location: {$referer}");
    exit;
}

// ============================================================================
// CSRF PROTECTION
// ============================================================================

/**
 * Generate atau get CSRF token
 * 
 * @return string CSRF token
 */
function csrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrfToken'])) {
        $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrfToken'];
}

/**
 * Generate hidden input field untuk CSRF
 * 
 * @return string HTML hidden input
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrfToken" value="' . csrfToken() . '">';
}

/**
 * Validasi CSRF token
 * 
 * @param string|null $token Token yang dikirim
 * @return bool True jika valid
 */
function verifyCsrf(?string $token): bool
{
    return $token !== null && hash_equals(csrfToken(), $token);
}

// ============================================================================
// SESSION & FLASH MESSAGES
// ============================================================================

/**
 * Set atau get flash message
 * 
 * @param string      $key     Key flash message
 * @param string|null $message Message (null untuk get)
 * @return string|null Flash message atau null
 */
function flash(string $key, ?string $message = null): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

/**
 * Set flash error message
 * 
 * @param string $message Error message
 */
function flashError(string $message): void
{
    flash('error', $message);
}

/**
 * Set flash success message
 * 
 * @param string $message Success message
 */
function flashSuccess(string $message): void
{
    flash('success', $message);
}

/**
 * Get old input value (untuk repopulasi form)
 * 
 * @param string $key     Key input
 * @param mixed  $default Default value
 * @return mixed Old value atau default
 */
function old(string $key, mixed $default = ''): mixed
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $value = $_SESSION['old'][$key] ?? $default;
    return $value;
}

/**
 * Simpan input ke session (untuk old())
 * 
 * @param array $data Input data
 */
function flashOld(array $data): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Jangan simpan password
    unset($data['password'], $data['passwordConfirm']);
    $_SESSION['old'] = $data;
}

/**
 * Clear old input
 */
function clearOld(): void
{
    unset($_SESSION['old']);
}

// ============================================================================
// SANITIZATION & VALIDATION
// ============================================================================

/**
 * Sanitize string untuk output HTML
 * 
 * @param string|null $value String untuk di-sanitize
 * @return string Sanitized string
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input request
 * 
 * @param string $key   Key dari $_POST atau $_GET
 * @param string $type  Tipe data: 'string', 'int', 'float', 'email', 'url'
 * @return mixed Sanitized value
 */
function input(string $key, string $type = 'string'): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? null;
    
    if ($value === null) {
        return null;
    }
    
    return match ($type) {
        'int' => filter_var($value, FILTER_VALIDATE_INT) ?: 0,
        'float' => filter_var($value, FILTER_VALIDATE_FLOAT) ?: 0.0,
        'email' => filter_var(trim($value), FILTER_SANITIZE_EMAIL),
        'url' => filter_var(trim($value), FILTER_SANITIZE_URL),
        default => trim(strip_tags($value))
    };
}

/**
 * Validasi data dengan aturan
 * 
 * @param array $data  Data untuk divalidasi
 * @param array $rules Aturan validasi
 * @return array Array of errors (empty jika valid)
 */
function validate(array $data, array $rules): array
{
    $errors = [];
    
    foreach ($rules as $field => $ruleString) {
        $ruleList = explode('|', $ruleString);
        $value = $data[$field] ?? null;
        $fieldLabel = ucfirst(str_replace('_', ' ', $field));
        
        foreach ($ruleList as $rule) {
            $params = [];
            if (str_contains($rule, ':')) {
                [$rule, $paramString] = explode(':', $rule, 2);
                $params = explode(',', $paramString);
            }
            
            $error = match ($rule) {
                'required' => empty($value) ? "{$fieldLabel} wajib diisi" : null,
                'email' => !filter_var($value, FILTER_VALIDATE_EMAIL) ? "Format email tidak valid" : null,
                'min' => strlen($value) < (int)$params[0] ? "{$fieldLabel} minimal {$params[0]} karakter" : null,
                'max' => strlen($value) > (int)$params[0] ? "{$fieldLabel} maksimal {$params[0]} karakter" : null,
                'numeric' => !is_numeric($value) ? "{$fieldLabel} harus berupa angka" : null,
                'phone' => !preg_match('/^08[0-9]{8,12}$/', $value) ? "Format nomor telepon tidak valid" : null,
                'confirmed' => $value !== ($data["{$field}Confirm"] ?? null) ? "{$fieldLabel} tidak cocok" : null,
                default => null
            };
            
            if ($error) {
                $errors[$field] = $error;
                break;
            }
        }
    }
    
    return $errors;
}

// ============================================================================
// FORMAT HELPERS
// ============================================================================

/**
 * Format angka ke format Rupiah
 * 
 * @param int|float $amount Jumlah uang
 * @return string Format Rupiah
 */
function rupiah(int|float $amount): string
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal ke format Indonesia
 * 
 * @param string|DateTime $date Tanggal
 * @param string          $format Format output
 * @return string Tanggal terformat
 */
function tanggal(string|DateTime $date, string $format = 'long'): string
{
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    
    return match ($format) {
        'short' => $date->format('d') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y'),
        'long' => $hari[$date->format('l')] . ', ' . $date->format('d') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y'),
        'time' => $date->format('H:i'),
        'datetime' => $date->format('d') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y') . ' ' . $date->format('H:i'),
        default => $date->format('Y-m-d')
    };
}

/**
 * Format waktu relatif (time ago)
 * 
 * @param string|DateTime $datetime Waktu
 * @return string Waktu relatif
 */
function timeAgo(string|DateTime $datetime): string
{
    if (is_string($datetime)) {
        $datetime = new DateTime($datetime);
    }
    
    $now = new DateTime();
    $diff = $now->diff($datetime);
    
    if ($diff->y > 0) return $diff->y . ' tahun yang lalu';
    if ($diff->m > 0) return $diff->m . ' bulan yang lalu';
    if ($diff->d > 0) return $diff->d . ' hari yang lalu';
    if ($diff->h > 0) return $diff->h . ' jam yang lalu';
    if ($diff->i > 0) return $diff->i . ' menit yang lalu';
    return 'Baru saja';
}

/**
 * Generate slug dari string
 * 
 * @param string $text Text untuk dijadikan slug
 * @return string Slug
 */
function slugify(string $text): string
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ============================================================================
// JSON RESPONSE (untuk API)
// ============================================================================

/**
 * Kirim JSON response
 * 
 * @param mixed $data   Data untuk dikirim
 * @param int   $status HTTP status code
 */
function jsonResponse(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Kirim JSON error response
 * 
 * @param string $message Error message
 * @param int    $status  HTTP status code
 */
function jsonError(string $message, int $status = 400): void
{
    jsonResponse(['success' => false, 'message' => $message], $status);
}

/**
 * Kirim JSON success response
 * 
 * @param mixed  $data    Data
 * @param string $message Success message
 */
function jsonSuccess(mixed $data = null, string $message = 'Berhasil'): void
{
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}
