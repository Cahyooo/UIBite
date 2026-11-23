<?php

/**
 * Public landing page for a buyer's pickup QR — reachable without login.
 *
 * The secret pickup_code embedded in the QR is the only proof of
 * authorization needed (only the buyer who owns the order ever sees it),
 * so whoever scans it — typically the seller at the counter — can confirm
 * pickup without first logging in on that device. Re-opening an already
 * completed link is safe: it simply re-shows the success message instead
 * of erroring out.
 */
function render_pickup_confirm(): void
{
    $orderId = (int) ($_GET['id'] ?? 0);
    $pickupCode = strtoupper(trim((string) ($_GET['code'] ?? '')));

    $stmt = db()->prepare('SELECT o.*, s.name AS store_name, p.payment_status, p.payment_method FROM orders o JOIN stores s ON s.id = o.store_id JOIN payments p ON p.order_id = o.id WHERE o.id = ? LIMIT 1');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    $isValidCode = $order && $pickupCode !== '' && hash_equals((string) $order['pickup_code'], $pickupCode);

    if ($isValidCode && $order['order_status'] === 'ready') {
        $order = complete_order_via_pickup_scan($order);
    }

    $isCompleted = $isValidCode && $order['order_status'] === 'completed';

    render_buyer_header('Konfirmasi pickup');
?>
    <section class="container-app flex flex-1 items-center justify-center py-12">
        <div class="w-full max-w-md rounded-2xl border p-7 text-center shadow-sm <?= $isCompleted ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' ?>">
            <?php if ($isCompleted): ?>
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check-circle-2" class="h-9 w-9"></i></div>
                <h1 class="mt-4 text-xl font-extrabold text-emerald-900">Pesanan selesai!</h1>
                <p class="mt-2 text-sm text-emerald-800">Pesanan <strong><?= e($order['order_code']) ?></strong> dari <strong><?= e($order['store_name']) ?></strong> berhasil ditandai selesai.</p>
            <?php elseif (!$isValidCode): ?>
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-rose-100 text-rose-600"><i data-lucide="x-circle" class="h-9 w-9"></i></div>
                <h1 class="mt-4 text-xl font-extrabold text-rose-900">QR tidak valid</h1>
                <p class="mt-2 text-sm text-rose-800">Kode pickup pada QR ini tidak cocok dengan pesanan manapun.</p>
            <?php else: ?>
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-rose-100 text-rose-600"><i data-lucide="x-circle" class="h-9 w-9"></i></div>
                <h1 class="mt-4 text-xl font-extrabold text-rose-900">Pesanan belum bisa diselesaikan</h1>
                <p class="mt-2 text-sm text-rose-800">Status pesanan saat ini: <?= e(status_label($order['order_status'])) ?>.</p>
            <?php endif; ?>
        </div>
    </section>
<?php
    render_buyer_footer();
}

/**
 * Completes an order from a pickup QR scan: locks the row, re-checks it is
 * still 'ready' (defends against a double-scan race), settles cash payments,
 * then marks the order completed. Mirrors the seller's manual
 * "selesaikan pesanan" action but is triggered by the scan itself.
 */
function complete_order_via_pickup_scan(array $order): array
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT order_status, payment_status FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.id = ? FOR UPDATE');
        $stmt->execute([$order['id']]);
        $locked = $stmt->fetch();

        if (!$locked || $locked['order_status'] !== 'ready') {
            $pdo->commit();
            $order['order_status'] = $locked['order_status'] ?? $order['order_status'];
            return $order;
        }

        if ($order['payment_method'] === 'cash_on_pickup' && $locked['payment_status'] !== 'paid') {
            $pdo->prepare("UPDATE payments SET payment_status = 'paid', paid_at = NOW() WHERE order_id = ?")->execute([$order['id']]);
        }
        $pdo->prepare("UPDATE orders SET order_status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$order['id']]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    $order['order_status'] = 'completed';
    return $order;
}
