<?php

class BookingModel
{
    private mysqli $conn;
    private string $table = 'main_records';
    private int $actorId;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
        $this->actorId = $_SESSION['user_id'] ?? 0;
    }

    public function getUserInput()
    {
        return $this->actorId;
    }
    /* ===============================
       Core Utilities
    =============================== */

    public function getConnection(): mysqli
    {
        return $this->conn;
    }
    public function generateKodeBooking()
    {
        return generateKode("BOO-", "booking", "kd_booking");
    }


    public function generateCode(string $prefix = 'BK-', int $length = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $out = '';

        for ($i = 0; $i < $length; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $prefix . $out;
    }

    public function countPendingAssignments(): int
    {
        $actorId = $_SESSION['user_id'] ?? 0;

        if (!$actorId) {
            throw new RuntimeException("Unauthorized access");
        }

        $sql = "SELECT COUNT(*) AS total
            FROM main_records r
            INNER JOIN users u ON r.assigned_agent = u.id
            INNER JOIN departments d ON u.department_id = d.id
            WHERE r.assigned_agent = ?
            AND r.progress = 'pending'
            AND r.status = 'active'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $actorId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return (int) ($row['total'] ?? 0);
    }

    public function listAssignedRecords(int $agentId): array
    {
        $sql = "SELECT id,
                   participant_name,
                   organization,
                   contact_person,
                   progress,
                   created_at
            FROM main_records
            WHERE assigned_agent = ?
            ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $agentId);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }
    /* ===============================
       Negotiation Update
    =============================== */

    public function updatePricing(int $id, string $method, array $adjustments): bool
    {
        if (!$id || empty($adjustments)) {
            return false;
        }

        $payload = json_encode($adjustments, JSON_UNESCAPED_UNICODE);

        $sql = "UPDATE main_records
                SET method = ?, 
                    pricing_data = ?, 
                    updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $method, $payload, $id);

        return $stmt->execute();
    }

    /* ===============================
       Fetch Detail
    =============================== */

    public function getDetail(int $id): ?array
    {
        $sql = "SELECT r.id,
                       r.participant_name,
                       r.organization,
                       c.title AS catalog_title,
                       r.status,
                       r.method
                FROM main_records r
                INNER JOIN schedule s ON r.schedule_id = s.id
                INNER JOIN catalog c ON s.catalog_id = c.id
                WHERE r.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /* ===============================
       Round-robin assignment
    =============================== */

    public function assignNextAgent(): ?int
    {
        $agents = $this->conn
            ->query("SELECT id FROM agents WHERE active = 1 ORDER BY id ASC")
            ->fetch_all(MYSQLI_ASSOC);

        if (!$agents) return null;

        $last = $this->conn
            ->query("SELECT assigned_agent FROM main_records
                     WHERE assigned_agent IS NOT NULL
                     ORDER BY id DESC LIMIT 1")
            ->fetch_assoc();

        $lastId = $last['assigned_agent'] ?? null;

        foreach ($agents as $i => $agent) {
            if ($agent['id'] == $lastId) {
                return $agents[($i + 1) % count($agents)]['id'];
            }
        }

        return $agents[0]['id'];
    }

    /* ===============================
       Create Record
    =============================== */

    public function create(array $data): int
    {
        $sql = "INSERT INTO main_records
                (code, schedule_id, participant_name,
                 email, organization, status, assigned_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sissssi",
            $data['code'],
            $data['schedule_id'],
            $data['participant_name'],
            $data['email'],
            $data['organization'],
            $data['status'],
            $data['assigned_agent']
        );

        $stmt->execute();

        return $this->conn->insert_id;
    }

    /* ===============================
       Mock Notification Layer
    =============================== */

    public function simulateNotification(int $id): bool
    {
        // Public demo version:
        // Real email / messaging integration removed
        return true;
    }
}
