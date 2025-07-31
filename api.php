<?php
header('Content-Type: application/json');

// Veritabanı bağlantısı
try {
    $db = new PDO('sqlite:' . __DIR__ . '/urls.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS urls (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE,
        url TEXT
    )");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı bağlantı hatası']);
    exit;
}

// Fonksiyon: Kod üret
function generateCode($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

// Yönlendirme modu (GET ile gelen kısa link kodları)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['code'])) {
    $code = $_GET['code'];
    $stmt = $db->prepare("SELECT url FROM urls WHERE code = :code");
    $stmt->execute([':code' => $code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        header("Location: " . $row['url']);
        exit;
    } else {
        echo "URL bulunamadı!";
        exit;
    }
}

// Kısaltma işlemi (POST metodu ile)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Sadece POST metodu desteklenir']);
    exit;
}

// URL alma (form-data veya raw JSON)
$data = $_POST;
if (empty($data['url'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['url'])) {
        $data['url'] = $input['url'];
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'URL parametresi eksik']);
        exit;
    }
}

$long_url = trim($data['url']);
if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz URL']);
    exit;
}

// URL daha önce kısaltılmış mı?
$stmt = $db->prepare("SELECT code FROM urls WHERE url = :url");
$stmt->execute([':url' => $long_url]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existing) {
    $base_url = "https://" . $_SERVER['HTTP_HOST'] . "/";
    echo json_encode(['short_url' => $base_url . $existing['code']]);
    exit;
}

// Yeni kısa kod üret
do {
    $code = generateCode();
    $stmt = $db->prepare("SELECT 1 FROM urls WHERE code = :code");
    $stmt->execute([':code' => $code]);
} while ($stmt->fetch());

$stmt = $db->prepare("INSERT INTO urls (code, url) VALUES (:code, :url)");
$stmt->execute([':code' => $code, ':url' => $long_url]);

$base_url = "https://" . $_SERVER['HTTP_HOST'] . "/";
echo json_encode(['short_url' => $base_url . $code]);
exit;