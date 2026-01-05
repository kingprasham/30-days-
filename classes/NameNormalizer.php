<?php
/**
 * Name Normalizer Class
 * Handles fuzzy matching and normalization of company names
 * Customer Tracking & Billing Management System
 */

class NameNormalizer {
    private $db;

    // Patterns to identify location/branch identifiers
    private $locationPatterns = [
        '/\s*\(\s*sono\s*(\d+)\s*\)/i',      // (sono 1), (sono 2)
        '/\s*\(\s*branch\s*(\d+)\s*\)/i',    // (branch 1)
        '/\s*\(\s*unit\s*(\d+)\s*\)/i',      // (unit 1)
        '/\s*-\s*unit\s*(\d+)/i',            // - unit 1
        '/\s*-\s*branch\s*(\d+)/i',          // - branch 1
        '/\s*#\s*(\d+)/i',                   // #1, #2
        '/\s+(\d+)$/i',                      // trailing numbers
    ];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Normalize a company name
     * @param string $name
     * @return array ['original' => ..., 'base_name' => ..., 'location_id' => ...]
     */
    public function normalize($name) {
        $original = $name;

        // Step 1: Trim whitespace
        $name = trim($name);

        // Step 2: Remove extra spaces
        $name = preg_replace('/\s+/', ' ', $name);

        // Step 3: Extract location identifier
        $locationId = null;
        foreach ($this->locationPatterns as $pattern) {
            if (preg_match($pattern, $name, $matches)) {
                $locationId = $matches[0];
                $name = trim(preg_replace($pattern, '', $name));
                break;
            }
        }

        // Step 4: Normalize case for comparison (but keep original case for display)
        $baseName = $this->cleanName($name);

        // Step 5: Check name mappings for known corrections
        $corrected = $this->getCorrectedName($baseName);
        if ($corrected) {
            $baseName = $corrected;
        }

        return [
            'original' => $original,
            'base_name' => $baseName,
            'location_id' => $locationId ? trim($locationId, ' ()') : null,
            'display_name' => $name
        ];
    }

    /**
     * Clean and standardize a name
     */
    private function cleanName($name) {
        // Remove special characters but keep essential ones
        $name = preg_replace('/[^\w\s\-\&\.]/u', '', $name);

        // Trim and collapse spaces
        $name = trim(preg_replace('/\s+/', ' ', $name));

        // Convert to title case for consistency
        $name = ucwords(strtolower($name));

        return $name;
    }

    /**
     * Get corrected name from mappings
     */
    public function getCorrectedName($name) {
        $normalized = strtolower(trim($name));

        $result = $this->db->queryOne(
            "SELECT corrected_name FROM name_mappings
             WHERE LOWER(original_name) = ? AND entity_type = 'customer'",
            [$normalized]
        );

        return $result ? $result['corrected_name'] : null;
    }

    /**
     * Find similar names using Levenshtein distance
     */
    public function findSimilar($name, $entityType = 'customer', $threshold = 3) {
        $normalized = strtolower(trim($name));
        $suggestions = [];

        if ($entityType === 'customer') {
            $existing = $this->db->query(
                "SELECT DISTINCT normalized_name as name FROM customers WHERE status = 'active'"
            );
        } else {
            $existing = $this->db->query(
                "SELECT DISTINCT company_name as name FROM dealers WHERE status = 'active'"
            );
        }

        foreach ($existing as $row) {
            $existingName = strtolower($row['name']);
            $distance = levenshtein($normalized, $existingName);

            if ($distance <= $threshold && $distance > 0) {
                $suggestions[] = [
                    'corrected_name' => $row['name'],
                    'distance' => $distance,
                    'similarity' => 1 - ($distance / max(strlen($normalized), strlen($existingName)))
                ];
            }
        }

        // Sort by distance (closest first)
        usort($suggestions, function($a, $b) {
            return $a['distance'] - $b['distance'];
        });

        return array_slice($suggestions, 0, 5);
    }

    /**
     * Check if two names are similar enough to be the same entity
     */
    public function areSimilar($name1, $name2, $threshold = 2) {
        $n1 = strtolower(trim($name1));
        $n2 = strtolower(trim($name2));

        // Exact match
        if ($n1 === $n2) return true;

        // Levenshtein distance
        $distance = levenshtein($n1, $n2);
        if ($distance <= $threshold) return true;

        // Check if one contains the other
        if (strpos($n1, $n2) !== false || strpos($n2, $n1) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Add name mapping
     */
    public function addMapping($original, $corrected, $entityType = 'customer', $autoDetected = false) {
        // Check if mapping already exists
        $exists = $this->db->getValue(
            "SELECT COUNT(*) FROM name_mappings
             WHERE LOWER(original_name) = LOWER(?) AND entity_type = ?",
            [$original, $entityType]
        );

        if ($exists > 0) {
            return $this->db->execute(
                "UPDATE name_mappings SET corrected_name = ?, auto_detected = ?
                 WHERE LOWER(original_name) = LOWER(?) AND entity_type = ?",
                [$corrected, $autoDetected ? 1 : 0, $original, $entityType]
            );
        }

        return $this->db->insert(
            "INSERT INTO name_mappings (original_name, corrected_name, entity_type, auto_detected)
             VALUES (?, ?, ?, ?)",
            [$original, $corrected, $entityType, $autoDetected ? 1 : 0]
        );
    }

    /**
     * Get all mappings
     */
    public function getMappings($entityType = null) {
        $sql = "SELECT * FROM name_mappings";
        $params = [];

        if ($entityType) {
            $sql .= " WHERE entity_type = ?";
            $params[] = $entityType;
        }

        $sql .= " ORDER BY original_name";

        return $this->db->query($sql, $params);
    }

    /**
     * Delete mapping
     */
    public function deleteMapping($id) {
        return $this->db->execute("DELETE FROM name_mappings WHERE id = ?", [$id]);
    }

    /**
     * Batch normalize names and detect duplicates
     */
    public function batchNormalize($names) {
        $results = [];
        $normalized = [];

        foreach ($names as $index => $name) {
            $result = $this->normalize($name);
            $baseLower = strtolower($result['base_name']);

            // Check for duplicates within the batch
            if (isset($normalized[$baseLower])) {
                $result['duplicate_of'] = $normalized[$baseLower];
                $result['is_duplicate'] = true;
            } else {
                $normalized[$baseLower] = $index;
                $result['is_duplicate'] = false;

                // Check against existing customers
                $existing = $this->findSimilar($result['base_name'], 'customer', 2);
                if (!empty($existing)) {
                    $result['possible_matches'] = $existing;
                }
            }

            $results[$index] = $result;
        }

        return $results;
    }

    /**
     * Auto-detect potential typos in customer names
     */
    public function detectTypos() {
        $customers = $this->db->query(
            "SELECT id, name, normalized_name FROM customers WHERE status = 'active' ORDER BY normalized_name"
        );

        $potentialTypos = [];
        $checked = [];

        foreach ($customers as $c1) {
            $checked[$c1['id']] = true;

            foreach ($customers as $c2) {
                if (isset($checked[$c2['id']])) continue;
                if ($c1['id'] === $c2['id']) continue;

                $distance = levenshtein(
                    strtolower($c1['normalized_name']),
                    strtolower($c2['normalized_name'])
                );

                if ($distance > 0 && $distance <= 2) {
                    $potentialTypos[] = [
                        'customer1' => $c1,
                        'customer2' => $c2,
                        'distance' => $distance
                    ];
                }
            }
        }

        return $potentialTypos;
    }
}
