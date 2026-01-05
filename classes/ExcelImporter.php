<?php
/**
 * Excel Importer Class
 * Customer Tracking & Billing Management System
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelImporter {
    private $db;
    private $errors = [];
    private $warnings = [];

    // Column mapping from Excel to database
    private $columnMap = [
        'Installation Date' => 'installation_date',
        'Monthly Commintment' => 'monthly_commitment',
        'Monthly Commitment' => 'monthly_commitment',
        'Rate' => 'rate',
        'State' => 'state',
        'Location' => 'location',
        'Customer Name' => 'customer_name',
        'Billed' => 'billed',
        'Challan Date' => 'challan_date',
        'Challan No' => 'challan_no',
        'Delivery Thru' => 'delivery_through',
        'Remark' => 'remark',
        'Material Sending Location' => 'material_sending_location',
        'Printer Mode' => 'printer_mode',
        'Printer Model' => 'printer_model',
        'Printer Sr No' => 'printer_sr_no',
        'Collect Printer' => 'collect_printer'
    ];

    // Product columns
    private $productColumns = [
        'A3 White PVC Film',
        'A4 White PVC Film',
        'A5 White PVC Film',
        '260 GSM Paper  A4',
        '260 GSM Paper  A5',
        '260 GSM Paper A4',
        '260 GSM Paper A5',
        '230CC Photo Paper - A3',
        '230CC Photo Paper - A4',
        '230CC Photo Paper - A5',
        '210CC Photo Paper - A3',
        '210CC Photo Paper - A4',
        '180 Gsm Photo Paper - A3',
        '180 Gsm Photo Paper - A4',
        '180 GSM Photo Paper A3',
        '180 GSM Photo Paper A4',
        'Blue Film (8*10)',
        'Blue Film A3',
        'Blue Film A4',
        'Blue Film (13*17)',
        'Blue Film (10*12)',
        'Blue Film 8x10',
        'Blue Film 13x17',
        'Blue Film 10x12'
    ];

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureStatesExist();
    }
    
    /**
     * Ensure states table is populated (auto-creates Indian states if empty)
     */
    private function ensureStatesExist() {
        static $initialized = false;
        if ($initialized) return;
        
        $stateCount = $this->db->getValue("SELECT COUNT(*) FROM states");
        if ($stateCount > 0) {
            $initialized = true;
            return;
        }
        
        // Auto-populate Indian states
        $states = [
            ['Andhra Pradesh', 'AP', 'South'],
            ['Arunachal Pradesh', 'AR', 'East'],
            ['Assam', 'AS', 'East'],
            ['Bihar', 'BR', 'East'],
            ['Chhattisgarh', 'CG', 'Central'],
            ['Goa', 'GA', 'West'],
            ['Gujarat', 'GJ', 'West'],
            ['Haryana', 'HR', 'North'],
            ['Himachal Pradesh', 'HP', 'North'],
            ['Jharkhand', 'JH', 'East'],
            ['Karnataka', 'KA', 'South'],
            ['Kerala', 'KL', 'South'],
            ['Madhya Pradesh', 'MP', 'Central'],
            ['Maharashtra', 'MH', 'West'],
            ['Manipur', 'MN', 'East'],
            ['Meghalaya', 'ML', 'East'],
            ['Mizoram', 'MZ', 'East'],
            ['Nagaland', 'NL', 'East'],
            ['Odisha', 'OR', 'East'],
            ['Punjab', 'PB', 'North'],
            ['Rajasthan', 'RJ', 'North'],
            ['Sikkim', 'SK', 'East'],
            ['Tamil Nadu', 'TN', 'South'],
            ['Telangana', 'TS', 'South'],
            ['Tripura', 'TR', 'East'],
            ['Uttar Pradesh', 'UP', 'North'],
            ['Uttarakhand', 'UK', 'North'],
            ['West Bengal', 'WB', 'East'],
            ['Delhi', 'DL', 'North'],
            ['Jammu and Kashmir', 'JK', 'North'],
        ];
        
        foreach ($states as $state) {
            try {
                $this->db->insert(
                    "INSERT IGNORE INTO states (name, code, region) VALUES (?, ?, ?)",
                    [$state[0], $state[1], $state[2]]
                );
            } catch (Exception $e) {
                // Ignore errors - state may already exist
            }
        }
        
        $initialized = true;
    }

    /**
     * Import Excel file
     */
    public function import($filePath, $monthYear = null, $userId = null) {
        $this->errors = [];
        $this->warnings = [];

        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);

            if (empty($rows)) {
                throw new Exception('Excel file is empty');
            }

            // Get headers from first row
            $headers = array_shift($rows);
            $headers = array_map('trim', $headers);

            // Map headers to column indices
            $columnIndices = $this->mapColumns($headers);

            if (empty($columnIndices['customer_name'])) {
                throw new Exception('Customer Name column not found');
            }

            // Create upload batch
            $batchId = $this->createBatch($filePath, $monthYear, $userId, count($rows));

            $successCount = 0;
            $errorCount = 0;
            $rowNumber = 2; // Starting from row 2 (after header)

            foreach ($rows as $row) {
                try {
                    $result = $this->processRow($row, $columnIndices, $headers, $batchId);
                    if ($result) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } catch (Exception $e) {
                    $this->errors[] = "Row $rowNumber: " . $e->getMessage();
                    $errorCount++;
                }
                $rowNumber++;
            }

            // Update batch status
            $this->db->execute(
                "UPDATE upload_batches SET status = 'completed', success_count = ?, error_count = ?, error_log = ?
                 WHERE id = ?",
                [$successCount, $errorCount, json_encode($this->errors), $batchId]
            );

            return [
                'success' => true,
                'batch_id' => $batchId,
                'total_rows' => count($rows),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $this->errors,
                'warnings' => $this->warnings
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'errors' => $this->errors
            ];
        }
    }

    /**
     * Preview Excel file (without importing)
     */
    public function preview($filePath, $limit = 100) {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);

            if (empty($rows)) {
                throw new Exception('Excel file is empty');
            }

            $headers = array_shift($rows);
            $headers = array_map('trim', $headers);

            // Map headers
            $columnIndices = $this->mapColumns($headers);

            // Get product columns
            $productCols = [];
            foreach ($headers as $idx => $header) {
                if ($this->isProductColumn($header)) {
                    $productCols[$idx] = $header;
                }
            }

            // Process rows for preview
            $previewData = [];
            $nameNormalizer = new NameNormalizer();
            $names = [];

            foreach (array_slice($rows, 0, $limit) as $row) {
                $customerName = $row[$columnIndices['customer_name']] ?? '';
                if (empty(trim($customerName))) continue;

                $names[] = $customerName;

                $previewRow = [
                    'customer_name' => $customerName,
                    'state' => $row[$columnIndices['state'] ?? ''] ?? '',
                    'location' => $row[$columnIndices['location'] ?? ''] ?? '',
                    'challan_date' => $this->parseDate($row[$columnIndices['challan_date'] ?? ''] ?? ''),
                    'challan_no' => $row[$columnIndices['challan_no'] ?? ''] ?? '',
                    'billed' => $row[$columnIndices['billed'] ?? ''] ?? '',
                    'products' => []
                ];

                // Get product quantities
                foreach ($productCols as $idx => $productName) {
                    $qty = $row[$idx] ?? 0;
                    if (!empty($qty) && is_numeric($qty)) {
                        $previewRow['products'][$productName] = (int)$qty;
                    }
                }

                $previewData[] = $previewRow;
            }

            // Normalize names and detect duplicates
            $normalizedNames = $nameNormalizer->batchNormalize($names);

            // Add normalization info to preview
            foreach ($previewData as $idx => &$row) {
                if (isset($normalizedNames[$idx])) {
                    $row['normalized'] = $normalizedNames[$idx];
                }
            }

            // Get distinct values for dropdowns
            $distinctStates = array_unique(array_filter(array_column($previewData, 'state')));
            $distinctLocations = array_unique(array_filter(array_column($previewData, 'location')));

            return [
                'success' => true,
                'headers' => $headers,
                'column_mapping' => $columnIndices,
                'product_columns' => $productCols,
                'total_rows' => count($rows),
                'preview_data' => $previewData,
                'distinct_states' => array_values($distinctStates),
                'distinct_locations' => array_values($distinctLocations)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Map Excel columns to database fields
     */
    private function mapColumns($headers) {
        $indices = [];

        foreach ($headers as $index => $header) {
            $header = trim($header);

            // Check direct mapping
            foreach ($this->columnMap as $excelCol => $dbField) {
                if (strcasecmp($header, $excelCol) === 0) {
                    $indices[$dbField] = $index;
                    break;
                }
            }
        }

        return $indices;
    }

    /**
     * Check if column is a product column
     */
    private function isProductColumn($header) {
        $header = trim($header);

        foreach ($this->productColumns as $productCol) {
            if (stripos($header, $productCol) !== false || stripos($productCol, $header) !== false) {
                return true;
            }
        }

        // Check patterns
        $patterns = [
            '/film/i',
            '/paper/i',
            '/gsm/i',
            '/pvc/i',
            '/cc\s*photo/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $header)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a string is actually a product name (not a customer name)
     */
    private function isProductName($name) {
        $name = trim($name);

        // Check against known product list
        foreach ($this->productColumns as $productName) {
            if (strcasecmp($name, $productName) === 0) {
                return true;
            }
            // Check if product name contains the value (partial match)
            if (stripos($productName, $name) !== false || stripos($name, $productName) !== false) {
                return true;
            }
        }

        // Check product patterns - if name matches product patterns, it's likely a product
        $productPatterns = [
            '/^(a3|a4|a5)\s+(white\s+)?pvc\s+film/i',
            '/^(180|210|230|260)\s*(cc|gsm)\s+(photo\s+)?paper/i',
            '/^blue\s+(base\s+)?film/i',
            '/^\d+\s*(cc|gsm)/i',
            '/film\s*[\(\[]?\s*(a3|a4|a5|8\s*x?\s*10|10\s*x?\s*12|13\s*x?\s*17)/i',
            '/^(pvc|film|paper)\s/i'
        ];

        foreach ($productPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process a single row
     */
    private function processRow($row, $columnIndices, $headers, $batchId) {
        // Get customer name
        $customerName = trim($row[$columnIndices['customer_name']] ?? '');
        if (empty($customerName)) {
            return false;
        }

        // CRITICAL: Skip if customer name is actually a product name
        if ($this->isProductName($customerName)) {
            return false;
        }

        // Skip if customer name is numeric (likely wrong data)
        if (is_numeric($customerName)) {
            return false;
        }

        // Skip if customer name is too short (likely invalid)
        if (strlen($customerName) < 3) {
            return false;
        }

        // Get or create customer
        $customer = new Customer();
        $stateValue = $row[$columnIndices['state'] ?? ''] ?? '';
        $location = $row[$columnIndices['location'] ?? ''] ?? '';

        // If State column is empty, try to infer state from Location
        if (empty($stateValue) && !empty($location)) {
            $stateValue = $this->inferStateFromLocation($location);
        }

        $stateId = $this->getStateId($stateValue);
        $customerRecord = $customer->getOrCreate($customerName, $stateId, $location);

        // Update customer with additional data if available
        if (!empty($columnIndices['installation_date'])) {
            $installDate = $this->parseDate($row[$columnIndices['installation_date']] ?? '');
            if ($installDate && empty($customerRecord['installation_date'])) {
                $this->db->execute(
                    "UPDATE customers SET installation_date = ? WHERE id = ?",
                    [$installDate, $customerRecord['id']]
                );
            }
        }

        if (!empty($columnIndices['monthly_commitment'])) {
            $commitment = $row[$columnIndices['monthly_commitment']] ?? 0;
            if ($commitment && empty($customerRecord['monthly_commitment'])) {
                $this->db->execute(
                    "UPDATE customers SET monthly_commitment = ? WHERE id = ?",
                    [$commitment, $customerRecord['id']]
                );
            }
        }

        // Get challan data
        $challanDate = $this->parseDate($row[$columnIndices['challan_date'] ?? ''] ?? '');
        if (!$challanDate) {
            // Skip this row if date cannot be parsed - dates are critical!
            error_log("WARNING: Could not parse challan date for row. Skipping challan.");
            return false; // Skip this row
        }

        $challanNo = $row[$columnIndices['challan_no'] ?? ''] ?? '';
        $billed = strtolower(trim($row[$columnIndices['billed'] ?? ''] ?? '')) === 'yes' ? 'yes' : 'no';
        $rate = floatval($row[$columnIndices['rate'] ?? ''] ?? 0);

        // Prepare challan data
        $challanData = [
            'customer_id' => $customerRecord['id'],
            'challan_no' => $challanNo,
            'challan_date' => $challanDate,
            'billed' => $billed,
            'rate' => $rate,
            'delivery_through' => $row[$columnIndices['delivery_through'] ?? ''] ?? '',
            'remark' => $row[$columnIndices['remark'] ?? ''] ?? '',
            'material_sending_location' => $row[$columnIndices['material_sending_location'] ?? ''] ?? '',
            'upload_batch_id' => $batchId
        ];

        // Get product items
        $items = [];
        $product = new Product();

        foreach ($headers as $idx => $header) {
            if ($this->isProductColumn($header)) {
                $qty = $row[$idx] ?? 0;
                if (!empty($qty) && is_numeric($qty) && $qty > 0) {
                    $productRecord = $product->getOrCreate($this->normalizeProductName($header));
                    if ($productRecord) {
                        // Use product's base_price instead of the empty "rate" column from Excel
                        $productRate = $productRecord['base_price'] ?? 0;

                        $items[] = [
                            'product_id' => $productRecord['id'],
                            'quantity' => (int)$qty,
                            'rate' => $productRate
                        ];
                    }
                }
            }
        }

        // Create challan with items
        $challan = new Challan();
        $challanId = $challan->create($challanData, $items);

        return $challanId > 0;
    }

    /**
     * Normalize product name
     */
    private function normalizeProductName($name) {
        $name = trim($name);

        // Remove extra spaces
        $name = preg_replace('/\s+/', ' ', $name);

        // Standardize common patterns
        $replacements = [
            '/\(\s*(\d+)\s*\*\s*(\d+)\s*\)/' => '$1x$2',
            '/\s+-\s+/' => ' ',
            '/gsm/i' => 'GSM',
            '/cc/i' => 'CC',
            '/pvc/i' => 'PVC'
        ];

        foreach ($replacements as $pattern => $replacement) {
            $name = preg_replace($pattern, $replacement, $name);
        }

        return $name;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($value) {
        if (empty($value)) return null;

        // If it's a numeric value (Excel serial date)
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        // Clean the value
        $value = trim($value);

        // Try common date formats including "d/Mon/yy" format (1/Nov/25)
        $formats = [
            'd/M/y',     // 1/Nov/25, 01/Nov/25
            'j/M/y',     // 1/Nov/25 (without leading zeros)
            'd/F/y',     // 1/November/25
            'j/F/y',     // 1/November/25 (without leading zeros)
            'd-M-y',     // 1-Nov-25
            'j-M-y',     // 1-Nov-25 (without leading zeros)
            'd/m/Y',     // 01/11/2025
            'j/n/Y',     // 1/11/2025 (no leading zeros)
            'm/d/Y',     // 11/01/2025
            'n/j/Y',     // 11/1/2025 (no leading zeros)
            'Y-m-d',     // 2025-11-01
            'd-m-Y',     // 01-11-2025
            'd.m.Y',     // 01.11.2025
            'd/m/y',     // 01/11/25
            'j/n/y',     // 1/11/25 (no leading zeros)
        ];

        foreach ($formats as $format) {
            // Use createFromFormat with strict validation but check for parse errors
            $date = DateTime::createFromFormat($format, $value);

            // Check if parsing succeeded and no errors occurred
            if ($date !== false) {
                $errors = DateTime::getLastErrors();
                // PHP 8.0+: getLastErrors() returns false when no errors, older PHP returns array
                // If false, there are no errors, so it's valid
                // If array, check warning_count and error_count
                if ($errors === false || ($errors['warning_count'] == 0 && $errors['error_count'] == 0)) {
                    return $date->format('Y-m-d');
                }
            }
        }

        // Try strtotime as fallback (handles many natural formats)
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Infer state from location name (city-based mapping)
     */
    private function inferStateFromLocation($location) {
        if (empty($location)) return null;

        $location = strtolower(trim($location));

        // Common city to state mappings
        $cityStateMap = [
            // Maharashtra
            'mumbai' => 'Maharashtra', 'pune' => 'Maharashtra', 'nagpur' => 'Maharashtra',
            'nashik' => 'Maharashtra', 'thane' => 'Maharashtra', 'aurangabad' => 'Maharashtra',
            'solapur' => 'Maharashtra', 'virar' => 'Maharashtra', 'navi mumbai' => 'Maharashtra',
            'kalyan' => 'Maharashtra', 'vasai' => 'Maharashtra', 'palghar' => 'Maharashtra',
            'badlapur' => 'Maharashtra', 'sangli' => 'Maharashtra', 'palus' => 'Maharashtra',

            // Delhi NCR
            'delhi' => 'Delhi', 'new delhi' => 'Delhi', 'dwarka' => 'Delhi',
            'gurugram' => 'Haryana', 'gurgaon' => 'Haryana', 'faridabad' => 'Haryana',
            'noida' => 'Uttar Pradesh', 'ghaziabad' => 'Uttar Pradesh', 'greater noida' => 'Uttar Pradesh',

            // Uttar Pradesh
            'lucknow' => 'Uttar Pradesh', 'kanpur' => 'Uttar Pradesh', 'agra' => 'Uttar Pradesh',
            'varanasi' => 'Uttar Pradesh', 'meerut' => 'Uttar Pradesh', 'allahabad' => 'Uttar Pradesh',
            'bareilly' => 'Uttar Pradesh', 'aligarh' => 'Uttar Pradesh', 'moradabad' => 'Uttar Pradesh',
            'saharanpur' => 'Uttar Pradesh', 'gorakhpur' => 'Uttar Pradesh', 'amethi' => 'Uttar Pradesh',

            // Karnataka
            'bangalore' => 'Karnataka', 'bengaluru' => 'Karnataka', 'mysore' => 'Karnataka',
            'mangalore' => 'Karnataka', 'hubli' => 'Karnataka', 'belgaum' => 'Karnataka',

            // Gujarat
            'ahmedabad' => 'Gujarat', 'surat' => 'Gujarat', 'vadodara' => 'Gujarat',
            'rajkot' => 'Gujarat', 'bhavnagar' => 'Gujarat', 'jamnagar' => 'Gujarat',

            // Other major cities
            'hyderabad' => 'Telangana', 'chennai' => 'Tamil Nadu', 'kolkata' => 'West Bengal',
            'jaipur' => 'Rajasthan', 'bhopal' => 'Madhya Pradesh', 'patna' => 'Bihar',
        ];

        // Check for city matches
        foreach ($cityStateMap as $city => $state) {
            if (stripos($location, $city) !== false) {
                return $state;
            }
        }

        return null;
    }

    /**
     * Get state ID by name
     */
    private function getStateId($stateName) {
        if (empty($stateName)) return null;

        $stateName = trim($stateName);

        $state = $this->db->queryOne(
            "SELECT id FROM states WHERE LOWER(name) = LOWER(?) OR LOWER(code) = LOWER(?)",
            [$stateName, $stateName]
        );

        return $state ? $state['id'] : null;
    }

    /**
     * Create upload batch record
     */
    private function createBatch($filePath, $monthYear, $userId, $recordsCount) {
        return $this->db->insert(
            "INSERT INTO upload_batches (filename, original_filename, upload_date, month_year, records_count, user_id, status)
             VALUES (?, ?, CURDATE(), ?, ?, ?, 'processing')",
            [
                basename($filePath),
                basename($filePath),
                $monthYear ?? date('F Y'),
                $recordsCount,
                $userId
            ]
        );
    }

    /**
     * Get upload history
     */
    public function getUploadHistory($limit = 50) {
        return $this->db->query(
            "SELECT ub.*, u.username as uploaded_by
             FROM upload_batches ub
             LEFT JOIN users u ON ub.user_id = u.id
             ORDER BY ub.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get warnings
     */
    public function getWarnings() {
        return $this->warnings;
    }
}
