<?php

/**
 * ================================
 * APP HELPER
 * Utility umum untuk controller & model
 * ================================
 */

if (!function_exists('logWithTimestamp')) {
    function logWithTimestamp(...$args)
    {
        date_default_timezone_set('Asia/Jakarta');
        $timestamp = date("Y-m-d H:i:s");

        $message = '';
        $context = [];
        $logFile = __DIR__ . '/../../app/logs/app.log'; // default log file

        foreach ($args as $arg) {
            if (is_string($arg) && str_ends_with($arg, '.log')) {
                // kalau string dan diakhiri .log → ini path log file
                $logFile = $arg;
            } elseif (is_string($arg)) {
                // kalau string → anggap pesan
                $message .= ($message ? ' ' : '') . $arg;
            } elseif (is_array($arg)) {
                // kalau array → anggap context tambahan
                $context = array_merge($context, $arg);
            }
        }

        // gabung context jika ada
        if (!empty($context)) {
            $message .= ' | CONTEXT: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        // tulis ke file log
        error_log("[{$timestamp}] {$message}" . PHP_EOL, 3, $logFile);
    }
}

if (!function_exists('requireField')) {
    function requireField($field, $value, $message, $logFile)
    {
        if (empty($value)) {
            logWithTimestamp("ERROR: {$field} => {$message}", $logFile);
            throw new Exception($message);
        }
    }
}

if (!function_exists('responseJSON')) {
    function responseJSON($data = [], $status = 'success', $message = '', $httpCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($httpCode);

        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('logDebugTable')) {
    function logDebugTable($tag, $data = null)
    {
        date_default_timezone_set('Asia/Jakarta');
        $timestamp = date("Y-m-d H:i:s");
        $logFile = __DIR__ . "/../logs/debugtable_model.log";
        error_log("[$timestamp] [$tag]" . PHP_EOL, 3, $logFile);

        if ($data !== null) {
            if (is_string($data)) {
                error_log($data . PHP_EOL, 3, $logFile);
            } else {
                error_log(print_r($data, true) . PHP_EOL, 3, $logFile);
            }
        }
    }
}

if (!function_exists('insertLogHelper')) {
    function insertLogHelper($conn, array $data = [])
    {
        date_default_timezone_set('Asia/Jakarta');

        $user_id    = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $action     = mysqli_real_escape_string($conn, $data['action'] ?? '-');
        $table_name = mysqli_real_escape_string($conn, $data['table_name'] ?? '-');
        $record_id  = isset($data['record_id']) ? (int)$data['record_id'] : "NULL";
        $description = mysqli_real_escape_string($conn, $data['description'] ?? '-');
        $ip_address = mysqli_real_escape_string($conn, $data['ip_address'] ?? '0.0.0.0');

        $sql = "INSERT INTO logs (user_id, action, table_name, record_id, description, ip_address)
                VALUES ($user_id, '$action', '$table_name', $record_id, '$description', '$ip_address')";

        mysqli_query($conn, $sql);

        // ✅ Log ke file juga (autofallback)
        logWithTimestamp(
            "INSERT LOG EVENT",
            [
                'action'      => $action,
                'table_name'  => $table_name,
                'record_id'   => $record_id,
                'ip'          => $ip_address
            ]
        );
    }
}

if (!function_exists('generateKode')) {
    /**
     * Membuat kode unik berurutan berdasarkan prefix dan tabel tertentu.
     *
     * @param string $prefix   Prefix kode (misal: 'BK', 'USR', 'INV')
     * @param string $table    Nama tabel
     * @param string $field    Nama kolom kode di tabel
     * @param int    $padding  Jumlah digit angka di belakang prefix (default: 6)
     * @return string          Kode baru yang sudah terformat, misal: BK000123
     */
    function generateKode($prefix, $table, $field, $padding = 6)
    {
        // gunakan koneksi global dari config
        global $conn;

        if (!$conn) {
            throw new Exception("Koneksi database belum diinisialisasi (global \$conn tidak ditemukan).");
        }

        // Bersihkan nama tabel & kolom (basic whitelist)
        $table  = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $field  = preg_replace('/[^a-zA-Z0-9_]/', '', $field);

        // Query ambil kode terakhir
        $sql = "SELECT MAX($field) AS maxKode FROM $table";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Gagal mempersiapkan query: " . mysqli_error($conn));
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Ambil angka terakhir dari kode
        if ($row && !empty($row['maxKode'])) {
            $lastNumber = (int) substr($row['maxKode'], strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format hasil
        return $prefix . str_pad($nextNumber, $padding, "0", STR_PAD_LEFT);
    }
}

if (!function_exists('handleController')) {
    /**
     * Membungkus logika controller dengan try-catch dan logging standar
     *
     * @param callable $callback Logika controller
     * @param string $logFile File log
     * @param object $model Model yang dipakai (harus ada getConn() & getUserInput())
     */
    function handleController(callable $callback, string $logFile, $model)
    {
        $function = debug_backtrace()[1]['function'] ?? '(unknown)';
        try {
            logWithTimestamp("=== CONTROLLER START: $function ===", $logFile);

            $result = $callback();

            logWithTimestamp("✅ CONTROLLER SUCCESS: $function", $logFile);

            if (is_string($result)) echo $result;

            return $result;
            // } catch (Exception $e) {
            //     $errorMessage = $e->getMessage();

            //     // 1️⃣ Catat ke log file
            //     logWithTimestamp("❌ ERROR $function: $errorMessage", $logFile);

            //     // 2️⃣ Catat ke DB juga, fleksibel untuk semua aksi
            //     try {
            //         insertLogHelper($model->getConn(), [
            //             'user_id'     => $model->getUserInput(),
            //             'action'      => 'FAILED ' . strtoupper($function), // misal FAILED INSERTMENU
            //             'table_name'  => $_POST['table_name'] ?? '-',         // optional
            //             'record_id'   => $_POST['id'] ?? null,
            //             'description' => "❌ ERROR $function: $errorMessage",
            //             'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            //         ]);
            //     } catch (Throwable $logError) {
            //         logWithTimestamp("⚠️ GAGAL MENULIS LOG DB: " . $logError->getMessage(), $logFile);
            //     }

            //     responseJSON([], 'error', $errorMessage, 400);
            // } finally {
            //     logWithTimestamp("=== CONTROLLER FINISH: $function ===", $logFile);
            // }
        } catch (Exception $e) {

            $errorMessage = $e->getMessage();
            $logFile = $logFile ?? __DIR__ . '/../../app/logs/app.log';

            // 🔍 Cek apakah pesan exception adalah JSON
            $decoded = json_decode($errorMessage, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Jika berupa JSON → ambil datanya
                $msg       = $decoded['message'] ?? 'Unknown error';
                $tableName = $decoded['table'] ?? ($_POST['table_name'] ?? '-');
                $recordId  = $decoded['record'] ?? ($_POST['id'] ?? null);
            } else {
                // Jika bukan JSON → gunakan plain text
                $msg       = $errorMessage;
                $tableName = $_POST['table_name'] ?? '-';
                $recordId  = $_POST['id'] ?? null;
            }

            // 1️⃣ Catat ke log file (lebih jelas)
            logWithTimestamp("❌ ERROR $function: $msg", $logFile);

            // 2️⃣ Catat ke DB log
            try {
                insertLogHelper($model->getConn(), [
                    'user_id'     => $model->getUserInput(),
                    'action'      => 'FAILED ' . strtoupper($function),
                    'table_name'  => $tableName,
                    'record_id'   => $recordId,
                    'description' => "❌ ERROR $function: $msg",
                    'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);
            } catch (Throwable $logError) {
                logWithTimestamp("⚠️ GAGAL MENULIS LOG DB: " . $logError->getMessage(), $logFile);
            }

            // 3️⃣ Kirim response JSON ke client
            responseJSON([], 'error', $msg, 400);
        } finally {
            logWithTimestamp("=== CONTROLLER FINISH: $function ===", $logFile);
        }
    }
}

if (!function_exists('executeModelQuery')) {
    /**
     * Helper untuk menjalankan query insert/update/delete dengan logging
     * 
     * @param mysqli $conn Koneksi database
     * @param string $sql Query SQL dengan placeholder (?)
     * @param array $params Array parameter untuk bind_param
     * @param string $logFile File log
     * @param string|null $action Nama aksi (INSERT/UPDATE/DELETE) untuk log
     * @return mixed true/false atau insert_id jika INSERT
     */
    function executeModelQuery($conn, string $sql, array $params, string $logFile, string $action = null)
    {
        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error = $conn->error;
                logWithTimestamp("GAGAL menyiapkan statement: {$error}", $logFile);
                throw new Exception("Gagal menyiapkan query: {$error}");
            }

            if (!empty($params)) {
                // Dynamically bind params
                $types = array_shift($params); // jenis parameter: "ssiiss..."
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL eksekusi query: {$error}", $logFile);
                throw new Exception("Gagal eksekusi query: {$error}");
            }

            logWithTimestamp(($action ? "{$action} SUCCESS" : "Query SUCCESS"), $logFile);

            // Return insert_id jika INSERT, else true
            if (stripos($sql, 'insert') === 0) {
                $id = $stmt->insert_id;
                $stmt->close();
                return $id ?: true;
            }

            $stmt->close();
            return true;
        } catch (Exception $e) {
            logWithTimestamp("ERROR executeModelQuery: " . $e->getMessage(), $logFile);
            return false;
        }
    }
}

if (!function_exists('checkForeignKeys')) {
    /**
     * Cek apakah suatu record masih digunakan di tabel lain
     *
     * @param mysqli $conn        Koneksi database
     * @param int    $id          ID record yang mau dicek
     * @param array  $tables      Array format ['nama_tabel' => 'nama_kolom_foreign']
     * @return array|null         Kembalikan info tabel yang masih pakai record, null kalau aman
     */
    // function checkForeignKeys($id, $tables)
    // {
    //     global $conn;
    //     $usedIn = [];

    //     foreach ($tables as $table => $column) {
    //         $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
    //         $stmt->bind_param("i", $id);
    //         $stmt->execute();
    //         $stmt->bind_result($count);
    //         $stmt->fetch();
    //         $stmt->close();

    //         if ($count > 0) {
    //             $usedIn[] = $table;
    //         }
    //     }

    //     return !empty($usedIn) ? $usedIn : null;
    // }
    function checkForeignKeys(mysqli $conn, int $id, array $tables): ?array
    {
        $usedIn = [];

        foreach ($tables as $table => $column) {
            $sql = "SELECT COUNT(*) AS total FROM `$table` WHERE `$column` = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ((int)$count > 0) {
                $usedIn[] = $table;
            }
        }

        return $usedIn ?: null;
    }
    // Cara pakai

    // $tables = [
    //     'schedule_training'     => 'id_silabus',
    //     'kendala'               => 'id_silabus',
    //     'fasilitas'             => 'id_silabus',
    //     'persyaratan'           => 'id_silabus',
    //     'latar_belakang'        => 'id_silabus',
    //     'materi_pelatihan'      => 'id_silabus',
    //     'pretest'               => 'id_silabus',
    // ];

    // $id_silabus = 3213;

    // $check = checkForeignKeys($id_silabus, $tables);

    // if ($check) {
    //     echo "Tidak bisa hapus, masih ada data di tabel: " . implode(", ", $check);
    // } else {
    // Aman untuk delete
    // }
}

if (!function_exists('uploadFoto')) {
    /**
     * Upload & compress foto peserta
     *
     * @param array $file  $_FILES['foto']
     * @param string $kd_user
     * @param string|null $oldFoto
     * @param string $saveDir absolute path folder penyimpanan
     * @return string nama file baru
     * @throws Exception
     */
    function uploadFoto($file, $kd_user, $oldFoto = null, $saveDir = '')
    {
        if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload foto gagal atau file tidak ditemukan.");
        }

        // Cek folder
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        $tmp  = $file['tmp_name'];
        $mime = $file['type'];

        // Validasi mime
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed)) {
            throw new Exception("Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.");
        }

        // Hapus foto lama (kecuali default)
        if (!empty($oldFoto) && $oldFoto !== 'default_user.webp') {
            $oldPath = $saveDir . "/" . $oldFoto;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Nama file baru
        $newName = $kd_user . ".webp";
        $output  = $saveDir . "/" . $newName;

        // --- RESIZE LOGIC ---
        list($width, $height) = getimagesize($tmp);
        $maxDim = 800;

        $newW = $width;
        $newH = $height;

        if ($width > $maxDim || $height > $maxDim) {
            if ($width > $height) {
                $newW = $maxDim;
                $newH = intval($height * $maxDim / $width);
            } else {
                $newH = $maxDim;
                $newW = intval($width * $maxDim / $height);
            }
        }

        // Create canvas baru
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = imagecreatefromjpeg($tmp);
                break;
            case 'image/png':
                $src = imagecreatefrompng($tmp);
                imagealphablending($src, true);
                imagesavealpha($src, true);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($tmp);
                break;
        }

        $canvas = imagecreatetruecolor($newW, $newH);

        // buat transparan utk PNG/WebP
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);

        // Simpan ke WebP (hasil resize)
        if (!imagewebp($canvas, $output, 80)) {
            throw new Exception("Gagal menyimpan foto.");
        }

        imagedestroy($src);
        imagedestroy($canvas);

        // --- OPSIONAL: compress via helper kamu ---
        if (function_exists('compressImage')) {
            compressImage($output, $output, 80);
        }

        return $newName;
    }
}

if (!function_exists('compressImage')) {
    function compressImage($source, $destination, $quality = 80)
    {
        $info = getimagesize($source);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                // agar background transparan tidak hilang
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($source);
                break;
            default:
                return false; // format tidak didukung
        }

        // Simpan dalam format WEBP
        $result = imagewebp($image, $destination, $quality);
        imagedestroy($image);

        return $result;
    }
}
// ======================== cara penggunaan
// logWithTimestampPage("Error saat delete", ['id' => $id], ['_page' => 'Perusahaan']);
// logWithTimestamp("Debug ajaxList", ['_page'=>'User']);
// logDebugTable("DEBUG", $data, 'Perusahaan');
// requireField('nama', $nama, 'Nama wajib diisi', null, 'Perusahaan');
