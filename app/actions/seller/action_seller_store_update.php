<?php

function action_seller_store_update(): void
{
    $seller = require_role('seller');
                    $store = get_store_for_seller((int) $seller['id']);
                    $name = trim((string) ($_POST['store_name'] ?? ''));
                    $description = trim((string) ($_POST['description'] ?? ''));
                    $location = trim((string) ($_POST['location'] ?? ''));
                    $phone = trim((string) ($_POST['phone'] ?? ''));
                    $isOpen = isset($_POST['is_open']) ? 1 : 0;
                    if ($name === '' || $location === '') {
                        flash('error', 'Nama toko dan lokasi wajib diisi.');
                        redirect('seller.store');
                    }
                    $stmt = db()->prepare('UPDATE stores SET name = ?, description = ?, location = ?, phone = ?, is_open = ? WHERE id = ? AND seller_id = ?');
                    $stmt->execute([$name, $description ?: null, $location, $phone ?: null, $isOpen, $store['id'], $seller['id']]);
                    flash('success', 'Informasi toko berhasil diperbarui.');
                    redirect('seller.store');
}
