<?php
include_once(__DIR__ . '/../Model/prestasiModel.php');

if (isset($_GET['act'])) {
    $action = $_GET['act'];
    
    if ($action === 'get_achievement_stats') {
        header('Content-Type: application/json');
        
        try {
            $prestasiModel = new PrestasiModel();
            $currentYear = date('Y');
            $stats = $prestasiModel->getMonthlyStats($currentYear);
            
            // Initialize array for all months (1-12)
            $monthlyData = array_fill(1, 12, 0);
            
            // Fill in actual data
            foreach ($stats as $stat) {
                $monthlyData[$stat['month']] = (int)$stat['count'];
            }
            
            // Prepare labels (month names)
            $monthNames = array(
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            );
            
            $response = array(
                'labels' => $monthNames,
                'values' => array_values($monthlyData)
            );
            
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
