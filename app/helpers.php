<?php
function env(string $key, mixed $default = null): mixed
{
    static $values = null;
    if ($values === null) {
        $file = BASE_PATH . '/.env';
        $values = is_file($file) ? (parse_ini_file($file, false, INI_SCANNER_TYPED) ?: []) : [];
    }
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        $value = $values[$key] ?? $default;
    }
    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string
{
    $configured = trim((string) env('APP_URL', ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return $script === '/' || $script === '.' ? '' : rtrim($script, '/');
}

function url(string $route = 'home', array $params = []): string
{
    return base_url() . '/index.php?' . http_build_query(array_merge(['r' => $route], $params));
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function redirect(string $route = 'home', array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string) ($_POST['_token'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan kembali dan coba lagi.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): array
{
    if (!is_logged_in()) {
        flash('warning', 'Silakan login terlebih dahulu.');
        redirect('login');
    }
    return current_user();
}

function require_role(string $role): array
{
    $user = require_login();
    if (($user['role'] ?? '') !== $role) {
        flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect($user['role'] === 'seller' ? 'seller.dashboard' : 'home');
    }
    return $user;
}

function money(int|float|string|null $amount): string
{
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

function format_datetime(?string $value, string $format = 'd M Y, H:i'): string
{
    if (!$value) return '-';
    return date($format, strtotime($value));
}

function status_label(string $status): string
{
    return [
        'pending' => 'Menunggu konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'preparing' => 'Sedang disiapkan',
        'ready' => 'Siap diambil',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'rejected' => 'Ditolak',
        'paid' => 'Dibayar',
        'unpaid' => 'Belum dibayar',
        'refunded' => 'Dikembalikan',
    ][$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function payment_status_label(string $status): string
{
    return [
        'pending' => 'Menunggu pembayaran',
        'unpaid' => 'Bayar saat pickup',
        'paid' => 'Dibayar',
        'refunded' => 'Dikembalikan',
        'failed' => 'Gagal',
    ][$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function payment_status_badge(string $status): string
{
    $label = payment_status_label($status);
    return '<span class="badge ring-1 ring-inset ' . status_classes($status) . '">' . e($label) . '</span>';
}

function status_classes(string $status): string
{
    return [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'confirmed' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'preparing' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'ready' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'completed' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'unpaid' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'refunded' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ][$status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
}

function status_badge(string $status): string
{
    return '<span class="badge ring-1 ring-inset ' . status_classes($status) . '">' . e(status_label($status)) . '</span>';
}

function product_image(?string $image, string $name = 'Menu'): string
{
    $src = trim((string) $image);
    if ($src === '') {
        $src = asset('images/food-placeholder.svg');
    }
    return '<img class="food-image" loading="lazy" src="' . e($src) . '" alt="' . e($name) . '" onerror="this.onerror=null;this.src=' . "'" . e(asset('images/food-placeholder.svg')) . "'" . ';">';
}

function get_store_for_seller(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM stores WHERE seller_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function cart_items(): array
{
    return $_SESSION['cart']['items'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_items()));
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_products(): array
{
    $items = cart_items();
    if (!$items) return [];
    $ids = array_values(array_filter(array_map('intval', array_keys($items))));
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT p.*, c.name AS category_name, s.name AS store_name, s.id AS store_id, s.is_open, s.location
        FROM products p JOIN stores s ON s.id = p.store_id
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $result = [];
    foreach ($rows as $row) {
        $row['cart_quantity'] = (int) ($items[$row['id']] ?? 0);
        $result[] = $row;
    }
    return $result;
}

function cart_subtotal(array $products): float
{
    return array_reduce($products, fn($carry, $item) => $carry + ((float) $item['price'] * (int) $item['cart_quantity']), 0.0);
}

function order_code(): string
{
    return 'UIB-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function remember_old_input(): void
{
    $_SESSION['_old'] = array_intersect_key($_POST, array_flip(['name', 'email', 'phone', 'store_name', 'description', 'location', 'product_name', 'price', 'stock', 'image_url', 'category_id', 'customer_note']));
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function safe_return_route(string $fallback = 'home'): string
{
    $route = (string) ($_POST['return_to'] ?? $fallback);
    return preg_match('/^[a-z.]+$/', $route) ? $route : $fallback;
}
