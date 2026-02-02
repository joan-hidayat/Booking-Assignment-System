<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<style>
    /* ====== BODY & LAYOUT ====== */
    .body-silabus {
        width: 98.4%;
    }

    .silabus {
        width: 96%;
        border-radius: 20px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        margin-bottom: 10px;
        padding: 10px;
    }

    .silabus-card {
        background: #fff;
        border-radius: 16px;
        padding-top: 10px;
        width: 100%;
        min-height: 450px;
        text-align: center;
        animation: fadeIn 0.4s ease-in-out;
    }

    .silabus-card h2 {
        margin-bottom: 15px;
        text-align: center;
    }

    /* ====== FORM ====== */
    .form-container {
        max-width: 900px;
        margin: 20px auto;
        display: grid;
        gap: 15px;
    }

    .form-images,
    .form-group {
        display: grid;
        grid-template-columns: 150px 1fr;
        align-items: center;
    }

    .form-group label {
        font-weight: bold;
        margin-right: 10px;
        text-align: right;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
        appearance: none;
        -moz-appearance: none;
        -webkit-appearance: none;
    }

    .form-group select {
        background: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='gray'%3E%3Cpath d='M4 6l4 4 4-4z'/%3E%3C/svg%3E") no-repeat right 10px center / 12px auto;
        padding-right: 30px;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #007bff;
        outline: none;
    }

    .input-tanggal,
    .input-harga {
        padding: 0 15px 0 0;
    }

    .list-harga {
        display: grid;
        grid-template-columns: 150px 1fr;
    }

    /* ====== FILE INPUT ====== */
    .logo-file-input,
    .cover-pretest-file-input input[type="file"],
    .logo-file-input input[type="file"] {
        display: none;
    }

    .logo-file-input {
        position: relative;
        display: inline-block;
        margin-bottom: 0;
        border: 1.5px solid white;
        border-radius: 8px;
    }

    .cover-pretest-file-label,
    .logo-file-label {
        display: inline-block;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }

    .cover-pretest-file-label:hover,
    .logo-file-label:hover {
        background: #0056b3;
    }

    .cover-pretest-preview,
    .logo-preview {
        justify-items: center;
    }

    .cover-pretest-preview img,
    .logo-preview img {
        max-width: 100%;
        max-height: 300px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* ====== BUTTONS ====== */
    .btn {
        display: inline-block;
        padding: 6px 14px;
        font-size: 14px;
        font-weight: bold;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-edit {
        background-color: #fbbf24;
        color: #fff;
    }

    .btn-edit:hover {
        background-color: #f59e0b;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-delete {
        background-color: #ef4444;
        color: #fff;
    }

    .btn-delete:hover {
        background-color: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .add {
        border: 1px solid #ccc;
        color: white;
        background-color: #4caf50;
        border-radius: 5px;
        text-decoration: none;
        padding: 5px 20px;
    }

    .add:hover {
        background-color: #155724;
    }

    /* ====== FORM ACTIONS ====== */
    .form-actions {
        grid-column: 1 / -1;
        text-align: right;
    }

    .form-actions button {
        background: #007bff;
        color: #fff;
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .form-actions button:hover {
        background: #0056b3;
    }

    .card {
        max-width: 100%;
        overflow-x: hidden;
        /* supaya ga luber */
    }

    /* #silabusTable {

        
    } */

    /* ====== TABLE ====== */
    #silabusTable {
        width: 100% !important;
        white-space: nowrap;
        border-collapse: collapse;
        margin-top: 20px;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        font-size: 14px;
    }

    #silabusTable th {
        background: #007bff;
        color: #fff;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
    }

    #silabusTable td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        color: #333;
    }

    #silabusTable tr:hover td {
        background: #f1f9ff;
    }

    #silabusTable tr:nth-child(even) {
        background: #f9f9f9;
    }

    #silabusTable .btn-edit {
        background: #ffb74d;
    }

    #silabusTable .btn-edit:hover {
        background: #ffa726;
    }

    #silabusTable .btn-delete {
        background: #e57373;
    }

    #silabusTable .btn-delete:hover {
        background: #ef5350;
    }

    /* ====== DATATABLE ====== */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 6px 10px;
        margin-left: 8px;
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 4px 8px;
        margin: 0 5px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 4px 10px;
        margin: 2px;
        border-radius: 6px;
        border: none;
        background: #f1f1f1;
        cursor: pointer;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        color: white !important;
        font-weight: bold;
    }

    /* ====== MODAL ====== */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        justify-items: center;
        align-content: center;
        z-index: 1000;
    }

    .modal.show {
        animation: fadeIn 0.4s ease-in-out;
    }

    .modal form {
        background: #fff;
        border-radius: 8px;
        padding: 20px 25px;
        width: 850px;
        max-width: 90%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .modal form .sparator {
        display: flex;
        border-bottom: 1px solid #ccc;
        height: 30px;
        margin-bottom: 5px;
        padding: 0;
    }

    .modal form .sparator .headerEdit {
        width: 100%;
        font-size: 20px;
    }

    .modal form .sparator .closeEdit {
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-radius: 5px;
        background-color: #ef4444;
        color: #fff;
        cursor: pointer;
    }

    .btn-group {
        text-align: center;
    }

    .modal form button {
        padding: 8px 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .modal form button[type="submit"] {
        background: #4caf50;
        color: #fff;
        margin-right: 10px;
    }

    .modal form button#closeEdit {
        background: #ccc;
        color: #333;
    }

    /* ====== ALERT ====== */
    .alert {
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background: red;
        color: #721c24;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
    }

    /* ====== CHECKBOX ====== */
    input[type="checkbox"] {
        accent-color: #007bff;
        transform: scale(1.3);
        cursor: pointer;
        margin-right: 5px;
        appearance: auto !important;
        -webkit-appearance: auto !important;
        -moz-appearance: auto !important;
    }

    /* ====== SCHEDULE ROOM ====== */
    .sch-room-header {
        max-width: 710px;
        width: 100%;
        box-sizing: border-box;
    }

    .schedule-room {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #007bff;
        padding: 10px 15px;
        border: 1px solid #ddd;
        cursor: pointer;
        color: white;
    }

    .schedule {
        font-size: 18px;
        font-weight: bold;
    }

    /* Expandable content */
    .satu {
        height: 43px;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        transition: height 0.4s ease;
        border-radius: 15px;
        border: 1px solid #ccc;
    }

    .satu.active {
        height: 700px;
        max-height: 1000px;
        visibility: visible;
    }

    .satu.active .arrow {
        transform: rotate(180deg);
    }

    .dua {
        width: 100%;
        display: flex;
        align-items: center;
        background: #007bff;
        padding: 12px 15px;
        cursor: pointer;
        color: white;
        margin-bottom: 5px;
        justify-content: space-between;
    }

    .label_jadwal {
        font-size: 16px;
        font-weight: bold;
    }

    .arrow {
        display: inline-block;
        margin-right: 30px;
        font-size: 16px;
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .tiga {
        width: 100%;
        overflow: auto;
    }

    .hapus,
    .edit {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }

    .hapus:hover svg,
    .edit:hover svg {
        opacity: 0.7;
    }

    /* ====== DETAIL ROW ====== */
    td.details-control {
        background: url('https://cdn.datatables.net/1.13.4/images/details_open.png') no-repeat center center;
        cursor: pointer;
    }

    tr.shown td.details-control {
        background: url('https://cdn.datatables.net/1.13.4/images/details_close.png') no-repeat center center;
    }

    .dataTables_child_wrapper {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: all 0.3s ease-in-out;
    }

    .dataTables_child_wrapper.open {
        max-height: 500px;
        opacity: 1;
    }

    /* ====== TABLE SCH ====== */
    .tbl-sch-room {
        width: 100%;
        margin-top: 10px;
        overflow-x: auto;
        max-height: 0;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .tbl-sch-room.active {
        max-height: 1000px;
        opacity: 1;
        visibility: visible;
    }

    .table-sch {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .table-sch th,
    .table-sch td {
        border: 1px solid #ccc;
        padding: 8px 10px;
        text-align: center;
        white-space: nowrap;
    }

    .table-sch thead {
        background: #f0f0f0;
    }

    .table-sch th {
        font-weight: bold;
    }

    /* ====== ANIMATIONS ====== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* ====== ROOM METODE ====== */
    .room-metode {
        padding: 5px;
        border-radius: 5px;
    }

    .room-metode label {
        font-weight: bold;
        display: block;
        text-align: center;
    }

    .metode {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 5px;
    }

    .tgl_metode {
        display: grid;
        grid-template-columns: 150px repeat(3, 1fr);
    }

    .harga {
        display: grid;
        grid-template-columns: 150px repeat(3, 1fr);
        /* max-height: 0; */
        /* opacity: 0; */
        /* visibility: hidden; */
        /* overflow: hidden;
        transition: all 0.5s ease; */
    }

    /* .harga.show {
        max-height: 1000px;
        opacity: 1;
        visibility: visible;
    } */
</style>
<style>
    /* Overlay */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        /* default hidden */
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Modal Box */
    .modal-box {
        background: #fff;
        width: 600px;
        max-width: 90%;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.3s ease-out;
    }

    /* Header */
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #eee;
        padding-bottom: .5rem;
        margin-bottom: 1rem;
    }

    .modal-header h3 {
        margin: 0;
    }

    .closepopup {
        background: #e74c3c;
        border: none;
        color: #fff;
        font-weight: bold;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
    }

    .closepopup:hover {
        background: #c0392b;
    }

    /* Body */
    .modal-body {
        margin-bottom: 1rem;
    }

    .roomCard {
        border: 2px dotted #ccc;
        text-align: center;
        padding: 1rem;
        border-radius: 6px;
    }

    .previewfoto {
        height: 200px;
        width: 100%;
        margin-bottom: 10px;
    }

    .previewfoto img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    .btnfoto .upload {
        display: inline-block;
        padding: 10px 15px;
        background: #3498db;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    .btnfoto .upload:hover {
        background: #2980b9;
    }

    /* Progress */
    .progress {
        width: 100%;
        background: #f1f1f1;
        border-radius: 4px;
        overflow: hidden;
        margin: 10px 0;
    }

    .progress_bar {
        width: 0%;
        height: 20px;
        background: linear-gradient(90deg, #3498db, #2ecc71);
        transition: width 0.4s ease;
    }

    /* Footer */
    .modal-footer {
        text-align: right;
        border-top: 1px solid #eee;
        padding-top: .5rem;
    }

    .btn {
        padding: 8px 16px;
        margin: 5px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    .btn.cancel {
        background: #e67e22;
        color: white;
    }

    .btn.cancel:hover {
        background: #d35400;
    }

    .btn.reset {
        background: #3498db;
        color: white;
    }

    .btn.reset:hover {
        background: #2980b9;
    }

    .btn.submit {
        background: #27ae60;
        color: white;
    }

    .btn.submit:hover {
        background: #1e8449;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<body class="body-silabus">
    <section class="silabus">
        <div class="silabus-card">
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
                    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <div class="card" style="width: 100%;">
                <h2>Data Booking</h2>
                <table id="silabusTable" class="display nowrap" style="width:100%; ">
                    <thead style="background-color: #007bff;color: #fff;border-radius: 10px;">
                        <tr>
                            <th>No</th>
                            <th>Action</th>
                            <th>Judul Training</th>
                            <th>Tanggal Training</th>
                            <th>Metode</th>
                            <th>Marketing</th>
                            <th>Sebagai</th>
                            <th>Nama Calon Peserta / PIC</th>
                            <th>Email Perusahaan</th>
                            <th>Email Pibadi</th>
                            <th>No whatsup</th>
                            <th>Status Pendaftaran</th>
                            <th>Followup</th>
                            <th>Tanggal input</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data)): ?>
                            <?php $no = 1;
                            foreach ($data as $menu): ?>
                                <tr>
                                    <!-- <td><?= $no++ ?></td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-warning btn-edit"
                                            data-id="<?= $row['id_persyaratan'] ?>"
                                            data-id_silabus="<?= $row['id_silabus'] ?>"
                                            data-judul="<?= htmlspecialchars($row['title']) ?>"
                                            data-list="<?= htmlspecialchars($row['list']) ?>">
                                            Edit
                                        </button>
                                        <a href="<?= ADMIN_URL ?>?page=training&tab=persyaratan&action=delete&id=<?= $row['id_persyaratan'] ?>" data-delete="true" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus data ini?')" style="text-decoration: none;"> Hapus </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['kd_persyaratan']) ?></td>
                                    <td><?= htmlspecialchars($row['list']) ?></td>
                                    <td><?= date('d-M-Y', strtotime(htmlspecialchars($row['tgl_input']))) ?></td>
                                    <td><?= htmlspecialchars($row['user_input']) ?></td>
                                    <td><?= date('d-M-Y', strtotime(htmlspecialchars($row['tgl_update']))) ?></td>
                                    <td><?= htmlspecialchars($row['user_update']) ?></td> -->
                                    <!-- ========================================= yang bawah Data baru -->
                                    <td><?= $no++ ?></td>
                                    <td class="roomaction">
                                        <?php if (
                                            $menu["marketing"] == $menu["marketing"] //$_SESSION['id'] 
                                            && $menu['followup'] == 'confirm via whatsApp : finish'
                                        ) { ?>
                                            <div class="btn btn-edit" onclick="frm_nego('<?= $menu['kd_booking']; ?>')">NEGOSIASI</div>
                                        <?php } else { ?>
                                            <div class="btn btn-edit" style="background-color: grey;">NEGOSIASI</div>
                                        <?php } ?>
                                    </td>
                                    <td><?= $menu['training_schedule'] ?></td>
                                    <td><?= $menu['d_mulai'] . " " . $menu['m_mulai'] . " " . $menu['y_mulai'] . " - " . $menu['d_selesai'] . " " . $menu['m_selesai'] . " " . $menu['y_selesai'] ?></td>
                                    <td><?php if (!empty($menu['offline'])) {
                                            echo $menu['offline'];
                                        } elseif (!empty($menu['online'])) {
                                            echo $menu['online'];
                                        } elseif (!empty($menu['blended'])) {
                                            echo $menu['blended'];
                                        } ?></td>
                                    <td><?= $menu['nama_marketing'] ?> </td><!-- ganti dengan nama marketing -->
                                    <td><?= $menu['sebagai'] ?></td>
                                    <td><?php if (!empty($menu['nama_peserta'])) {
                                            echo $menu['nama_peserta'];
                                        } else {
                                            echo $menu['nama_pic'];
                                        } ?></td>
                                    <!-- ================= kirim email perusahaan  -->
                                    <?php if (
                                        $menu["marketing"] == $menu["marketing"] //$_SESSION['id'] 
                                        && $menu["sebagai"] == 'perusahaan' && $menu['followup'] == 'confirm via whatsApp : finish'
                                    ) { ?>
                                        <td class=" ">
                                            <div class="sendEmail btn btn-edit" onclick="sendEmailPerusahaan('<?= $menu['kd_booking']; ?>')"><?= $menu["email_perusahaan"] ?></div>
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <div class="sendEmail btn btn-edit" style="background-color: grey; cursor: pointer;">
                                                <?= $menu["email_perusahaan"] ?>
                                            </div>
                                        </td>
                                    <?php } ?>
                                    <!-- ================= kirim email pribadi  -->
                                    <?php
                                    if (
                                        $menu["marketing"] == $menu["marketing"] //$_SESSION['id'] 
                                        && $menu['followup'] == 'confirm via whatsApp : finish'
                                    ) {
                                    ?>
                                        <td class=" ">
                                            <div class="sendEmail btn btn-edit" onclick="sendEmailPerorangan('<?= $menu['kd_booking']; ?>')">
                                                <?= $menu["email_pribadi"] ?>
                                            </div>
                                        </td>
                                    <?php } else { ?>
                                        <td class="">
                                            <div class="sendEmail btn btn-edit" style="background-color: grey; cursor: pointer;">
                                                <?= $menu["email_pribadi"] ?>
                                            </div>
                                        </td>
                                    <?php } ?>
                                    <!-- ================ konfirm kirim via telp -->
                                    <td><?= $menu['no_telp'] ?>
                                        <div style="display: flex;">
                                            <?php

                                            if (
                                                $menu["marketing"] == $menu["marketing"] //$_SESSION['user_id']
                                                && $menu['followup'] == 'none'
                                            ) {
                                            ?>
                                                <div class="btn btn-edit" onclick="followupWaStart('<?= $menu['no_telp'] ?>','<?= $menu['kd_booking']; ?>')">
                                                    start
                                                </div>
                                                <div class="btn btn-edit" style="background-color: grey; cursor: pointer;">
                                                    finish
                                                </div>
                                            <?php } elseif (
                                                $menu["marketing"] == $menu["marketing"] //$_SESSION['user_id'] 
                                                && $menu['followup'] == 'confirm via whatsApp : start'
                                            ) { ?>
                                                <div class="btn btn-edit" style="background-color: grey; cursor: pointer;">
                                                    start
                                                </div>
                                                <div class="btn btn-edit" onclick="followupWaFinish('<?= $menu['kd_booking']; ?>')">
                                                    finish
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td><?= $menu['_status'] ?></td>
                                    <td><?= $menu['followup'] ?></td>
                                    <td><?= $menu['booking_date'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="modal-overlay" id="upload_document_finish">
                <div class="modal-box">
                    <div class="modal-header">
                        <h3>Upload Document Finish</h3>
                        <button class="closepopup" onclick="ClosePopup()" type="button"><strong>X</strong></button>
                    </div>

                    <form id="document_finish" class="document_finish" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="roomCard">
                                <div class="drop_boxfotologo">
                                    <div class="previewfoto">
                                        <img id="uploadPreview" class="uploadPreview" />
                                        <p id="namebackground"></p>
                                    </div>
                                    <input type="text" hidden name="kd" id="kd">
                                    <input type="file" hidden accept=".jpg,.jpeg,.png" name="foto" id="foto" onchange="previewdocument()">
                                    <div id="btnfoto" class="btnfoto">
                                        <div class="btn upload" id="choose-documentfinish">upload bukti followup via chat</div>
                                    </div>
                                    <small id="msg-uploadfoto" class="error-msg"></small>
                                    <p>Files Supported: PNG, JPG, JPEG - Max size: 1 Mb</p>
                                </div>
                            </div>

                            <div class="progress_wrapper" id="process" style="display:none;">
                                <div class="progress">
                                    <div class="progress_bar"></div>
                                </div>
                            </div>

                            <span id="success_message"></span>
                        </div>

                        <div class="modal-footer">
                            <button class="btn cancel" type="button">Cancel</button>
                            <button class="btn reset" type="reset">Reset</button>
                            <button type="submit" name="submit" class="btn submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Modal Edit -->
            <!-- <div id="editModal" class="modal">
                <form action="<?= ADMIN_URL ?>?page=training&tab=persyaratan&action=update" method="POST" class="form-container" enctype="multipart/form-data">
                    <div class="sparator">
                        <div class="headerEdit"><label>Edit Persyaratan</label></div>
                        <div class="closeEdit" id="closeEdit">
                            <span>X</span>
                        </div>
                    </div>
                    <input type="hidden" name="edit-id" id="id-edit">
                    <div class="form-group">
                        <label for="edit-judulsilabus">Judul Silabus</label>
                        <input list="edit-judulsilabusOptions" id="edit-judulsilabus" name="edit-judulsilabus" placeholder="Pilih judul silabus...">
                        <datalist id="edit-judulsilabusOptions">
                            <?php foreach ($juduls as $judul): ?>
                                <option data-id="<?= $judul['id'] ?>" value="<?= htmlspecialchars($judul['judul_silabus']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <input type="text" id="edit-judulsilabus_id" name="edit-judulsilabus_id">
                    </div>
                    <div class="form-group">
                        <label for="edit-isipersyaratan">Persyaratan</label>
                        <input type="text" name="edit-isipersyaratan" id="edit-isipersyaratan" class="isipersyaratan">
                    </div>
                    <div style="height: 50px;"></div>

                    <div class="form-actions">
                        <button type="submit">update</button>
                    </div>
                </form>
            </div> -->
        </div>
    </section>
</body>
<script>
    // $(document).ready(function() {
    //     console.log("API URL:", "<?= API_URL ?>pendaftaran_api.php?action=ajaxList");
    //     initDataTable('#myTable', '<?= API_URL ?>pendaftaran_api.php?action=ajaxList', [{
    //             data: 'no'
    //         },
    //         {
    //             data: 'negosiasi',
    //             orderable: false,
    //             searchable: false
    //         },
    //         {
    //             data: 'judul_training'
    //         },
    //         {
    //             data: 'tanggal_training'
    //         },
    //         {
    //             data: 'netode'
    //         },
    //         {
    //             data: 'maeketing'
    //         },
    //         {
    //             data: 'sebagai'
    //         },
    //         {
    //             data: 'nama_pic'
    //         },
    //         {
    //             data: 'email_perusahaan'
    //         },
    //         {
    //             data: 'email_pribadi'
    //         },
    //         {
    //             data: 'no_whatsapp'
    //         },
    //         {
    //             data: 'status_pendaftaran'
    //         },
    //         {
    //             data: 'followup'
    //         },
    //         {
    //             data: 'tanggal_input'
    //         }
    //         // ,
    //         // {
    //         //     data: 'tanggal_cancle'
    //         // },
    //         // {
    //         //     data: 'tanggal_reschedule'
    //         // },
    //         // {
    //         //     data: 'tanggal_finish'
    //         // },
    //         // {
    //         //     data: 'aksi',
    //         //     orderable: false,
    //         //     searchable: false
    //         // }
    //     ]);
    // });
</script>
<script>
    function formatCurrencyDisplay(raw) {
        if (!raw) return "Rp 0,00";
        const n = parseInt(raw, 10);
        if (isNaN(n) || n === 0) return "Rp 0,00";
        return "Rp " + n.toLocaleString("id-ID") + ",00";
    }

    document.addEventListener("DOMContentLoaded", () => {
        // document.addEventListener("DOMContentLoaded", () => {
        function formatTanggal(tanggalISO) {
            const bulanIndo = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];
            const [tahun, bulan, hari] = tanggalISO.split("-");
            return `${hari} ${bulanIndo[parseInt(bulan) - 1]} ${tahun}`;
        }

    });

    function followupWaStart(wa, id) {
        var no = 'https://wa.me/' + wa;
        let formData = new FormData();
        formData.append('kd', id);
        formData.append('update', 'wa start');
        formData.append('wa', wa);
        $.ajax({
            type: 'POST',
            data: formData,
            url: '../functions/update_status_followup.php',
            cache: false,
            processData: false,
            contentType: false,
            success: function(msg) {
                if (msg == '"success"') {
                    alert('Memulai untuk percakapan dengan calon user')
                    window.open(no, "_blank");
                } else {
                    alert(msg)
                }
                location.reload();
            }
        });
    }

    function followupWaFinish(kd) {
        document.getElementById("kd").value = kd; //Popup show
        document.getElementById("upload_document_finish").style.display = "flex";
    }

    function ClosePopup() {
        document.getElementById("upload_document_finish").style.display = "none";
    }

    $("#choose-documentfinish").click(function() {
        $(this).parents().find("#foto").click();
    });

    function previewdocument() {
        const fileInput = document.getElementById("foto");
        const file = fileInput.files[0];
        const previewImg = document.getElementById("uploadPreview");
        const msgUpload = document.getElementById("msg-uploadfoto");

        if (!file) {
            previewImg.src = "";
            msgUpload.textContent = "Silahkan pilih gambar terlebih dahulu";
            msgUpload.style.color = "red";
            return;
        }

        const limit = 1 * 1024 * 1024; // 1MB
        const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
        const fileSize = file.size;
        const fileName = file.name;

        // ✅ Validasi ukuran
        if (fileSize > limit) {
            alert("Ukuran file terlalu besar! Maksimal 1MB.");
            fileInput.value = "";
            previewImg.src = "";
            return;
        }

        // ✅ Validasi panjang nama
        if (fileName.length > 80) {
            alert("Nama file terlalu panjang, maksimal 80 karakter.");
            fileInput.value = "";
            previewImg.src = "";
            return;
        }

        // ✅ Validasi format file
        if (!allowedTypes.includes(file.type)) {
            alert("Format file tidak didukung. Hanya JPG, JPEG, atau PNG.");
            fileInput.value = "";
            previewImg.src = "";
            return;
        }

        // ✅ Convert ke WebP & tampilkan preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.src = e.target.result;

            img.onload = function() {
                const canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);

                // Convert ke WebP kualitas 0.8
                canvas.toBlob(
                    function(blob) {
                        // Preview hasil webp
                        previewImg.src = URL.createObjectURL(blob);

                        // Simpan blob ke input hidden (opsional)
                        const hiddenInput = document.getElementById("foto_webp");
                        if (hiddenInput) {
                            const newFile = new File([blob], "upload.webp", {
                                type: "image/webp"
                            });
                            const dt = new DataTransfer();
                            dt.items.add(newFile);
                            fileInput.files = dt.files; // replace file asli dgn webp
                        }

                        msgUpload.textContent = "";
                    },
                    "image/webp",
                    0.8
                );
            };
        };
        reader.readAsDataURL(file);
    }

    document.getElementById("document_finish").addEventListener("submit", function(event) {
        event.preventDefault(); // cegah submit normal
        validateDocument();
    });

    function validateDocument() {
        const imageInput = document.getElementById("foto");
        const image = imageInput.files[0];
        const id = document.getElementById("kd").value;
        const msgUpload = document.getElementById("msg-uploadfoto");
        let hasError = false;

        // ✅ Validasi file ada atau tidak
        if (!image) {
            setError(imageInput, msgUpload, "Silahkan isi gambar bukti chat via WA kamu");
            hasError = true;
        } else {
            clearError(imageInput, msgUpload);

            // ✅ Validasi ukuran max 1MB
            if (image.size > 1024 * 1024) {
                setError(imageInput, msgUpload, "Ukuran gambar maksimal 1MB");
                hasError = true;
            }

            // ✅ Validasi tipe file (hanya JPG, PNG, WEBP)
            const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
            if (!allowedTypes.includes(image.type)) {
                setError(imageInput, msgUpload, "Format file harus JPG, PNG, atau WEBP");
                hasError = true;
            }
        }

        if (hasError) return;

        // ✅ Convert ke WebP sebelum upload
        convertToWebP(image, function(webpBlob) {
            const formData = new FormData();
            formData.append("kd", id);
            formData.append("foto", webpBlob, "upload.webp"); // nama file dipaksa .webp
            formData.append("update", "wa finish");

            // ✅ Kirim via AJAX
            $.ajax({
                type: "POST",
                url: "../functions/update_status_followup.php",
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const res = JSON.parse(response);

                        if (res.status === "success") {
                            alert(res.message);
                            location.reload();
                        } else {
                            setError(imageInput, msgUpload, res.message || "Terjadi kesalahan.");
                        }
                    } catch (e) {
                        console.error("Parsing error:", e);
                        console.error("Invalid JSON:", response);
                        alert("Respon server tidak valid.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("Terjadi kesalahan saat mengirim data.");
                }
            });
        });
    }

    // ✅ Helper: Set error style
    function setError(input, msgElement, message) {
        input.style.backgroundColor = "rgb(255, 228, 228)";
        msgElement.textContent = message;
        msgElement.style.color = "red";
    }

    // ✅ Helper: Clear error style
    function clearError(input, msgElement) {
        input.style.backgroundColor = "#ffffff";
        msgElement.textContent = "";
    }

    // ✅ Helper: Convert image ke WebP
    function convertToWebP(file, callback) {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = function(e) {
            const img = new Image();
            img.src = e.target.result;

            img.onload = function() {
                const canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);

                // ✅ Convert ke Blob WebP kualitas 0.8
                canvas.toBlob(
                    function(blob) {
                        callback(blob);
                    },
                    "image/webp",
                    0.8
                );
            };
        };
    }

    function sendEmailPerusahaan(id) {
        let formData = new FormData();
        formData.append('kd_booking', id);
        $.ajax({
            type: 'POST',
            data: formData,
            url: '../mailer/verif_booking.php',
            cache: false,
            processData: false,
            contentType: false,
            success: function(msg) {
                alert(msg);
                location.reload();
            }
        });
    }

    function sendEmailPerorangan(id) {
        let formData = new FormData();
        formData.append('kd_booking', id);
        // /mailer/verif_booking.php'
        $.ajax({
            type: 'POST',
            data: formData,
            url: '../mailer/verif_booking.php',
            cache: false,
            processData: false,
            contentType: false,
            success: function(msg) {
                alert(msg)
                location.reload();
            }
        });
    }

    document.querySelectorAll("input[list]").forEach(input => {
        input.addEventListener("input", function() {
            let val = this.value;
            let listId = this.getAttribute("list");
            let opts = document.getElementById(listId).options;

            // hidden input = id input + "_id"
            let hidden = document.getElementById(this.id + "_id");
            if (hidden) hidden.value = ""; // reset default

            for (let i = 0; i < opts.length; i++) {
                if (opts[i].value === val) {
                    if (hidden) hidden.value = opts[i].dataset.id;
                    break;
                }
            }
        });
        const checkboxes = document.querySelectorAll('input[name="metode[]"]');
        const hargaSection = document.querySelector('.harga');

        checkboxes.forEach(cb => {
            cb.addEventListener("change", function() {
                if (this.checked) {
                    // Uncheck semua yang lain
                    checkboxes.forEach(other => {
                        if (other !== this) {
                            other.checked = false;
                        }
                    });
                }

                // Tampilkan harga dengan animasi
                const adaYangDipilih = Array.from(checkboxes).some(c => c.checked);
                if (adaYangDipilih) {
                    hargaSection.classList.add("show");
                } else {
                    hargaSection.classList.remove("show");
                }
            });
        });
    });

    $(document).ready(function() {

        // ==========================
        // DataTable Initialization
        // ==========================


        $('#silabusTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            scrollX: true,
            responsive: {
                details: {
                    type: 'column',
                    target: 0, // klik di kolom "No"
                    renderer: function(api, rowIdx, columns) {
                        var data = $.map(columns, function(col, i) {
                            if (i === 0 || col.hidden === false) {
                                return '';
                            }
                            return `<tr>
                                <td style="font-weight:bold;">${col.title}:</td>
                                <td>${col.data}</td>
                            </tr>`;
                        }).join('');
                        return data ? $('<table/>').append(data) : false;
                    }
                }
            },
            autoWidth: false, // biar ga nambah width otomatis
            columnDefs: [{
                className: 'control',
                orderable: false,
                targets: 0
            }],
            order: [
                [1, 'asc']
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                emptyTable: "Belum ada data tersedia",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "›",
                    previous: "‹"
                }
            }
        });
    });
</script>