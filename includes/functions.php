<?php
// includes/functions.php

function isPengurus($jabatan_id) {
    // Jabatan pengurus: 3 = Ketua, 4 = Sekretaris, 5 = Bendahara, 6 = Pimpinan Divisi
    return in_array($jabatan_id, [3, 4, 5, 6]);
}

function isKetua() {
    return isset($_SESSION['jabatan_id']) && $_SESSION['jabatan_id'] == 3;
}

function isSekretaris() {
    return isset($_SESSION['jabatan_id']) && $_SESSION['jabatan_id'] == 4;
}

function isBendahara() {
    return isset($_SESSION['jabatan_id']) && $_SESSION['jabatan_id'] == 5;
}

function isPimpinanDivisi() {
    return isset($_SESSION['jabatan_id']) && $_SESSION['jabatan_id'] == 6;
}

function redirectIfNotPengurus() {
    if (!isset($_SESSION['jabatan_id']) || !isPengurus($_SESSION['jabatan_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}
