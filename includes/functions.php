<?php
/**
 * Helper Functions
 * Customer Tracking & Billing Management System
 */

/**
 * Get all states
 */
function getStates() {
    return dbQuery("SELECT * FROM states ORDER BY name");
}

/**
 * Get state by ID
 */
function getStateById($id) {
    return dbQueryOne("SELECT * FROM states WHERE id = ?", [$id]);
}

/**
 * Get territories
 */
function getTerritories() {
    return ['North', 'South', 'East', 'West', 'Central', 'Pan India'];
}

/**
 * Get status options
 */
function getStatusOptions() {
    return [
        'active' => 'Active',
        'inactive' => 'Inactive'
    ];
}

/**
 * Get billed options
 */
function getBilledOptions() {
    return [
        'yes' => 'Yes',
        'no' => 'No'
    ];
}

/**
 * Get contract status options
 */
function getContractStatusOptions() {
    return [
        'active' => 'Active',
        'expired' => 'Expired',
        'renewed' => 'Renewed',
        'terminated' => 'Terminated'
    ];
}

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';

    $html = '<nav><ul class="pagination justify-content-center">';

    // Previous button
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prevDisabled . '">
              <a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '">&laquo;</a>
              </li>';

    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);

    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">
                  <a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a>
                  </li>';
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }

    // Next button
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    $html .= '<li class="page-item ' . $nextDisabled . '">
              <a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '">&raquo;</a>
              </li>';

    $html .= '</ul></nav>';

    return $html;
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Get time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Truncate text
 */
function truncateText($text, $length = 50, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get badge class based on status
 */
function getStatusBadgeClass($status) {
    $classes = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
        'pending' => 'bg-warning',
        'expired' => 'bg-danger',
        'renewed' => 'bg-info',
        'terminated' => 'bg-dark',
        'yes' => 'bg-success',
        'no' => 'bg-danger'
    ];

    return $classes[strtolower($status)] ?? 'bg-secondary';
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $filename, $headers = []) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write headers
    if (!empty($headers)) {
        fputcsv($output, $headers);
    } elseif (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }

    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Indian)
 */
function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) === 10;
}

/**
 * Validate GST number
 */
function isValidGST($gst) {
    $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
    return preg_match($pattern, strtoupper($gst));
}

/**
 * Clean filename for upload
 */
function cleanFilename($filename) {
    // Remove any path components
    $filename = basename($filename);

    // Replace spaces with underscores
    $filename = str_replace(' ', '_', $filename);

    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);

    return $filename;
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($extension) {
    return date('YmdHis') . '_' . uniqid() . '.' . $extension;
}
