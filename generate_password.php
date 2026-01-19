<?php
// File untuk generate password hash
// Jalankan file ini sekali untuk mendapatkan hash password yang benar

echo "=== PASSWORD HASH GENERATOR ===\n\n";

// Password untuk admin
$admin_password = "aoel";
$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
echo "Password Admin: aoel\n";
echo "Username Admin: aoel123\n";
echo "Hash: $admin_hash\n\n";

// Password untuk siswa
$siswa_password = "siswa123";
$siswa_hash = password_hash($siswa_password, PASSWORD_DEFAULT);
echo "Password Siswa: siswa123\n";
echo "Hash: $siswa_hash\n\n";

echo "=== QUERY SQL ===\n\n";
echo "UPDATE users SET password='$admin_hash' WHERE username='aoel123';\n";
echo "UPDATE users SET password='$siswa_hash' WHERE username='siswa01';\n";
?>
