<?php
// File: includes/functions/homepage_functions.php

/**
 * Mengambil pengaturan hero section
 * 
 * @return array Data hero section
 */
function getHeroContent()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'hero_content'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['setting_value'])) {
            return json_decode($result['setting_value'], true);
        }
    } catch (PDOException $e) {
        error_log('Error fetching hero content: ' . $e->getMessage());
    }

    // Default values if not found
    return [
        'title' => 'Cabang Dinas Kehutanan Wilayah Bojonegoro',
        'subtitle' => 'Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur yang melaksanakan kebijakan teknis operasional di bidang kehutanan',
        'button1_text' => 'Layanan Kami',
        'button1_link' => '#layanan',
        'button2_text' => 'Hubungi Kami',
        'button2_link' => '#kontak',
        'background_video' => 'forest-bg.mp4'
    ];
}

