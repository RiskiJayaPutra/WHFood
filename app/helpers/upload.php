<?php

/**
 * ============================================================================
 * WHFood - Upload Helper
 * ============================================================================
 * 
 * Fungsi-fungsi untuk handling upload file (gambar).
 * 
 * @package     WHFood
 * @subpackage  Helpers
 * @author      WHFood Development Team
 * @version     1.0.0
 */

declare(strict_types=1);

// ============================================================================
// CONSTANTS
// ============================================================================

define('UPLOAD_DIR', dirname(__DIR__, 2) . '/public/assets/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ============================================================================
// UPLOAD FUNCTIONS
// ============================================================================

/**
 * Upload single file
 * 
 * @param array  $file      File dari $_FILES
 * @param string $folder    Subfolder tujuan (contoh: 'products', 'profiles')
 * @param string $prefix    Prefix nama file
 * @return array ['success' => bool, 'message' => string, 'path' => string|null]
 */
function uploadFile(array $file, string $folder = 'misc', string $prefix = ''): array
{
    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas server)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
        ];
        
        $message = $errorMessages[$file['error']] ?? 'Upload gagal';
        return ['success' => false, 'message' => $message, 'path' => null];
    }
    
    // Cek ukuran file
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB', 'path' => null];
    }
    
    // Cek MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, atau WebP', 'path' => null];
    }
    
    // Cek extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Extensi file tidak diizinkan', 'path' => null];
    }
    
    // Buat folder jika belum ada
    $uploadPath = UPLOAD_DIR . '/' . $folder;
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Generate unique filename
    $newFilename = ($prefix ? $prefix . '_' : '') . uniqid() . '_' . time() . '.' . $extension;
    $fullPath = $uploadPath . '/' . $newFilename;
    
    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        return ['success' => false, 'message' => 'Gagal menyimpan file', 'path' => null];
    }
    
    // Return relative path (untuk database)
    $relativePath = "assets/uploads/{$folder}/{$newFilename}";
    
    return ['success' => true, 'message' => 'Upload berhasil', 'path' => $relativePath];
}

/**
 * Upload multiple files
 * 
 * @param array  $files    Files dari $_FILES (sudah di-normalize)
 * @param string $folder   Subfolder tujuan
 * @param string $prefix   Prefix nama file
 * @param int    $maxFiles Maksimal jumlah file
 * @return array ['success' => bool, 'message' => string, 'paths' => array]
 */
function uploadMultipleFiles(array $files, string $folder = 'misc', string $prefix = '', int $maxFiles = 5): array
{
    // Normalize files array jika dari single input multiple
    $normalized = normalizeFilesArray($files);
    
    if (count($normalized) > $maxFiles) {
        return ['success' => false, 'message' => "Maksimal {$maxFiles} file", 'paths' => []];
    }
    
    $paths = [];
    $errors = [];
    
    foreach ($normalized as $index => $file) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        
        $result = uploadFile($file, $folder, $prefix . '_' . ($index + 1));
        
        if ($result['success']) {
            $paths[] = $result['path'];
        } else {
            $errors[] = "File " . ($index + 1) . ": " . $result['message'];
        }
    }
    
    if (empty($paths) && !empty($errors)) {
        return ['success' => false, 'message' => implode(', ', $errors), 'paths' => []];
    }
    
    return [
        'success' => true, 
        'message' => count($paths) . ' file berhasil diupload', 
        'paths' => $paths
    ];
}

/**
 * Normalize $_FILES array untuk multiple upload
 * 
 * @param array $files Raw $_FILES array
 * @return array Normalized array
 */
function normalizeFilesArray(array $files): array
{
    $normalized = [];
    
    // Cek apakah sudah dalam format normal
    if (isset($files['name']) && is_array($files['name'])) {
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $normalized[] = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
        }
    } else {
        $normalized[] = $files;
    }
    
    return $normalized;
}

/**
 * Hapus file dari storage
 * 
 * @param string $path Path relatif file
 * @return bool True jika berhasil
 */
function deleteFile(string $path): bool
{
    $fullPath = dirname(__DIR__, 2) . '/public/' . $path;
    
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Resize gambar (optional, untuk menghemat storage)
 * 
 * @param string $path      Path file asli
 * @param int    $maxWidth  Lebar maksimal
 * @param int    $maxHeight Tinggi maksimal
 * @param int    $quality   Kualitas output (1-100)
 * @return bool True jika berhasil
 */
function resizeImage(string $path, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 85): bool
{
    $fullPath = dirname(__DIR__, 2) . '/public/' . $path;
    
    if (!file_exists($fullPath)) {
        return false;
    }
    
    $info = getimagesize($fullPath);
    if (!$info) {
        return false;
    }
    
    [$width, $height] = $info;
    $mimeType = $info['mime'];
    
    // Jika ukuran sudah cukup kecil, skip
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return true;
    }
    
    // Hitung dimensi baru
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Buat image resource
    $source = match ($mimeType) {
        'image/jpeg' => imagecreatefromjpeg($fullPath),
        'image/png' => imagecreatefrompng($fullPath),
        'image/webp' => imagecreatefromwebp($fullPath),
        'image/gif' => imagecreatefromgif($fullPath),
        default => null
    };
    
    if (!$source) {
        return false;
    }
    
    // Buat canvas baru
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency untuk PNG dan GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Simpan
    $result = match ($mimeType) {
        'image/jpeg' => imagejpeg($destination, $fullPath, $quality),
        'image/png' => imagepng($destination, $fullPath, (int)(9 - ($quality * 0.09))),
        'image/webp' => imagewebp($destination, $fullPath, $quality),
        'image/gif' => imagegif($destination, $fullPath),
        default => false
    };
    
    imagedestroy($source);
    imagedestroy($destination);
    
    return $result;
}

/**
 * Get URL lengkap untuk file upload
 * 
 * @param string|null $path Path relatif atau null
 * @param string      $default Default image jika null
 * @return string Full URL
 */
function uploadUrl(?string $path, string $default = ''): string
{
    if (empty($path)) {
        return $default ?: 'https://via.placeholder.com/400x400?text=No+Image';
    }
    
    // Jika sudah URL lengkap, return as-is
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    
    return url($path);
}
