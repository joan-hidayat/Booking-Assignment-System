<?php
require_once __DIR__ . '/../models/BookingModel.php';
require_once __DIR__ . '/../services/BookingService.php';
require_once __DIR__ . '/../email/EmailTemplate.php';
require_once __DIR__ . '/../models/EmailQueueModel.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers/app_helper.php';


class BookingController
{
    private $model;
    private $EmailQueueModel;
    private $servicebooking;
    private $logFile;

    public function __construct($conn)
    {
        $this->servicebooking = new BookingService($conn);
        $this->model = new BookingModel($conn);
        $this->logFile = __DIR__ . '/../../app/logs/debug_controller.log';
    }

    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                responseJSON([], 'error', 'Invalid request method', 405);
            }
            logWithTimestamp("=== CONTROLLER BOOKING STORE START === payload -> " . json_encode($_POST), $this->logFile);

            $booking = $this->servicebooking->createBooking($_POST);

            responseJSON([], 'success', 'Terima kasih sudah melakukan booking, anda akan di hubungi oleh admin kami!');
        } catch (Exception $e) {
            logWithTimestamp("ERROR store(): " . $e->getMessage(), $this->logFile);
            responseJSON([], 'error', $e->getMessage(), 500);
        }
    }
}
