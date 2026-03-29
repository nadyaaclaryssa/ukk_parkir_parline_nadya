<?php
session_start();
if($_SESSION['role'] != "admin") header("location:../index.php");
include '../config/koneksi.php';

if(isset($_POST['submit'])){
    $nama = $_POST['nama'];
    $user = $_POST['user'];
    $pass = md5($_POST['pass']);
    $role = $_POST['role'];

    $query = mysqli_query($koneksi, "INSERT INTO tb_user VALUES(NULL, '$nama', '$user', '$pass', '$role', 1)");
    if($query) echo "<script>alert('Berhasil!'); window.location='dashboard.php';</script>";
}
?>
<form method="post">
    <input type="text" name="nama" placeholder="Nama Lengkap" required><br>
    <input type="text" name="user" placeholder="Username" required><br>
    <input type="password" name="pass" placeholder="Password" required><br>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="petugas">Petugas</option>
        <option value="owner">Owner</option>
    </select><br>
    <button type="submit" name="submit">Simpan</button>
</form>