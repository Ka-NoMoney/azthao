<?php

set_time_limit(0);

// Thông tin kết nối đến cơ sở dữ liệu
$servername = "127.0.0.1";
$username = "taobills_tu";
$password = "ug^=ax1FUOg&";
$dbname = "taobills_tu";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập các ký tự kết nối UTF8
$conn->set_charset("utf8");

// Lấy danh sách các bảng trong cơ sở dữ liệu
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

// Tên tệp tin để lưu trữ dữ liệu xuất
$outputFile = 'backup.sql';

// Mở tệp tin để ghi
if ($handle = fopen($outputFile, 'w')) {
    // Xuất dữ liệu của mỗi bảng ra SQL và ghi vào tệp tin
    foreach ($tables as $table) {
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            fwrite($handle, "-- Dumping table: $table\n");

            while ($row = $result->fetch_assoc()) {
                fwrite($handle, "INSERT INTO $table (");
                $fields = array_keys($row);
                fwrite($handle, implode(", ", $fields));
                fwrite($handle, ") VALUES (");
                $values = array_map(function ($value) use ($conn) {
                    return "'" . $conn->real_escape_string($value) . "'";
                }, array_values($row));
                fwrite($handle, implode(", ", $values));
                fwrite($handle, ");\n");
            }
        } else {
            fwrite($handle, "-- Không có dữ liệu trong bảng: $table\n");
        }
    }

    // Đóng tệp tin
    fclose($handle);

    echo "Xuất dữ liệu thành công vào file: $outputFile\n";
} else {
    echo "Không thể mở tệp tin để ghi.\n";
}

// Đóng kết nối
$conn->close();
