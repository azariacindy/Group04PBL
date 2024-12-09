<?php
// Function to set flash data in session
function set_flashdata($key = "", $value = "")
{
    if (!empty($key) && !empty($value)) {
        $_SESSION['_flashdata'][$key] = $value;
        return true;
    }
    return false;
}

// Function to get flash data from session
function get_flashdata($key = "")
{
    if (!empty($key) && isset($_SESSION['_flashdata'][$key])) {
        $data = $_SESSION['_flashdata'][$key];
        unset($_SESSION['_flashdata'][$key]); // Remove flash data after retrieving
        return $data;
    } else {
        echo "<script> alert('Flash Message \'{$key}\' is not defined.'); </script>";
    }
}

// Function to display different types of alert messages
function pesan($key = "", $pesan = "")
{
    // Define alert structure based on the type of message
    if ($key === "info") {
        set_flashdata('info', "<div class='alert alert-primary alert-dismissible fade show' role='alert'>
            <strong>Info! </strong> {$pesan}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>");
    } elseif ($key === "success") {
        set_flashdata('success', "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            <strong>Berhasil! </strong> {$pesan}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>");
    } elseif ($key === "error") {
        set_flashdata('error', "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            <strong>Gagal! </strong> {$pesan}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>");
    } elseif ($key === "warning") {
        set_flashdata('warning', "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
            <strong>Peringatan! </strong> {$pesan}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>");
    }
}