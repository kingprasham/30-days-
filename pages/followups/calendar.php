<?php
$pageTitle = 'Follow-Ups Calendar';
require_once __DIR__ . '/../../includes/header.php';

$followUpObj = new FollowUp();

// Get current month and year from query params or use current
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Validate month and year
$month = max(1, min(12, intval($month)));
$year = max(2020, min(2030, intval($year)));

// Get first and last day of the month
$firstDay = date('Y-m-01', strtotime("$year-$month-01"));
$lastDay = date('Y-m-t', strtotime("$year-$month-01"));

// Get all follow-ups for this month
$followUps = $followUpObj->getByDateRange($firstDay, $lastDay, '');

// Organize follow-ups by date
$followUpsByDate = [];
foreach ($followUps as $fu) {
    $date = $fu['follow_up_date'];
    if (!isset($followUpsByDate[$date])) {
        $followUpsByDate[$date] = [];
    }
    $followUpsByDate[$date][] = $fu;
}

// Calendar calculations
$firstDayOfMonth = strtotime($firstDay);
$daysInMonth = date('t', $firstDayOfMonth);
$dayOfWeek = date('w', $firstDayOfMonth); // 0 (Sunday) to 6 (Saturday)

// Previous and next month navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Follow-Ups Calendar</h4>
    <div>
        <a href="<?= BASE_URL ?>/pages/followups/list.php" class="btn btn-info">
            <i class="fas fa-list me-2"></i>List View
        </a>
        <a href="<?= BASE_URL ?>/pages/followups/add.php" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Add Follow-Up
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <h5 class="mb-0"><?= date('F Y', $firstDayOfMonth) ?></h5>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-sm btn-outline-primary">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="calendar-container">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr class="text-center">
                        <th class="bg-light">Sun</th>
                        <th class="bg-light">Mon</th>
                        <th class="bg-light">Tue</th>
                        <th class="bg-light">Wed</th>
                        <th class="bg-light">Thu</th>
                        <th class="bg-light">Fri</th>
                        <th class="bg-light">Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $currentDay = 1;
                    $today = date('Y-m-d');

                    // Output calendar rows
                    for ($week = 0; $week < 6; $week++) {
                        if ($currentDay > $daysInMonth) break;

                        echo '<tr>';
                        for ($day = 0; $day < 7; $day++) {
                            if ($week === 0 && $day < $dayOfWeek) {
                                // Empty cells before first day of month
                                echo '<td class="calendar-day empty-day"></td>';
                            } elseif ($currentDay > $daysInMonth) {
                                // Empty cells after last day of month
                                echo '<td class="calendar-day empty-day"></td>';
                            } else {
                                // Regular day cell
                                $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                                $isToday = ($currentDate === $today);
                                $dayFollowUps = $followUpsByDate[$currentDate] ?? [];

                                $dayClass = 'calendar-day';
                                if ($isToday) $dayClass .= ' today';
                                if (!empty($dayFollowUps)) $dayClass .= ' has-followups';

                                echo '<td class="' . $dayClass . '" data-date="' . $currentDate . '">';
                                echo '<div class="day-number">' . $currentDay . '</div>';

                                if (!empty($dayFollowUps)) {
                                    echo '<div class="followups-list">';
                                    foreach ($dayFollowUps as $fu) {
                                        $priorityColors = ['urgent'=>'danger','high'=>'warning','medium'=>'info','low'=>'secondary'];
                                        $color = $priorityColors[$fu['priority']] ?? 'secondary';
                                        $statusClass = $fu['status'] === 'completed' ? 'text-decoration-line-through opacity-50' : '';

                                        echo '<div class="followup-item ' . $statusClass . '" onclick="viewFollowUp(' . $fu['id'] . ')">';
                                        echo '<span class="badge bg-' . $color . ' badge-sm">' . ucfirst($fu['type']) . '</span> ';
                                        echo '<small>' . htmlspecialchars(substr($fu['title'], 0, 20)) . '</small>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }

                                echo '</td>';
                                $currentDay++;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="card mt-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Priority Levels:</h6>
                <span class="badge bg-danger me-2">Urgent</span>
                <span class="badge bg-warning me-2">High</span>
                <span class="badge bg-info me-2">Medium</span>
                <span class="badge bg-secondary">Low</span>
            </div>
            <div class="col-md-6">
                <h6>Types:</h6>
                <span class="badge bg-secondary me-2">Call</span>
                <span class="badge bg-secondary me-2">Email</span>
                <span class="badge bg-secondary me-2">Visit</span>
                <span class="badge bg-secondary me-2">Payment</span>
                <span class="badge bg-secondary">General</span>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-day {
    height: 120px;
    vertical-align: top;
    padding: 5px;
    position: relative;
    cursor: default;
}

.calendar-day.empty-day {
    background-color: #f8f9fa;
}

.calendar-day.today {
    background-color: #e7f3ff;
    border: 2px solid #0d6efd !important;
}

.calendar-day.has-followups {
    background-color: #fff9e6;
}

.day-number {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 14px;
}

.followups-list {
    font-size: 11px;
    max-height: 90px;
    overflow-y: auto;
}

.followup-item {
    padding: 2px;
    margin-bottom: 3px;
    cursor: pointer;
    border-radius: 3px;
    background-color: rgba(255,255,255,0.7);
}

.followup-item:hover {
    background-color: rgba(0,0,0,0.05);
}

.badge-sm {
    font-size: 9px;
    padding: 2px 4px;
}

.calendar-container {
    overflow-x: auto;
}
</style>

<script>
function viewFollowUp(id) {
    window.location.href = '<?= BASE_URL ?>/pages/followups/edit.php?id=' + id;
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
