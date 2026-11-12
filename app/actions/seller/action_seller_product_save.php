<?php

function action_seller_product_save(): void
{
    $seller = require_role('seller');
                    $store = get_store_for_seller((int) $seller['id']);
                    if (!$store) throw new RuntimeException('Akun seller belum terhubung ke toko.');
                    $id = (int) ($_POST['product_id'] ?? 0);
                    $name = trim((string) ($_POST['product_name'] ?? ''));
                    $description = trim((string) ($_POST['description'] ?? ''));
                    $categoryId = (int) ($_POST['category_id'] ?? 0);
                    $price = (float) ($_POST['price'] ?? 0);
                    $stock = (int) ($_POST['stock'] ?? 0);
                    $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
                    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
                    if ($name === '' || $categoryId < 1 || $price <= 0 || $stock < 0) {
                        remember_old_input();
                        flash('error', 'Nama, kategori, harga, dan stok harus diisi dengan benar.');
                        redirect('seller.product-form', $id ? ['id' => $id] : []);
                    }
                    if ($imageUrl !== '' && (!filter_var($imageUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME)), ['http', 'https'], true))) {
                        remember_old_input();
                        flash('error', 'URL gambar harus berupa alamat HTTP atau HTTPS yang valid.');
                        redirect('seller.product-form', $id ? ['id' => $id] : []);
                    }
                    $cat = db()->prepare('SELECT id FROM categories WHERE id = ? AND is_active = 1');
                    $cat->execute([$categoryId]);
                    if (!$cat->fetch()) throw new RuntimeException('Kategori tidak ditemukan.');
                    $pdo = db();
                    $pdo->beginTransaction();
                    try {
                        if ($id > 0) {
                            $check = $pdo->prepare('SELECT id, stock FROM products WHERE id = ? AND store_id = ? FOR UPDATE');
                            $check->execute([$id, $store['id']]);
                            $existing = $check->fetch();
                            if (!$existing) throw new RuntimeException('Produk tidak ditemukan.');
                            $stockDifference = $stock - (int) $existing['stock'];
                            $stmt = $pdo->prepare('UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image_url = ?, is_available = ? WHERE id = ? AND store_id = ?');
                            $stmt->execute([$categoryId, $name, $description ?: null, $price, $stock, $imageUrl ?: null, $isAvailable, $id, $store['id']]);
                            if ($stockDifference !== 0) {
                                $movement = $pdo->prepare("INSERT INTO stock_movements (store_id, product_id, actor_user_id, movement_type, quantity_change, note) VALUES (?, ?, ?, 'adjustment', ?, ?)");
                                $movement->execute([$store['id'], $id, $seller['id'], $stockDifference, 'Penyesuaian stok melalui edit menu']);
                            }
                            $pdo->commit();
                            flash('success', 'Produk berhasil diperbarui.');
                        } else {
                            $sku = 'UIB-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
                            $stmt = $pdo->prepare('INSERT INTO products (store_id, category_id, sku, name, description, price, stock, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                            $stmt->execute([$store['id'], $categoryId, $sku, $name, $description ?: null, $price, $stock, $imageUrl ?: null, $isAvailable]);
                            $newProductId = (int) $pdo->lastInsertId();
                            if ($stock > 0) {
                                $movement = $pdo->prepare("INSERT INTO stock_movements (store_id, product_id, actor_user_id, movement_type, quantity_change, note) VALUES (?, ?, ?, 'restock', ?, ?)");
                                $movement->execute([$store['id'], $newProductId, $seller['id'], $stock, 'Stok awal produk']);
                            }
                            $pdo->commit();
                            flash('success', 'Produk baru berhasil ditambahkan.');
                        }
                    } catch (Throwable $exception) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                        throw $exception;
                    }
                    clear_old_input();
                    redirect('seller.products');
}
