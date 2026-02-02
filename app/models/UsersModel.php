<?php
require_once __DIR__ . '/../helpers/app_helper.php';
class UsersModel
{
    protected $conn;
    protected $table = "users";
    protected $user_input;
    protected $logFile;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->user_input = $_SESSION['user_id'] ?? 0;
        $this->logFile = __DIR__ . '/../logs/debug_model.log';
    }

    public function getConn()
    {
        return $this->conn;
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function getUserInput()
    {
        return $this->user_input;
    }

    // ======================================
    // Fungsi insert halaman staff 
    // ======================================
    public function checkStaffExist($datainput)
    {
        $sql = "SELECT a.nama_lengkap as nama_lengkap,
                        b.nm_perusahaan as nama_perusahaan, 
                        a.bagian as bagian 
                FROM staff as a  INNER JOIN perusahaan as b ON a.per_id = b.id 
                WHERE a.nama_Lengkap = ? AND a.per_id=? AND a.bagian = ? AND a.is_deleted = 0 AND b.is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $datainput['nama_lengkap'], $datainput['perusahaan'], $datainput['bagian']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data;
    }

    // ========================== tahan
    public function findUserByemail($email)
    {
        $sql = "SELECT 
                u.id,
                u.email,
                c.customer_name,
                o.organization_name
            FROM users u
            INNER JOIN customers c 
                ON u.customer_id = c.id 
                AND c.is_active = 1
            INNER JOIN organizations o 
                ON c.organization_id = o.id 
                AND o.is_active = 1
            WHERE u.email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc(); // ⬅ ambil satu record saja
        return $data; // ⬅ langsung return associative array
    }
    // ========================== tahan
    public function insertstaff($datainput)
    {
        // 🔹 Hash password
        $sql = "INSERT INTO staff
                (kd_staff, 
                nama_lengkap, 
                nama_panggilan, 
                alamat, 
                per_id, 
                bagian, 
                prov_id, 
                kota_id, 
                kec_id, 
                desa_id,
                kodepos, 
                tempat_lahir, 
                tanggal_lahir, 
                no_telp, 
                no_handphone, 
                email, 
                foto,
                tgl_input, 
                user_input, 
                user_level,
                status_kepegawaian) 
                VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $error = $this->conn->error;
            logWithTimestamp("GAGAL menyiapkan statement INSERT: {$error}", $this->logFile);
            throw new Exception("Gagal menyiapkan query: {$error}");
        }

        // 🔹 Binding parameter
        $stmt->bind_param(
            "ssssissiiiissssssiss",
            $datainput['kd_staff'],         // s
            $datainput['nama_lengkap'],     // s
            $datainput['nama_panggilan'],   // s
            $datainput['alamat'],           // s
            $datainput['per_id'],           // i 
            $datainput['bagian'],           // s
            $datainput['prov_id'],          // i
            $datainput['kota_id'],          // i
            $datainput['kec_id'],           // i
            $datainput['desa_id'],          // i
            $datainput['kodepos'],          // s
            $datainput['tempat_lahir'],     // s
            $datainput['tanggal_lahir'],    // s
            $datainput['no_telp'],          // s
            $datainput['no_handphone'],     // s
            $datainput['email'],            // s
            $datainput['foto'],             // s
            $this->user_input,              // s
            $datainput['user_level'], // s
            $datainput['status_kepegawaian'] // s
        );

        // 🔹 Eksekusi query
        if (!$stmt->execute()) {
            $error = $stmt->error;
            logWithTimestamp("GAGAL eksekusi INSERT: {$error}", $this->logFile);
            throw new Exception("Gagal menyimpan data user: {$error}");
        }
        $insertedId = $this->conn->insert_id;
        $stmt->close();
        return $insertedId;
    }

    public function insertusers($datainput)
    {
        $sql = "INSERT INTO users
                (kd_user, 
                staff_id, 
                email, 
                foto, 
                username, 
                password, 
                tgl_input, 
                user_input, 
                user_level,
                status_kepegawaian) 
                VALUES ( ?, ?, ?, ?, ?, ?, now(), ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $error = $this->conn->error;
            logWithTimestamp("GAGAL menyiapkan statement INSERT: {$error}", $this->logFile);
            throw new Exception("Gagal menyiapkan query: {$error}");
        }

        // 🔹 Binding parameter
        $stmt->bind_param(
            "sissssiss",
            $datainput['kd_user'],         // s
            $datainput['staff_id'],     // s
            $datainput['email'],   // s
            $datainput['foto'],           // s
            $datainput['username'],           // i 
            $datainput['password'],           // s
            $this->user_input,              // s
            $datainput['user_level'], // s
            $datainput['status_kepegawaian'] // s
        );

        // 🔹 Eksekusi query
        if (!$stmt->execute()) {
            $error = $stmt->error;
            logWithTimestamp("GAGAL eksekusi INSERT: {$error}", $this->logFile);
            throw new Exception("Gagal menyimpan data user: {$error}");
        }
        $insertedId = $this->conn->insert_id;
        $stmt->close();
        return $insertedId;
    }
    // ======================================
    // Fungsi update halaman staff 
    // ======================================
    public function cekpassword($password, $id)
    {
        logWithTimestamp("=== MODEL cekpassword({$id}) START ===", $this->logFile);

        try {
            if (!$id) {
                throw new Exception("ID user tidak ditemukan.");
            }

            $sql = "SELECT password FROM {$this->table} WHERE id = ? AND is_deleted = 0";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $error = $this->conn->error;
                logWithTimestamp("GAGAL menyiapkan statement SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal menyiapkan query: {$error}");
            }

            // 🔹 Binding parameter
            if (!$stmt->bind_param("i", $id)) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL bind_param SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal bind parameter: {$error}");
            }

            // 🔹 Eksekusi query
            if (!$stmt->execute()) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL eksekusi SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal mengambil data password: {$error}");
            }

            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $hashedPassword = $row['password'];
                $stmt->close();

                if (password_verify($password, $hashedPassword)) {
                    logWithTimestamp("SUCCESS: Password cocok untuk ID {$id}", $this->logFile);
                    return true;
                } else {
                    logWithTimestamp("WARNING: Password salah untuk ID {$id}", $this->logFile);
                    return false;
                }
            } else {
                logWithTimestamp("WARNING: User ID {$id} tidak ditemukan", $this->logFile);
                return false;
            }
        } catch (Exception $e) {
            logWithTimestamp("ERROR cekpassword({$id}): " . $e->getMessage(), $this->logFile);
            return false;
        } finally {
            logWithTimestamp("=== MODEL cekpassword({$id}) FINISH ===", $this->logFile);
        }
    }

    public function cekkd_staff($id)
    {
        // return responseJSON([], 'error', "Gagal update data staff" . $id);
        $sql = "SELECT s.kd_staff FROM staff AS s INNER JOIN users AS u on s.id=u.staff_id where u.id = ? AND s.is_deleted = 0 AND u.is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $kd_staff = $row['kd_staff'];
            return $kd_staff;
        }
        return null;
    }

    public function updateStaff($dataupdate)
    {
        // Base SQL
        $sql = "UPDATE staff s
                INNER JOIN users u ON s.id = u.staff_id SET 
                         s.nama_lengkap = ?,
                         s.nama_panggilan = ?,
                         s.per_id = ?,
                         s.alamat = ?,
                         s.bagian = ?,
                         s.prov_id = ?,
                         s.kota_id = ?,
                         s.kec_id = ?,
                         s.desa_id = ?,
                         s.kodepos = ?,
                         s.tempat_lahir = ?,
                         s.tanggal_lahir = ?,
                         s.no_telp = ?,
                         s.no_handphone = ?,
                         s.email = ?,
                         s.status_kepegawaian = ?,
                         s.user_update = ?,
                         s.tgl_update = NOW()";
        // $sql = "UPDATE staff SET
        //                 nama_lengkap = ?,
        //                 nama_panggilan = ?,
        //                 per_id = ?,
        //                 alamat = ?,
        //                 bagian = ?,
        //                 prov_id = ?,
        //                 kota_id = ?,
        //                 kec_id = ?,
        //                 desa_id = ?,
        //                 kodepos = ?,
        //                 tempat_lahir = ?,
        //                 tanggal_lahir = ?,
        //                 no_telp = ?,
        //                 no_handphone = ?,
        //                 email = ?,
        //                 status_kepegawaian = ?,
        //                 user_update = ?,
        //                 tgl_update = NOW()";

        $params = [
            $dataupdate['nama_lengkap'],
            $dataupdate['nama_panggilan'],
            $dataupdate['per_id'],
            $dataupdate['alamat'],
            $dataupdate['bagian'],
            $dataupdate['prov_id'],
            $dataupdate['kota_id'],
            $dataupdate['kec_id'],
            $dataupdate['desa_id'],
            $dataupdate['kodepos'],
            $dataupdate['tempat_lahir'],
            $dataupdate['tanggal_lahir'],
            $dataupdate['no_telp'],
            $dataupdate['no_handphone'],
            $dataupdate['email'],
            $dataupdate['status_kepegawaian'],
            $this->user_input
        ];
        $types = "ssissiiiissssssss"; // tanpa foto dulu

        // 🔹 Kalau user upload foto → tambahkan ke query
        if (!empty($dataupdate['foto'])) {
            $sql .= ", s.foto = ?";
            $params[] = $dataupdate['foto'];
            $types .= "s";
        }

        // Final WHERE
        $sql .= " WHERE u.id = ? AND u.is_deleted = 0 AND s.is_deleted = 0";
        $params[] = $dataupdate['id'];
        $types .= "i";

        // Prepare
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'error' => $this->conn->error
            ];
        }

        // Bind all parameters dinamis
        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) return false;

        $affected = $stmt->affected_rows;
        $stmt->close();
        // return responseJSON([], 'error', "Gagal update data staff" . json_encode($dataupdate) . $sql . json_encode($params) . $dataupdate['id'] . ' + ' . $affected);
        return $affected;
    }

    public function updateusers($dataupdate)
    {
        // . $sql . json_encode($params) . $dataupdate['id'] . ' + ' . $affected);
        $sql = "UPDATE users SET
                email = ?,
                username = ?,
                status_kepegawaian = ?,
                user_update = ?,
                tgl_update = NOW()
            WHERE id = ? AND is_deleted = 0";
        // return responseJSON([], 'error', "Gagal update data staff" . json_encode($dataupdate) . $sql);
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'error'   => $this->conn->error
            ];
        }

        $stmt->bind_param(
            "sssii",
            $dataupdate['email'],
            $dataupdate['username'],
            $dataupdate['status_kepegawaian'],
            $this->user_input,
            $dataupdate['id'],
        );
        if (!$stmt->execute()) {
            return false;
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected;
    }

    public function findUserByid($id)
    {
        $sql = "SELECT * FROM users as a INNER JOIN staff as b ON a.staff_id = b.id WHERE a.id = ? and a.is_deleted = 0 AND b.is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc(); // ⬅ ambil satu record saja
        return $data; // ⬅ langsung return associative array
    }
    // ======================================
    // Fungsi delete halaman staff 
    // ======================================
    public function deleteStaff($id)
    {
        $sql = "UPDATE staff s
                INNER JOIN users u ON s.id = u.staff_id
                SET 
                    s.is_deleted = 1,
                    s.deleted_at = NOW(),
                    s.deleted_by = ?,
                    u.is_deleted = 1,
                    u.deleted_at = NOW(),
                    u.deleted_by = ?
                WHERE u.id = ? AND u.is_deleted = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $this->user_input, $this->user_input, $id);
        return $stmt->execute();
    }

    public function resetpass($id, $newpass)
    {
        logWithTimestamp("=== MODEL resetpass() START ===", $this->logFile);

        try {
            // Hash password baru
            $passwordHash = password_hash($newpass, PASSWORD_BCRYPT);

            // Siapkan query
            $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Gagal menyiapkan statement: " . $this->conn->error);
            }

            // Binding parameter
            $stmt->bind_param("si", $passwordHash, $id);

            // Eksekusi query
            if (!$stmt->execute()) {
                throw new Exception("Gagal eksekusi query: " . $stmt->error);
            }

            $stmt->close();
            logWithTimestamp("SUCCESS: Password user ID {$id} berhasil diperbarui.", $this->logFile);

            logWithTimestamp("=== MODEL resetpass() FINISH ===", $this->logFile);
            return true;
        } catch (Exception $e) {
            logWithTimestamp("ERROR MODEL resetpass(): " . $e->getMessage(), $this->logFile);
            throw $e; // biar ditangani controller
        }
    }

    public function cekUserName($user_name)
    {
        $sql = "SELECT COUNT(*) AS jml FROM users WHERE username = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_name]);
        $row = $stmt->fetch();

        return ($row['jml'] > 0); // TRUE kalau ada
    }

    public function getNamaUserlevel($level)
    {
        $sql = "SELECT id,nama FROM user_level WHERE id <> $level ORDER BY id";
        $result = $this->conn->query($sql);

        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getuser_levelbyid_peserta($idpeserta)
    {
        $sql = "SELECT us.id as cust_id, ul.nama as user_level FROM peserta as p 
                INNER JOIN customer as c ON p.cust_id = c.id
                INNER JOIN users as us ON us.cust_id = c.id
                INNER JOIN user_level as ul on c.user_level = ul.id
                WHERE p.id = ? and p.is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idpeserta);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc(); // ⬅ ambil satu record saja
        return $data; // ⬅ langsung return associative array   
    }

    public function getDataTable($request)
    {
        // logDebugtable("=== DEBUG ajaxList USERS START === ", $request);

        $columns = [
            'id',
            'nama_lengkap',
            'nama_perusahaan',
            'bagian',
            'alamat',
            'provinsi',
            'kota',
            'kecamatan',
            'desa',
            'kodepos',
            'tempat_lahir',
            'tanggal_lahir',
            'email',
            'no_telp',
            'no_hp',
            'foto',
            'level',
            'status_kepegawaian',
            'ket_update'
        ];

        // Base FROM query
        $baseQuery = "FROM `staff` as s INNER JOIN `users` AS a on s.id = a.staff_id
                        LEFT JOIN perusahaan AS b ON s.per_id = b.id
                        LEFT JOIN provinsi AS c ON s.prov_id = c.id
                        LEFT JOIN kota_kab AS d ON s.kota_id = d.id
                        LEFT JOIN kecamatan AS e ON s.kec_id = e.id
                        LEFT JOIN desa AS f ON s.desa_id = f.id
                        INNER JOIN user_level AS g ON a.user_level = g.id";

        // Default WHERE — gunakan is_deleted (sesuai DB kamu)
        $where = " WHERE a.is_deleted = 0 AND s.is_deleted = 0 AND a.id<> 1 ";

        // Search
        if (!empty($request['search']['value'])) {
            $search = $this->conn->real_escape_string($request['search']['value']);
            $where .= " AND (
                s.nama_lengkap LIKE '%{$search}%'
            OR s.nama_panggilan LIKE '%{$search}%'
            OR b.nm_perusahaan LIKE '%{$search}%'
            OR s.bagian LIKE '%{$search}%'
            OR s.alamat LIKE '%{$search}%'
            OR c.nama_prov LIKE '%{$search}%'
            OR d.nama_kota LIKE '%{$search}%'
            OR e.nama_kec LIKE '%{$search}%'
            OR f.nama_desa LIKE '%{$search}%'
            OR s.kodepos LIKE '%{$search}%'
            OR a.username LIKE '%{$search}%'
            OR g.nama LIKE '%{$search}%')";
        }

        // Main SELECT
        $sql = "SELECT a.id AS id,
                s.nama_lengkap AS nama_lengkap,
                s.nama_panggilan AS nama_panggilan,
                s.per_id AS id_perusahaan,
                b.nm_perusahaan AS nama_perusahaan,
                s.bagian AS bagian,
                s.alamat AS alamat,
                c.id AS id_provinsi,
                c.nama_prov AS provinsi,
                d.id AS id_kota,
                d.nama_kota AS kota,
                e.id AS id_kecamatan,
                e.nama_kec AS kecamatan,
                f.id AS id_kelurahan,
                f.nama_desa AS desa,
                s.kodepos AS kodepos,
                s.tempat_lahir AS tempat_lahir,
                DATE_FORMAT(s.tanggal_lahir, '%d %M %Y') AS tanggal_lahir,
                s.tanggal_lahir AS tanggal_lahir2,
                s.email AS email,
                s.no_telp AS no_telp,
                s.no_handphone AS no_hp,
                s.foto AS foto,
                a.username AS username,
                g.nama AS level,
                a.user_level AS level_kd,
                s.status_kepegawaian AS status_kepegawaian,
                s.ket_update AS ket_update
            $baseQuery
            $where";

        // Ordering (validasi index & direction)
        if (isset($request['order'][0])) {
            $colIndex = intval($request['order'][0]['column']);
            $dir = strtoupper($request['order'][0]['dir'] ?? 'ASC');
            $dir = ($dir === 'DESC') ? 'DESC' : 'ASC';
            if (isset($columns[$colIndex])) {
                if ($columns[$colIndex] === 'id') {
                    $dir = 'DESC';
                }
                $sql .= " ORDER BY {$columns[$colIndex]} $dir";
            } else {
                $sql .= " ORDER BY a.id DESC";
            }
        } else {
            $sql .= " ORDER BY a.id DESC";
        }

        // Paging
        $start = isset($request['start']) ? intval($request['start']) : 0;
        $length = isset($request['length']) ? intval($request['length']) : 10;
        if ($length > 0) {
            $sqlLimit = $sql . " LIMIT $start, $length";
        } else {
            $sqlLimit = $sql; // no limit
        }

        $result = $this->conn->query($sqlLimit);
        if (!$result) {
            logDebugtable("SQL Error", [
                'error' => $this->conn->error,
                'query' => $sqlLimit
            ]);
            return [
                "draw" => intval($request['draw'] ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ];
        }

        // Build Data rows
        $data = [];
        $no = $start + 1;
        while ($row = $result->fetch_assoc()) {
            $row['no'] = $no++;
            $row['aksi'] = "<button class='edit' data-id='{$row['id']}' 
                                                data-nama='{$row['nama_lengkap']}'
                                                data-panggilan='{$row['nama_panggilan']}'
                                                data-alamat='{$row['alamat']}'
                                                data-perusahaan='{$row['id_perusahaan']}'
                                                data-bagian='{$row['bagian']}'
                                                data-provinsi='{$row['id_provinsi']}'
                                                data-kota='{$row['id_kota']}'
                                                data-kecamatan='{$row['id_kecamatan']}'
                                                data-kelurahan='{$row['id_kelurahan']}'
                                                data-kodepos='{$row['kodepos']}'
                                                data-tempatlahir='{$row['tempat_lahir']}'
                                                data-tanggallahir='{$row['tanggal_lahir2']}'
                                                data-telephone='{$row['no_telp']}'
                                                data-handphone='{$row['no_hp']}'
                                                data-email='{$row['email']}'
                                                data-username='{$row['username']}'
                                                data-foto='{$row['foto']}'
                                                data-status_kepegawaian='{$row['status_kepegawaian']}'
                                                data-status='{$row['level_kd']}'>Edit</button>
                        <button class='delete' data-id='{$row['id']}' data-nama_lengkap='{$row['nama_lengkap']}'>Delete</button>";
            $data[] = $row;
        }

        // Count total records (without search)
        $totalQuery = "SELECT COUNT(*) AS total $baseQuery WHERE a.is_deleted = 0 AND s.is_deleted = 0";
        // logDebugtable("[SQL COUNT TOTAL]", $totalQuery);
        $qTotal = $this->conn->query($totalQuery);
        $total = $qTotal ? (int) $qTotal->fetch_assoc()['total'] : 0;

        // Count filtered records (with search)
        $filteredQuery = "SELECT COUNT(*) AS total $baseQuery $where";
        // logDebugtable("[SQL COUNT FILTERED]", $filteredQuery);
        $qFiltered = $this->conn->query($filteredQuery);
        $totalFiltered = $qFiltered ? (int) $qFiltered->fetch_assoc()['total'] : $total;

        return [
            "draw" => intval($request['draw'] ?? 0),
            "recordsTotal" => $total,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ];
    }
    // ==================================================================================

    // ======================================
    // Fungsi delete halaman staff 
    // ======================================

    public function checkCustomerExist($datainput)
    {
        $sql = "SELECT a.nama_lengkap as nama_lengkap,
                        b.nm_perusahaan as nama_perusahaan, 
                        a.bagian as bagian 
                FROM customer as a  INNER JOIN perusahaan as b ON a.per_id = b.id 
                WHERE a.nama_Lengkap = ? AND a.per_id=? AND a.bagian = ? AND a.is_deleted = 0 AND b.is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $datainput['nama_lengkap'], $datainput['perusahaan'], $datainput['bagian']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        // responseJSON([], 'error', 'masuk' . json_encode($datainput) . ' + ' . $data);
        return $data;
    }

    public function insertcustomer($datainput)
    {
        // 🔹 Hash password
        $sql = "INSERT INTO customer
                (kd_staff, 
                nama_lengkap, 
                nama_panggilan, 
                alamat, 
                per_id, 
                bagian, 
                prov_id, 
                kota_id, 
                kec_id, 
                desa_id,
                kodepos, 
                tempat_lahir, 
                tanggal_lahir, 
                no_telp, 
                no_handphone, 
                email, 
                foto,
                tgl_input, 
                user_input, 
                user_level,
                marketing_id) 
                VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $error = $this->conn->error;
            logWithTimestamp("GAGAL menyiapkan statement INSERT: {$error}", $this->logFile);
            throw new Exception("Gagal menyiapkan query: {$error}");
        }

        // 🔹 Binding parameter
        $stmt->bind_param(
            "ssssissiiiissssssisi",
            $datainput['kd_staff'],         // s
            $datainput['nama_lengkap'],     // s
            $datainput['nama_panggilan'],   // s
            $datainput['alamat'],           // s
            $datainput['per_id'],           // i 
            $datainput['bagian'],           // s
            $datainput['prov_id'],          // i
            $datainput['kota_id'],          // i
            $datainput['kec_id'],           // i
            $datainput['desa_id'],          // i
            $datainput['kodepos'],          // s
            $datainput['tempat_lahir'],     // s
            $datainput['tanggal_lahir'],    // s
            $datainput['no_telp'],          // s
            $datainput['no_handphone'],     // s
            $datainput['email'],            // s
            $datainput['foto'],             // s
            $this->user_input,              // s
            $datainput['user_level'], // s
            $datainput['marketing_id'] // s
        );

        // 🔹 Eksekusi query
        if (!$stmt->execute()) {
            $error = $stmt->error;
            logWithTimestamp("GAGAL eksekusi INSERT: {$error}", $this->logFile);
            throw new Exception("Gagal menyimpan data user: {$error}");
        }
        $insertedId = $this->conn->insert_id;
        $stmt->close();
        return $insertedId;
    }

    public function getDataTable_customer($request)
    {
        // logDebugtable("=== DEBUG ajaxList USERS START === ", $request);

        $columns = [
            'id',
            'nama_lengkap',
            'nama_perusahaan',
            'bagian',
            'status',
            'alamat',
            'provinsi',
            'kota',
            'kecamatan',
            'desa',
            'kodepos',
            'tempat_lahir',
            'tanggal_lahir',
            'email',
            'no_telp',
            'no_hp',
            'foto',
            'level',
            'nama_marketing',
            'ket_update'
        ];

        // Base FROM query
        $baseQuery = "FROM `customer` as cust INNER join `users` AS a on cust.id = a.cust_id
            LEFT JOIN perusahaan AS b ON cust.per_id = b.id
            LEFT JOIN provinsi AS c ON cust.prov_id = c.id
            LEFT JOIN kota_kab AS d ON cust.kota_id = d.id
            LEFT JOIN kecamatan AS e ON cust.kec_id = e.id
            LEFT JOIN desa AS f ON cust.desa_id = f.id
            INNER JOIN user_level AS g ON a.user_level = g.id
            LEFT JOIN staff AS s ON cust.marketing_id = s.id
            ";

        // Default WHERE — gunakan is_deleted (sesuai DB kamu)
        $where = " WHERE a.is_deleted = 0 AND cust.is_deleted = 0 AND a.id<> 1 ";

        // Search
        if (!empty($request['search']['value'])) {
            $search = $this->conn->real_escape_string($request['search']['value']);
            $where .= " AND (
                cust.nama_lengkap LIKE '%{$search}%'
            OR cust.nama_panggilan LIKE '%{$search}%'
            OR b.nm_perusahaan LIKE '%{$search}%'
            OR cust.bagian LIKE '%{$search}%'
            OR cust.alamat LIKE '%{$search}%'
            OR c.nama_prov LIKE '%{$search}%'
            OR d.nama_kota LIKE '%{$search}%'
            OR e.nama_kec LIKE '%{$search}%'
            OR f.nama_desa LIKE '%{$search}%'
            OR cust.kodepos LIKE '%{$search}%'
            OR a.username LIKE '%{$search}%'
            OR g.nama LIKE '%{$search}%'
        )";
        }

        $sql = "SELECT 
        a.id AS id,
        cust.nama_lengkap AS nama_lengkap,
        cust.nama_panggilan AS nama_panggilan,
        cust.per_id AS id_perusahaan,
        b.nm_perusahaan AS nama_perusahaan,
        cust.bagian AS bagian,
        cust.alamat AS alamat,
        c.id AS id_provinsi,
        c.nama_prov AS provinsi,
        d.id AS id_kota,
        d.nama_kota AS kota,
        e.id AS id_kecamatan,
        e.nama_kec AS kecamatan,
        f.id AS id_kelurahan,
        f.nama_desa AS desa,
        cust.kodepos AS kodepos,
        cust.tempat_lahir AS tempat_lahir,
        DATE_FORMAT(cust.tanggal_lahir, '%d %M %Y') AS tanggal_lahir,
        cust.tanggal_lahir AS tanggal_lahir2,
        cust.email AS email,
        cust.no_telp AS no_telp,

        -- 🔥 STATUS BARU DI SINI
        CASE 
            WHEN cust.status = 'pic' 
                 AND (SELECT COUNT(*) FROM peserta p WHERE p.cust_id = cust.id AND p.is_deleted = 0) > 0
            THEN 'PIC + peserta'
            ELSE cust.status
        END AS status,

        cust.marketing_id AS marketing_id,
        s.nama_lengkap AS nama_marketing,
        cust.no_handphone AS no_hp,
        cust.foto AS foto,
        a.username AS username,
        g.nama AS level,
        a.user_level AS level_kd,
        cust.ket_update AS ket_update
        $baseQuery
        $where";

        if (isset($request['order'][0])) {
            $colIndex = intval($request['order'][0]['column']);
            $dir = strtoupper($request['order'][0]['dir'] ?? 'ASC');
            $dir = ($dir === 'DESC') ? 'DESC' : 'ASC';
            if (isset($columns[$colIndex])) {
                if ($columns[$colIndex] === 'id') {
                    $dir = 'DESC';
                }
                $sql .= " ORDER BY {$columns[$colIndex]} $dir";
            } else {
                $sql .= " ORDER BY a.id DESC";
            }
        } else {
            $sql .= " ORDER BY a.id DESC";
        }

        $start = isset($request['start']) ? intval($request['start']) : 0;
        $length = isset($request['length']) ? intval($request['length']) : 10;
        if ($length > 0) {
            $sqlLimit = $sql . " LIMIT $start, $length";
        } else {
            $sqlLimit = $sql; // no limit
        }

        $result = $this->conn->query($sqlLimit);
        if (!$result) {
            logDebugtable("SQL Error", [
                'error' => $this->conn->error,
                'query' => $sqlLimit
            ]);
            return [
                "draw" => intval($request['draw'] ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ];
        }

        $data = [];
        $no = $start + 1;
        while ($row = $result->fetch_assoc()) {
            $row['no'] = $no++;
            $row['aksi'] = "<button class='edit' data-id='{$row['id']}' 
                                                data-nama='{$row['nama_lengkap']}'
                                                data-panggilan='{$row['nama_panggilan']}'
                                                data-alamat='{$row['alamat']}'
                                                data-perusahaan='{$row['id_perusahaan']}'
                                                data-bagian='{$row['bagian']}'
                                                data-provinsi='{$row['id_provinsi']}'
                                                data-kota='{$row['id_kota']}'
                                                data-kecamatan='{$row['id_kecamatan']}'
                                                data-kelurahan='{$row['id_kelurahan']}'
                                                data-kodepos='{$row['kodepos']}'
                                                data-tempatlahir='{$row['tempat_lahir']}'
                                                data-tanggallahir='{$row['tanggal_lahir2']}'
                                                data-telephone='{$row['no_telp']}'
                                                data-handphone='{$row['no_hp']}'
                                                data-email='{$row['email']}'
                                                data-username='{$row['username']}'
                                                data-foto='{$row['foto']}'
                                                data-status='{$row['level_kd']}'>Edit</button>
                        <button class='delete' data-id='{$row['id']}' data-nama_lengkap='{$row['nama_lengkap']}'>Delete</button>";
            $data[] = $row;
        }

        $totalQuery = "SELECT COUNT(*) AS total $baseQuery WHERE a.is_deleted = 0 AND cust.is_deleted = 0";
        $qTotal = $this->conn->query($totalQuery);
        $total = $qTotal ? (int) $qTotal->fetch_assoc()['total'] : 0;

        $filteredQuery = "SELECT COUNT(*) AS total $baseQuery $where";
        $qFiltered = $this->conn->query($filteredQuery);
        $totalFiltered = $qFiltered ? (int) $qFiltered->fetch_assoc()['total'] : $total;

        return [
            "draw" => intval($request['draw'] ?? 0),
            "recordsTotal" => $total,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ];
    }
    // ==================================================================================

    public function checkUserExis($namaLengkap, $perusahaan, $bagian)
    {
        $sql = "SELECT a.nama_lengkap as nama_lengkap ,b.nm_perusahaan as nama_perusahaan, a.bagian as bagian 
                FROM users as a  INNER JOIN perusahaan as b ON a.per_id = b.id WHERE a.nama_Lengkap = ? and a.per_id=? and a.bagian = ? and is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $namaLengkap, $perusahaan, $bagian);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data;
    }

    // =================== GENERATE KD USER ===================
    public function generatekdUser()
    {
        $prefix = "USR-";
        $this->conn->begin_transaction();

        try {
            // Kunci tabel
            $this->conn->query("LOCK TABLES {$this->table} WRITE");

            $sql = "SELECT kd_user FROM {$this->table} ORDER BY id DESC LIMIT 1 FOR UPDATE";
            $result = $this->conn->query($sql);

            if ($result && $row = $result->fetch_assoc()) {
                $lastNumber = (int) substr($row['kd_user'], strlen($prefix));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $kd_user = $prefix . str_pad($newNumber, 6, "0", STR_PAD_LEFT);

            // Lepaskan kunci
            $this->conn->query("UNLOCK TABLES");
            $this->conn->commit();

            return $kd_user;
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->query("UNLOCK TABLES");
            throw $e;
        }
    }

    public function getAllres($res, $id)
    {
        $sql = "SELECT 1 
            FROM {$this->table} 
            WHERE per_id = ? AND nama_lengkap LIKE ?  and is_deleted = 0
            LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        $res = "%" . $res . "%";
        $stmt->bind_param("is", $id, $res);
        $stmt->execute();
        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();
        return $exists; // true jika ada, false kalau tidak
    }

    public function getAllq($q, $id)
    {
        $sql = "SELECT * FROM {$this->table} where per_id = ? AND nama_lengkap like ? AND is_deleted = 0 ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $q = "%" . $q . "%";
        $stmt->bind_param("is", $id, $q);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row; // masukkan ke array
        }
        return $data;
    }

    public function getAllid($id)
    {
        $sql = "SELECT * FROM {$this->table} where per_id = ? AND is_deleted = 0 ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row; // masukkan ke array
        }
        return $data;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_deleted = 0 ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        // $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $kd_user = $row['kd_user'];
            return $kd_user;
        }
        return null;
    }

    public function hasRelatedPendaftaran($userId)
    {
        logWithTimestamp("=== MODEL hasRelatedPendaftaran({$userId}) START ===", $this->logFile);

        $sql = "SELECT u.nama_lengkap AS nama_user,
                        COUNT(p.id) AS jumlah_pendaftaran
                    FROM users u
                    LEFT JOIN pendaftaran p ON p.user_id = u.id
                    WHERE p.id = ? AND is_deleted = 0
                    GROUP BY u.id, u.nama_lengkap";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        // $count = (int) ($row['cnt'] ?? 0);
        $namaUser = $row['nama_user'] ?? '';
        $jumlah = (int) ($row['jumlah_pendaftaran'] ?? 0);
        $data = ['namaUser' => $namaUser, 'jumlah' => $jumlah];
        logWithTimestamp("=== MODEL hasRelatedPendaftaran({$userId}) FINISH — jumlah relasi: {$jumlah} ===", $this->logFile);

        return $data;
    }

    public function hasRelatedPeserta($userId)
    {
        logWithTimestamp("=== MODEL hasRelatedPeserta({$userId}) START ===", $this->logFile);

        $sql = "SELECT u.nama_lengkap AS nama_user,
                        COUNT(p.id) AS jumlah_peserta
                    FROM users u
                    LEFT JOIN peserta p ON p.user_id = u.id
                    WHERE p.id = ? AND is_deleted = 0
                    GROUP BY u.id, u.nama_lengkap";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $namaUser = $row['nama_user'] ?? '';
        $jumlah = (int) ($row['jumlah_peserta'] ?? 0);
        $data = ['namaUser' => $namaUser, 'jumlah' => $jumlah];
        logWithTimestamp("=== MODEL hasRelatedPeserta({$userId}) FINISH — jumlah relasi: {$jumlah} ===", $this->logFile);

        return $data;
    }

    // =================== INSERT USER ===================

    // public function insert(
    //     $kd_user,
    //     $namaLengkap,
    //     $namaPanggilan,
    //     $alamat,
    //     $perusahaan,
    //     $bagian,
    //     $provinsi,
    //     $kota,
    //     $kecamatan,
    //     $kelurahan,
    //     $kodepos,
    //     $tempatLahir,
    //     $birthday,
    //     $telephone,
    //     $handphone,
    //     $email,
    //     $fotoName,
    //     $username,
    //     $password1,
    //     $statusAdmin
    // ) {
    //     logWithTimestamp("=== MODEL insert({$kd_user}) START ===", $this->logFile);
    //     try {
    //         $now = date('Y-m-d H:i:s');

    //         // 🔹 Hash password
    //         $passwordHash = password_hash($password1, PASSWORD_BCRYPT);

    //         // 🔹 Siapkan query
    //         $sql = "INSERT INTO {$this->table} 
    //             (kd_user, nama_lengkap, nama_panggilan, alamat, per_id, bagian, prov_id, kota_id, kec_id, desa_id, 
    //              kodepos, tempat_lahir, tanggal_lahir, no_telp, no_handphone, email, foto, username, password, 
    //              tgl_input, user_input, user_level) 
    //             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    //         $stmt = $this->conn->prepare($sql);
    //         if (!$stmt) {
    //             $error = $this->conn->error;
    //             logWithTimestamp("GAGAL menyiapkan statement INSERT: {$error}", $this->logFile);
    //             throw new Exception("Gagal menyiapkan query: {$error}");
    //         }

    //         // 🔹 Binding parameter
    //         $stmt->bind_param(
    //             "ssssissiiissssssssssss",
    //             $kd_user,
    //             $namaLengkap,
    //             $namaPanggilan,
    //             $alamat,
    //             $perusahaan,   // i
    //             $bagian,
    //             $provinsi,     // i
    //             $kota,         // i
    //             $kecamatan,    // i
    //             $kelurahan,    // i
    //             $kodepos,
    //             $tempatLahir,
    //             $birthday,
    //             $telephone,
    //             $handphone,
    //             $email,
    //             $fotoName,
    //             $username,
    //             $passwordHash,
    //             $now,
    //             $this->user_input,
    //             $statusAdmin
    //         );

    //         // 🔹 Eksekusi query
    //         if (!$stmt->execute()) {
    //             $error = $stmt->error;
    //             logWithTimestamp("GAGAL eksekusi INSERT: {$error}", $this->logFile);
    //             throw new Exception("Gagal menyimpan data user: {$error}");
    //         }

    //         logWithTimestamp("SUCCESS: Berhasil insert user ID {$kd_user}", $this->logFile);

    //         $stmt->close();
    //         return true;
    //     } catch (Exception $e) {
    //         logWithTimestamp("ERROR insert({$kd_user}): " . $e->getMessage(), $this->logFile);
    //         return false;
    //     } finally {
    //         logWithTimestamp("=== MODEL insert({$kd_user}) FINISH ===", $this->logFile);
    //     }
    // }

    public function DataPesertaPIC($id)
    {
        logWithTimestamp("=== MODEL DataPesertaPIC({$id}) START ===", $this->logFile);

        try {
            $this->conn->query("SET lc_time_names = 'id_ID'");
            $sql = "SELECT us.id as id,
                            us.kd_user AS kd_user
                                ,cus.nama_lengkap AS nama_lengkap
                                ,cus.nama_panggilan AS nama_panggilan
                                ,per.nm_perusahaan AS nama_perusahaan
                                ,per.id AS kd_perusahaan
                                ,cus.bagian AS bagian
                                ,cus.alamat AS alamat
                                ,cus.prov_id AS provinsi
                                ,prov.nama_prov AS nama_prov
                                ,cus.kota_id AS kota_kab
                                ,koka.nama_kota AS nama_kota
                                ,cus.kec_id AS kecamatan
                                ,kec.nama_kec AS nama_kec
                                ,cus.desa_id AS kelurahan
                                ,kel.nama_desa AS nama_desa
                                ,cus.kodepos AS kodepos
                                ,cus.tempat_lahir AS tempat_lahir
                                ,DATE_FORMAT(cus.tanggal_lahir, '%d %M %Y') AS tanggal_lahir
                                ,cus.no_telp AS no_telp
                                ,cus.no_handphone AS no_handphone
                                ,cus.email AS email
                                ,per.email1 as email_perusahaan
                                ,cus.foto as foto
                                FROM users AS us 
                            INNER JOIN customer AS cus ON us.cust_id = cus.id
                            LEFT JOIN perusahaan AS per ON cus.per_id = per.id
                            LEFT JOIN provinsi AS prov ON cus.prov_id = prov.id
                            LEFT JOIN kota_kab AS koka ON cus.kota_id = koka.id
                            LEFT JOIN kecamatan AS kec ON cus.kec_id = kec.id
                            LEFT JOIN desa AS kel ON cus.desa_id = kel.id
                            WHERE us.id = ? AND cus.is_deleted = 0";

            // $sql = "SELECT us.id as id,
            //                 us.kd_user AS kd_user
            //                     ,us.nama_lengkap AS nama_lengkap
            //                     ,us.nama_panggilan AS nama_panggilan
            //                     ,per.nm_perusahaan AS nama_perusahaan
            //                     ,per.kd_perusahaan AS kd_perusahaan
            //                     ,us.bagian AS bagian
            //                     ,us.alamat AS alamat
            //                     ,us.prov_id AS provinsi
            //                     ,prov.nama_prov AS nama_prov
            //                     ,us.kota_id AS kota_kab
            //                     ,koka.nama_kota AS nama_kota
            //                     ,us.kec_id AS kecamatan
            //                     ,kec.nama_kec AS nama_kec
            //                     ,us.desa_id AS kelurahan
            //                     ,kel.nama_desa AS nama_desa
            //                     ,us.kodepos AS kodepos
            //                     ,us.tempat_lahir AS tempat_lahir
            //                     ,us.tanggal_lahir AS tanggal_lahir
            //                     ,us.no_telp AS no_telp
            //                     ,us.no_handphone AS no_handphone
            //                     ,us.email AS email
            //                     ,per.email1 as email_perusahaan
            //                     ,us.foto as foto
            //                     FROM users AS us 
            //                 LEFT JOIN perusahaan AS per ON us.per_id = per.id
            //                 LEFT JOIN provinsi AS prov ON us.prov_id = prov.id
            //                 LEFT JOIN kota_kab AS koka ON us.kota_id = koka.id
            //                 LEFT JOIN kecamatan AS kec ON us.kec_id = kec.id
            //                 LEFT JOIN desa AS kel ON us.desa_id = kel.id
            //                 WHERE us.id = ? AND us.is_deleted = 0";
            // // responseJSON([], 'error', 'Masuk DataPesertaPIC' . $sql);
            $stmt = $this->conn->prepare($sql);
            $error = $stmt->error;
            if (!$stmt) {
                logWithTimestamp("GAGAL menyiapkan statement SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal menyiapkan query: {$error}");
            }
            // 🔹 Binding parameter
            if (!$stmt->bind_param("i", $id)) {
                logWithTimestamp("GAGAL bind_param SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal bind parameter: {$error}");
            }

            // 🔹 Eksekusi query
            if (!$stmt->execute()) {
                logWithTimestamp("GAGAL eksekusi SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal mengambil data peserta PIC: {$error}");
            }

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            logWithTimestamp("SUCCESS: Data peserta PIC berhasil di ambil {$id}", $this->logFile);
            return $row;
        } catch (Exception $e) {
            logWithTimestamp("ERROR DataPesertaPIC({$id}): " . $e->getMessage(), $this->logFile);
            return false;
        } finally {
            logWithTimestamp("=== MODEL DataPesertaPIC({$id}) FINISH ===", $this->logFile);
        }
    }

    public function DataPerusahaanPesertaPIC($id)
    {
        logWithTimestamp("=== MODEL DataPerusahaanPesertaPIC({$id}) START ===", $this->logFile);
        try {
            $sql = "SELECT us.id AS id 
                                    ,us.kd_user AS kd_user
                                    ,per.nm_perusahaan AS nama_perusahaan
                                    ,per.alamat AS alamat
                                    ,per.prov_id AS provinsi
                                    ,prov.nama_prov AS nama_prov
                                    ,per.kota_id AS kota_kab
                                    ,koka.nama_kota AS nama_koka
                                    ,per.kec_id AS kecamatan
                                    ,kec.nama_kec AS nama_kec
                                    ,per.desa_id AS kelurahan
                                    ,kel.nama_desa AS nama_desa
                                    ,per.kodepos AS kodepos
                                    ,per.url AS url
                                    ,per.no_telp1 AS telp_1
                                    ,per.no_telp2 AS telp_2
                                    ,per.no_telp3 AS telp_3
                                    ,per.email1 AS email_1
                                    ,per.email2 AS email_2
                                    ,per.email3 AS email_3
                                    ,per.logo AS logo
                                    FROM perusahaan AS per 
                                INNER JOIN customer AS cus ON cus.per_id = per.id
                                INNER JOIN users AS us ON us.cust_id = cus.id 
                                LEFT JOIN provinsi AS prov ON per.prov_id = prov.id
                                LEFT JOIN kota_kab AS koka ON per.kota_id = koka.id
                                LEFT JOIN kecamatan AS kec ON per.kec_id = kec.id
                                LEFT JOIN desa AS kel ON per.desa_id = kel.id
                                WHERE us.id= ? AND cus.is_deleted = 0";
            // $sql = "SELECT us.id AS id 
            //                         ,us.kd_user AS kd_user
            //                         ,per.nm_perusahaan AS nama_perusahaan
            //                         ,per.alamat AS alamat
            //                         ,per.prov_id AS provinsi
            //                         ,prov.nama_prov AS nama_prov
            //                         ,per.kota_id AS kota_kab
            //                         ,koka.nama_kota AS nama_koka
            //                         ,per.kec_id AS kecamatan
            //                         ,kec.nama_kec AS nama_kec
            //                         ,per.desa_id AS kelurahan
            //                         ,kel.nama_desa AS nama_desa
            //                         ,per.kodepos AS kodepos
            //                         ,per.url AS url
            //                         ,per.no_telp1 AS telp_1
            //                         ,per.no_telp2 AS telp_2
            //                         ,per.no_telp3 AS telp_3
            //                         ,per.email1 AS email_1
            //                         ,per.email2 AS email_2
            //                         ,per.email3 AS email_3
            //                         ,per.logo AS logo
            //                         FROM perusahaan AS per 
            //                     LEFT JOIN users AS us ON per.id = us.per_id 
            //                     LEFT JOIN provinsi AS prov ON per.prov_id = prov.id
            //                     LEFT JOIN kota_kab AS koka ON per.kota_id = koka.id
            //                     LEFT JOIN kecamatan AS kec ON per.kec_id = kec.id
            //                     LEFT JOIN desa AS kel ON per.desa_id = kel.id
            //                     WHERE us.id= ? AND us.is_deleted = 0";

            $stmt = $this->conn->prepare($sql);
            $error = $stmt->error;
            if (!$stmt) {
                logWithTimestamp("GAGAL menyiapkan statement SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal menyiapkan query: {$error}");
            }
            // 🔹 Binding parameter
            if (!$stmt->bind_param("i", $id)) {
                logWithTimestamp("GAGAL bind_param SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal bind parameter: {$error}");
            }

            // 🔹 Eksekusi query
            if (!$stmt->execute()) {
                logWithTimestamp("GAGAL eksekusi SELECT: {$error}", $this->logFile);
                throw new Exception("Gagal mengambil data perusahaan peserta PIC: {$error}");
            }

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            logWithTimestamp("SUCCESS: Data perusahaan peserta PIC berhasil di ambil {$id}", $this->logFile);
            return $row;
        } catch (Exception $e) {
            logWithTimestamp("ERROR DataPerusahaanPesertaPIC({$id}): " . $e->getMessage(), $this->logFile);
            return false;
        } finally {
            logWithTimestamp("=== MODEL DataPerusahaanPesertaPIC({$id}) FINISH ===", $this->logFile);
        }
    }

    // =================== UPDATE USER ===================

    public function updateUser($id, $data, $fotoname = null)
    {
        logWithTimestamp("=== MODEL updateUser({$id}) START ===", $this->logFile);
        try {
            if (!$id) {
                throw new Exception("ID user tidak ditemukan.");
            }

            // 🔹 Persiapkan field
            $fields = [
                "nama_lengkap"      => $data['nama-lengkap-user-edit'],
                "nama_Panggilan"    => $data['nama-panggilan-user-edit'],
                "alamat"            => $data['alamat-user-edit'],
                "per_id"            => $data['perusahaan-user-edit'],
                "bagian"            => $data['bagian-user-edit'],
                "prov_id"           => $data['provinsi-user-edit'],
                "kota_id"           => $data['kota-user-edit'],
                "kec_id"            => $data['kecamatan-user-edit'],
                "desa_id"           => $data['kelurahan-user-edit'],
                "kodepos"           => $data['kodepos-user-edit'],
                "tempat_Lahir"      => $data['tempat-lahir-user-edit'],
                "tanggal_lahir"     => $data['birthday-edit'],
                "no_telp"           => $data['telephone-user-edit'],
                "no_handphone"      => $data['handphone-user-edit'],
                "email"             => $data['email-user-edit'],
                "username"          => $data['username-login-user-edit'],
                "user_update"       => $_SESSION['user_id'] ?? 'admin',
                "tgl_update"        => date('Y-m-d H:i:s'),
            ];

            if ($fotoname) {
                $fields["foto"] = $fotoname;
            }
            if ($_SESSION['level'] === 'super admin') {
                $fields['user_level'] = $data['status-admin-user-edit'];
            }
            // 🔹 Buat query SET dinamis
            $setParts = [];
            $values   = [];
            $types    = "";

            foreach ($fields as $col => $val) {
                $setParts[] = "$col = ?";
                $values[]   = $val;
                $types     .= "s"; // semua string, sesuaikan jika ada int
            }

            $sql = "UPDATE {$this->table} SET " . implode(", ", $setParts) . " WHERE id = ? AND is_deleted = 0";
            // $sql = "UPDATE customer SET " . implode(", ", $setParts) . " WHERE id = ? AND is_deleted = 0";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $error = $this->conn->error;
                logWithTimestamp("GAGAL menyiapkan statement UPDATE: {$error}", $this->logFile);
                throw new Exception("Gagal menyiapkan query: {$error}");
            }

            $values[] = $id;
            $types   .= "i";

            // 🔹 Binding parameter
            if (!$stmt->bind_param($types, ...$values)) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL bind_param UPDATE: {$error}", $this->logFile);
                throw new Exception("Gagal bind parameter: {$error}");
            }
            // responseJSON([], 'error', "masuk model update user" . $sql . $values);

            // 🔹 Eksekusi query
            if (!$stmt->execute()) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL eksekusi UPDATE: {$error}", $this->logFile);
                throw new Exception("Gagal update data user: {$error}");
            }

            if ($stmt->affected_rows <= 0) {
                logWithTimestamp("Tidak ada perubahan data untuk ID {$id}", $this->logFile);
                throw new Exception("Data user ID {$id} tidak ditemukan atau tidak ada perubahan.");
            }

            logWithTimestamp("SUCCESS: Berhasil update data user ID {$id}", $this->logFile);
            $stmt->close();
            return true;
        } catch (Exception $e) {
            logWithTimestamp("ERROR updateUser({$id}): " . $e->getMessage(), $this->logFile);
            return $e->getMessage(); // ✅ biar controller tahu penyebab error
        } finally {
            logWithTimestamp("=== MODEL updateUser({$id}) FINISH ===", $this->logFile);
        }
    }

    public function updateUser_staff($id, $data, $fotoname = null)
    {
        logWithTimestamp("=== MODEL updateUser_staff({$id}) START ===", $this->logFile);

        try {
            if (!$id) {
                throw new Exception("ID user tidak ditemukan.");
            }

            // 🔹 Persiapkan field
            $fields = [
                "nama_lengkap"              => $data['nama-lengkap-user-edit'],
                "nama_Panggilan"            => $data['nama-panggilan-user-edit'],
                "alamat"                    => $data['alamat-user-edit'],
                "per_id"                    => $data['perusahaan-user-edit'],
                "bagian"                    => $data['bagian-user-edit'],
                "prov_id"                   => $data['provinsi-user-edit'],
                "kota_id"                   => $data['kota-user-edit'],
                "kec_id"                    => $data['kecamatan-user-edit'],
                "desa_id"                   => $data['kelurahan-user-edit'],
                "kodepos"                   => $data['kodepos-user-edit'],
                "tempat_Lahir"              => $data['tempat-lahir-user-edit'],
                "tanggal_lahir"             => $data['birthday-edit'],
                "no_telp"                   => $data['telephone-user-edit'],
                "no_handphone"              => $data['handphone-user-edit'],
                "email"                     => $data['email-user-edit'],
                "username"                  => $data['username-login-user-edit'],
                "user_level"                => $data['status-admin-user-edit'],
                "status_kepegawaian"        => $data['status-kepegawaian-user-edit'],
                "user_update"               => $_SESSION['user_name'] ?? 'admin',
                "tgl_update"                => date('Y-m-d H:i:s'),
            ];

            if ($fotoname) {
                $fields["foto"] = $fotoname;
            }

            // 🔹 Buat query SET dinamis
            $setParts = [];
            $values   = [];
            $types    = "";

            foreach ($fields as $col => $val) {
                $setParts[] = "$col = ?";
                $values[]   = $val;
                $types     .= "s"; // semua string, sesuaikan jika ada int
            }

            $sql = "UPDATE {$this->table} SET " . implode(", ", $setParts) . " WHERE id = ? AND is_deleted = 0";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                $error = $this->conn->error;
                logWithTimestamp("GAGAL menyiapkan statement UPDATE: {$error}", $this->logFile);
                throw new Exception("Gagal menyiapkan query: {$error}");
            }

            $values[] = $id;
            $types   .= "i";

            // 🔹 Binding parameter
            if (!$stmt->bind_param($types, ...$values)) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL bind_param UPDATE: {$error}", $this->logFile);
                throw new Exception("Gagal bind parameter: {$error}");
            }

            // 🔹 Eksekusi query
            if (!$stmt->execute()) {
                $error = $stmt->error;
                logWithTimestamp("GAGAL eksekusi UPDATE: {$error}", $this->logFile);
                throw new Exception("Gagal update data user: {$error}");
            }

            if ($stmt->affected_rows <= 0) {
                logWithTimestamp("Tidak ada perubahan data untuk ID {$id}", $this->logFile);
                throw new Exception("Data user ID {$id} tidak ditemukan atau tidak ada perubahan.");
            }

            logWithTimestamp("SUCCESS: Berhasil update data user ID {$id}", $this->logFile);
            $stmt->close();
            return true;
        } catch (Exception $e) {
            logWithTimestamp("ERROR updateUser({$id}): " . $e->getMessage(), $this->logFile);
            return $e->getMessage(); // ✅ biar controller tahu penyebab error
        } finally {
            logWithTimestamp("=== MODEL updateUser_staff({$id}) FINISH ===", $this->logFile);
        }
    }

    public function delete($id)
    {
        $sql = "UPDATE {$this->table}
            SET is_deleted = 1,
                deleted_at = NOW(),
                deleted_by = ?
            WHERE id = ? AND is_deleted = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $this->user_input, $id);
        return $stmt->execute();
    }

    public function insertLog($data)
    {
        $user_id    = isset($data['user_id']) ? (int)$data['user_id'] : 'NULL';
        $action     = mysqli_real_escape_string($this->conn, $data['action']);
        $table_name = isset($data['table_name']) ? "'" . mysqli_real_escape_string($this->conn, $data['table_name']) . "'" : "NULL";
        $record_id  = isset($data['record_id']) ? (int)$data['record_id'] : "NULL";
        $description = isset($data['description']) ? "'" . mysqli_real_escape_string($this->conn, $data['description']) . "'" : "NULL";
        $ip_address = isset($data['ip_address']) ? "'" . mysqli_real_escape_string($this->conn, $data['ip_address']) . "'" : "NULL";

        $sql = "INSERT INTO logs (user_id, action, table_name, record_id, description, ip_address) 
            VALUES ($user_id, '$action', $table_name, $record_id, $description, $ip_address)";
        mysqli_query($this->conn, $sql);

        // $this->logDebug('insertLog SELESAI');
    }

    public function getDataTable_staff($request)
    {
        // logDebugtable("=== DEBUG ajaxList STAFF START === ", $request);

        $columns = [
            'id',
            'nama_lengkap',
            'nama_perusahaan',
            'bagian',
            'alamat',
            'provinsi',
            'kota',
            'kecamatan',
            'desa',
            'kodepos',
            'tempat_lahir',
            'tanggal_lahir',
            'email',
            'no_telp',
            'no_hp',
            'foto',
            'username',
            'level',
            'status_kepegawaian',
            'ket_update'
        ];

        // === BASE QUERY ==
        $baseQuery = "FROM users AS a
        LEFT JOIN perusahaan AS b ON a.per_id = b.id
        LEFT JOIN provinsi AS c ON a.prov_id = c.id
        LEFT JOIN kota_kab AS d ON a.kota_id = d.id
        LEFT JOIN kecamatan AS e ON a.kec_id = e.id
        LEFT JOIN desa AS f ON a.desa_id = f.id
        INNER JOIN user_level AS g ON a.user_level = g.id";

        // === WHERE default untuk staff ===
        // COM-000001 + tidak dihapus
        $where = "WHERE b.kd_perusahaan='COM-000001' AND a.is_deleted = 0";

        // === SEARCH ===
        if (!empty($request['search']['value'])) {
            $search = $this->conn->real_escape_string($request['search']['value']);

            $where .= " AND (
                a.nama_lengkap LIKE '%{$search}%'
            OR b.nm_perusahaan LIKE '%{$search}%'
            OR a.bagian LIKE '%{$search}%'
            OR a.alamat LIKE '%{$search}%'
            OR c.nama_prov LIKE '%{$search}%'
            OR d.nama_kota LIKE '%{$search}%'
            OR e.nama_kec LIKE '%{$search}%'
            OR f.nama_desa LIKE '%{$search}%'
            OR a.kodepos LIKE '%{$search}%'
            OR a.username LIKE '%{$search}%'
            OR g.nama LIKE '%{$search}%'
        )";
        }

        // === MAIN SELECT ===
        $sql = "SELECT
                a.id AS id,
                a.nama_lengkap AS nama_lengkap,
                a.nama_panggilan AS nama_panggilan,
                b.id AS id_perusahaan,
                b.nm_perusahaan AS nama_perusahaan,
                a.bagian AS bagian,
                a.alamat AS alamat,
                c.id AS id_provinsi,
                c.nama_prov AS provinsi,
                d.id AS id_kota,
                d.nama_kota AS kota,
                e.id AS id_kecamatan,
                e.nama_kec AS kecamatan,
                f.id AS id_kelurahan,
                f.nama_desa AS desa,
                a.kodepos AS kodepos,
                a.tempat_lahir AS tempat_lahir,
                DATE_FORMAT(a.tanggal_lahir, '%d %M %Y') AS tanggal_lahir,
                a.tanggal_lahir AS tanggal_lahir2,
                a.email AS email,
                a.no_telp AS no_telp,
                a.no_handphone AS no_hp,
                a.foto AS foto,
                a.username AS username,
                g.id AS user_level,
                g.nama AS level,
                a.status_kepegawaian AS status_kepegawaian,
                a.ket_update AS ket_update
            $baseQuery
            $where";

        // === ORDER ===
        if (isset($request['order'][0])) {
            $colIndex = intval($request['order'][0]['column']);
            $dir = strtoupper($request['order'][0]['dir']) === 'DESC' ? 'DESC' : 'ASC';

            if (isset($columns[$colIndex])) {
                $sql .= " ORDER BY {$columns[$colIndex]} $dir";
            } else {
                $sql .= " ORDER BY a.id ASC";
            }
        } else {
            $sql .= " ORDER BY a.id ASC";
        }

        // === LIMIT ===
        $start = intval($request['start']);
        $length = intval($request['length']);
        $sqlLimit = $sql . " LIMIT $start, $length";

        // LOG final query
        // logDebugtable("[FINAL SQL STAFF]", $sqlLimit);

        // === EXECUTE MAIN QUERY ===
        $result = $this->conn->query($sqlLimit);
        if (!$result) {
            // logDebugtable("SQL Error STAFF", [
            //     'error' => $this->conn->error,
            //     'query' => $sqlLimit
            // ]);
            return [
                "draw" => intval($request['draw'] ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ];
        }

        // === BUILD DATA ===
        $data = [];
        $no = $start + 1;

        while ($row = $result->fetch_assoc()) {
            $row['no'] = $no++;

            $row['aksi'] = "<button class='edit' data-id='{$row['id']}' 
                                            data-nama='{$row['nama_lengkap']}'
                                            data-panggilan='{$row['nama_panggilan']}'
                                            data-alamat='{$row['alamat']}'
                                            data-perusahaan='{$row['id_perusahaan']}'
                                            data-bagian='{$row['bagian']}'
                                            data-provinsi='{$row['id_provinsi']}'
                                            data-kota='{$row['id_kota']}'
                                            data-kecamatan='{$row['id_kecamatan']}'
                                            data-kelurahan='{$row['id_kelurahan']}'
                                            data-kodepos='{$row['kodepos']}'
                                            data-tempatlahir='{$row['tempat_lahir']}'
                                            data-tanggallahir='{$row['tanggal_lahir2']}'
                                            data-telephone='{$row['no_telp']}'
                                            data-handphone='{$row['no_hp']}'
                                            data-email='{$row['email']}'
                                            data-username='{$row['username']}'
                                            data-foto='{$row['foto']}'
                                            data-status='{$row['user_level']}'
                                            data-status_kepegawaian='{$row['status_kepegawaian']}'>Edit</button>
                            <button class='delete' data-id='{$row['id']}' data-nama_lengkap='{$row['nama_lengkap']}'>Delete</button>";

            $data[] = $row;
        }

        // === COUNT TOTAL ===
        $totalQuery = "SELECT COUNT(*) AS total $baseQuery WHERE b.kd_perusahaan='COM-000001' AND a.is_deleted = 0";
        // logDebugtable("[SQL STAFF COUNT TOTAL]", $totalQuery);

        $qTotal = $this->conn->query($totalQuery);
        $total = $qTotal ? intval($qTotal->fetch_assoc()['total']) : 0;

        // === COUNT FILTERED ===
        $filteredQuery = "SELECT COUNT(*) AS total $baseQuery $where";
        // logDebugtable("[SQL STAFF COUNT FILTERED]", $filteredQuery);

        $qFiltered = $this->conn->query($filteredQuery);
        $totalFiltered = $qFiltered ? intval($qFiltered->fetch_assoc()['total']) : $total;

        // logDebugtable("=== DEBUG ajaxList STAFF FINISH === ", [
        //     'returned' => count($data),
        //     'total' => $total,
        //     'filtered' => $totalFiltered
        // ]);

        return [
            "draw" => intval($request['draw'] ?? 0),
            "recordsTotal" => $total,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ];
    }
}
