<?php
require_once __DIR__ . '/includes/bootstrap.php';
logout();
flash('success', 'Anda telah keluar.');
redirect('index.php');
