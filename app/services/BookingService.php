<?php
require_once __DIR__ . '/MarketingService.php';
require_once __DIR__ . '/../models/BookingModel.php';
// require_once __DIR__ . '/../../private/models/BookingModel.php';
require_once __DIR__ . '/../models/UsersModel.php';
// require_once __DIR__ . '/../../private/models/UsersModel.php';
// require_once __DIR__ . '/../helpers/app_helper.php';

class BookingService
{
    private $bookingModel;
    private $logFile;
    private $service_marketing;
    private $usersModel;

    public function __construct($conn)
    {
        $this->bookingModel = new BookingModel($conn);
        $this->service_marketing = new MarketingService($conn);
        $this->usersModel = new UsersModel($conn);
        $this->logFile = __DIR__ . '/../../app/logs/debug_service.log';
    }

    public function listBooking()
    {
        $marketingId = $_SESSION['user_id'] ?? 0;
        logWithTimestamp("=== MODEL BOOKINGMODEL listBooking() ambil marketing " . json_encode($marketingId), $this->logFile);
        if (!$marketingId) {
            throw new Exception("gagal ambil marketing: " . json_encode($marketingId)  . $this->bookingModel->getConnection()->error);
        }
        if ($marketingId == 0) {
            return [];
        }
        return $this->bookingModel->listAssignedRecords($marketingId);
    }

    public function checkBooking()
    {
        $marketingId = $_SESSION['user_id'] ?? 0;
        if (!$marketingId) {
            throw new Exception("gagal ambil marketing" . json_encode($marketingId)  . $this->bookingModel->getConnection()->error);
        }
        if ($marketingId == 0) {
            return 0;
        }
        return $this->bookingModel->countPendingAssignments($marketingId);
    }
    // ========================== tahan
    public function createBooking(array $payload): void
    {
        $emailCustomer = $payload['BookingEmailPribadi'] ?? $payload['BookingEmailPerusahaan'];
        if (!$emailCustomer) {
            throw new Exception('Email customer tidak valid');
        }

        $customer = $this->usersModel->findUserByemail($emailCustomer);
        $conn     = $this->bookingModel->getConnection();
        if (!$conn) {
            throw new Exception('Database connection not available');
        }

        $status = $payload['Bookingstatus_pendaftaran'] ?? null;

        if ($status === 'perusahaan' && empty($payload['BookingEmailPerusahaan'])) {
            throw new Exception('Email perusahaan wajib diisi');
        }

        if ($status === 'individu' && empty($payload['BookingEmailPribadi'])) {
            throw new Exception('Email pribadi wajib diisi');
        }

        $emailCustomer = $payload['BookingEmailPribadi'] ?? $payload['BookingEmailPerusahaan'];

        mysqli_begin_transaction($conn);

        try {
            $bookingData = $this->prepareBaseBookingData($payload);

            if ($customer) {
                $this->handleRepeatCustomer($bookingData, $customer);
            } else {
                $this->handleNewCustomer($bookingData, $payload);
            }

            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $emailPribadi     = $payload['BookingEmailPribadi'] ?? '-';
            $emailPerusahaan  = $payload['BookingEmailPerusahaan'] ?? '-';
            insertLogHelper(
                $conn,
                [
                    'user_id'     => $this->bookingModel->getUserInput(),
                    'action'      => 'INSERT',
                    'table_name'  => 'booking',
                    'record_id'   => 0,
                    'description' => "Gagal menyimpan booking dengan data (ID: 0, 
                                                            customer: {$payload['BookingNamaPeserta']}, 
                                                            perusahaan: {$payload['BookingTempatBekerja']}, 
                                                            email_pribadi: {$emailPribadi},
                                                            email_perusahaan: {$emailPerusahaan},
                                                            Error: {$e->getMessage()})",
                    'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]
            );
            throw $e;
        }
    }

    private function prepareBaseBookingData(array $payload): array
    {
        return [
            'kd_booking'  => $this->bookingModel->generateKodeBooking(),
            'id_schedule' => $payload['BookingKdschtraining'],
            'rand_code'   => $this->bookingModel->generateCode(),
            'sebagai'     => $payload['Bookingstatus_pendaftaran'],
            'status'      => 'booking',
            'followup'    => 'none',
            'metode'      => null,
        ];
    }

    private function handleNewCustomer(array $data, array $payload): void
    {
        $data += [
            'nama_peserta'     => $payload['Bookingstatus_pendaftaran'] === 'individu'
                ? $payload['BookingNamaPeserta']
                : null,
            'email_pribadi'    => $payload['BookingEmailPribadi'] ?? null,
            'no_whatsapp'      => $payload['BookingNoWhatsUp'] ?? null,
            'nama_pic'         => $payload['BookingNamaPeserta'],
            'perusahaan'       => $payload['BookingTempatBekerja'] ?? 'Tidak bekerja',
            'email_perusahaan' => $payload['BookingEmailPerusahaan'] ?? $payload['BookingEmailPribadi'],
            'marketing'        => $this->service_marketing->getNextAssignee(),
            'jenis'            => 'NEW'
        ];

        $id = $this->bookingModel->create($data);

        if (!$id) {
            throw new Exception('Gagal menyimpan booking NEW order');
        }

        $this->logSuccessBooking($id, $data);
    }

    private function handleRepeatCustomer(array $data, array $customer): void
    {
        $data += [
            'nama_peserta'     => $customer['nama_lengkap'],
            'email_pribadi'    => $customer['email'],
            'no_whatsapp'      => $customer['no_handphone'],
            'nama_pic'         => $customer['nama_lengkap'],
            'perusahaan'       => $customer['nm_perusahaan'],
            'email_perusahaan' => $customer['email_perusahaan'],
            'marketing'        => $customer['marketing_id'],
            'jenis'            => 'REPEAT'
        ];

        $id = $this->bookingModel->create($data);

        if (!$id) {
            throw new Exception('Gagal menyimpan booking REPEAT order');
        }

        $this->logSuccessBooking($id, $data);
    }
    // ========================== tahan
    private function logSuccessBooking(int $id, array $data): void
    {
        insertLogHelper(
            $this->bookingModel->getConnection(),
            [
                'user_id'     => $this->bookingModel->getUserInput(),
                'action'      => 'INSERT',
                'table_name'  => 'booking',
                'record_id'   => $id,
                'description' => "Sukses menyimpan booking (ID: $id, 
                                    customer: {$data['nama_pic']}, 
                                    perusahaan: {$data['perusahaan']}, 
                                    marketing: {$data['marketing']}, 
                                    jenis: {$data['jenis']})",
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]
        );
    }
}
