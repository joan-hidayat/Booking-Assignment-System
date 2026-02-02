<?php
class MarketingService
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get next assignee using round-robin strategy
     */
    public function getNextAssignee(): ?int
    {
        $sql = "SELECT u.id
                FROM users u
                INNER JOIN staff s ON u.staff_id = s.id
                WHERE s.department = 'marketing'
                AND s.status = 'active'
                ORDER BY u.id ASC";

        $res = mysqli_query($this->conn, $sql);

        $assignees = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $assignees[] = $row['id'];
        }

        if (empty($assignees)) {
            return null;
        }

        $sqlLast = "SELECT assignee_id
                    FROM assignments
                    WHERE assignee_id IS NOT NULL
                    ORDER BY id DESC LIMIT 1";

        $resLast = mysqli_query($this->conn, $sqlLast);

        $last = ($resLast && mysqli_num_rows($resLast) > 0)
            ? mysqli_fetch_assoc($resLast)['assignee_id']
            : null;

        $nextIndex = 0;

        if ($last !== null) {
            foreach ($assignees as $i => $id) {
                if ($id == $last) {
                    $nextIndex = ($i + 1) % count($assignees);
                    break;
                }
            }
        }

        return $assignees[$nextIndex];
    }
}

// }
