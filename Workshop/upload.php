<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Vérification de l'existence du fichier
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === 0) {

    $file = $_FILES['fichier'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmPath = $file['tmp_name'];
    $fileType = $file['type'];
    }
}