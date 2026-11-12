<?php

function action_auth_register(): void
{
    $name = trim((string) ($_POST['name'] ?? ''));
                    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                    $phone = trim((string) ($_POST['phone'] ?? ''));
                    $password = (string) ($_POST['password'] ?? '');
                    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
                    if ($name === '' || strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $confirmation) {
                        remember_old_input();
                        flash('error', 'Periksa kembali nama, email, dan password. Password minimal 8 karakter dan harus sama dengan konfirmasi.');
                        redirect('register');
                    }
                    $stmt = db()->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, \'buyer\')');
                    try {
                        $stmt->execute([$name, $email, $phone ?: null, password_hash($password, PASSWORD_DEFAULT)]);
                    } catch (PDOException $exception) {
                        if ($exception->getCode() === '23000') {
                            flash('error', 'Email sudah terdaftar. Gunakan email lain atau login.');
                            redirect('register');
                        }
                        throw $exception;
                    }
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) db()->lastInsertId(),
                        'name' => $name,
                        'email' => $email,
                        'role' => 'buyer',
                    ];
                    clear_old_input();
                    flash('success', 'Akun UIBite berhasil dibuat. Selamat berbelanja!');
                    redirect('home');
}
