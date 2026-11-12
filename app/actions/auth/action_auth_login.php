<?php

function action_auth_login(): void
{
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                    $password = (string) ($_POST['password'] ?? '');
                    $stmt = db()->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    if (!$user || !password_verify($password, $user['password'])) {
                        flash('error', 'Email atau password tidak sesuai.');
                        remember_old_input();
                        redirect('login');
                    }
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                    ];
                    clear_old_input();
                    flash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
                    redirect($user['role'] === 'seller' ? 'seller.dashboard' : 'home');
}
