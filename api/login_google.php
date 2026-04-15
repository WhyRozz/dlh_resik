<?php

include 'config.php';

header('Content-Type: application/json');



$google_id = $_POST['google_id'];

$email = $_POST['email'];

$nama = $_POST['nama'];



// 🔍 1. Cek apakah user sudah ada berdasarkan google_id

$query = $conn->prepare("SELECT * FROM masyarakat WHERE google_id = ?");

$query->bind_param("s", $google_id);

$query->execute();

$result = $query->get_result();



if ($result->num_rows > 0) {

    // ✅ User sudah login Google sebelumnya

    $user = $result->fetch_assoc();

    echo json_encode([

        "status" => "success",

        "message" => "Login berhasil",

        "data" => [

            "id_masyarakat" => $user["id_masyarakat"],

            "nama" => $user["nama"],

            "email" => $user["email"]

        ]

    ]);

} else {

    // 🔍 2. Cek apakah email sudah terdaftar manual sebelumnya

    $query_email = $conn->prepare("SELECT * FROM masyarakat WHERE email = ?");

    $query_email->bind_param("s", $email);

    $query_email->execute();

    $result_email = $query_email->get_result();



    if ($result_email->num_rows > 0) {

        // 🧩 Update google_id untuk akun lama (biar bisa login pakai Google ke depannya)

        $user = $result_email->fetch_assoc();

        $update = $conn->prepare("UPDATE masyarakat SET google_id = ?, updated_at = NOW() WHERE email = ?");

        $update->bind_param("ss", $google_id, $email);

        $update->execute();



        echo json_encode([

            "status" => "success",

            "message" => "Login berhasil (akun lama terhubung dengan Google)",

            "data" => [

                "id_masyarakat" => $user["id_masyarakat"],

                "nama" => $user["nama"],

                "email" => $user["email"]

            ]

        ]);

    } else {

        // 🆕 Kalau benar-benar akun baru

        $insert = $conn->prepare("INSERT INTO masyarakat (nama, email, google_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");

        $insert->bind_param("sss", $nama, $email, $google_id);



        if ($insert->execute()) {

            $user_id = $conn->insert_id;

            echo json_encode([

                "status" => "success",

                "message" => "Akun baru berhasil dibuat",

                "data" => [

                    "id_masyarakat" => $user_id,

                    "nama" => $nama,

                    "email" => $email

                ]

            ]);

        } else {

            echo json_encode([

                "status" => "error",

                "message" => "Gagal menyimpan data"

            ]);

        }

    }

}

?>