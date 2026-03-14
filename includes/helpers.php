<?php

// ─── Output escaping ──────────────────────────────────────────────────────────
function e(?string $val): string {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

// ─── Redirects ────────────────────────────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit();
}

// ─── Flash messages ───────────────────────────────────────────────────────────
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ─── Date formatting ──────────────────────────────────────────────────────────
function formatDate(?string $date): string {
    if (!$date) return '—';
    return date('M d, Y', strtotime($date));
}

function formatDateTime(?string $dt): string {
    if (!$dt) return '—';
    return date('M d, Y h:i A', strtotime($dt));
}

// ─── Status badge ─────────────────────────────────────────────────────────────
function statusBadge(string $status): string {
    $map = [
        'unclaimed' => 'warning',
        'matched'   => 'info',
        'claimed'   => 'success',
        'disposed'  => 'secondary',
        'open'      => 'primary',
        'closed'    => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst(e($status)) . '</span>';
}

// ─── Pagination ───────────────────────────────────────────────────────────────
function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): string {
    if ($total <= $perPage) return '';

    $totalPages = (int)ceil($total / $perPage);
    $html = '<nav><ul class="pagination pagination-sm mb-0">';

    // Previous
    $html .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '">‹</a></li>';

    // Page numbers
    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }

    // Next
    $html .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '">›</a></li>';

    $html .= '</ul></nav>';
    return $html;
}

// ─── Activity logging ─────────────────────────────────────────────────────────
function logActivity(string $action, string $description): void {
    try {
        $db     = getDB();
        $userId = $_SESSION['user_id'] ?? null;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($userId) {
            $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            if ($stmt === false) return; // table may not exist yet — skip silently
            $stmt->bind_param('isss', $userId, $action, $description, $ip);
            $stmt->execute();
        }
    } catch (Throwable $e) {
        // Silently fail — logging should never break the app
    }
}

// ─── Photo upload ─────────────────────────────────────────────────────────────
function uploadPhoto(array $file): string|array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload error code: ' . $file['error']];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'File too large. Maximum size is 5MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['error' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS)];
    }

    // Ensure upload directory exists
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }

    $filename = uniqid('item_', true) . '.' . $ext;
    $dest     = UPLOAD_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['error' => 'Failed to save uploaded file.'];
    }

    return $filename;
}

// ═══════════════════════════════════════════════════════════════
//  AUTO-MATCH ENGINE
//  Scores lost reports vs found items, creates match records.
//  Returns array of ['found_id','lost_id','score','match_id']
// ═══════════════════════════════════════════════════════════════
function autoMatch(int $systemUserId, ?int $onlyFoundId = null, ?int $onlyLostId = null): array {
    $db = getDB();

    // Add match_score column if not exists
    $db->query("ALTER TABLE item_matches ADD COLUMN IF NOT EXISTS match_score TINYINT UNSIGNED DEFAULT 0");
    $db->query("ALTER TABLE item_matches ADD COLUMN IF NOT EXISTS auto_matched TINYINT(1) DEFAULT 0");

    // Load open found items (optionally filtered to one)
    $foundFilter = $onlyFoundId ? "AND f.id = $onlyFoundId" : '';
    $foundItems  = $db->query("
        SELECT f.id, f.branch_id, f.category_id, f.item_name, f.color,
               f.brand, f.found_date, f.description
        FROM found_items f
        WHERE f.status = 'unclaimed' $foundFilter
    ")->fetch_all(MYSQLI_ASSOC);

    // Load open lost reports (optionally filtered to one)
    $lostFilter = $onlyLostId ? "AND r.id = $onlyLostId" : '';
    $lostReports = $db->query("
        SELECT r.id, r.branch_id, r.category_id, r.item_name, r.color,
               r.brand, r.lost_date, r.description
        FROM lost_reports r
        WHERE r.status = 'open' $lostFilter
    ")->fetch_all(MYSQLI_ASSOC);

    $created = [];

    foreach ($foundItems as $f) {
        foreach ($lostReports as $r) {
            // Skip if already matched together (any status)
            $exists = $db->query("
                SELECT id FROM item_matches
                WHERE found_item_id = {$f['id']} AND lost_report_id = {$r['id']}
            ")->fetch_assoc();
            if ($exists) continue;

            // ── Scoring ──────────────────────────────────────────
            $score = 0;

            // 1. Item name keyword match (up to 40 pts)
            $fWords = array_filter(explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i','', $f['item_name']))));
            $rWords = array_filter(explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i','', $r['item_name']))));
            $commonWords = array_intersect($fWords, $rWords);
            // Remove stop words
            $stopWords = ['a','an','the','of','and','or','with','for','my','your','old','used'];
            $meaningfulCommon = array_diff($commonWords, $stopWords);
            if (!empty($rWords)) {
                $nameScore = (count($meaningfulCommon) / max(count($rWords), count($fWords))) * 40;
                $score += (int)$nameScore;
            }
            // Exact item name match bonus
            if (strtolower(trim($f['item_name'])) === strtolower(trim($r['item_name']))) {
                $score += 15;
            }

            // 2. Category match (15 pts)
            if ($f['category_id'] && $r['category_id'] && $f['category_id'] == $r['category_id']) {
                $score += 15;
            }

            // 3. Color match (15 pts)
            if ($f['color'] && $r['color']) {
                $fc = strtolower(trim($f['color']));
                $rc = strtolower(trim($r['color']));
                if ($fc === $rc) $score += 15;
                elseif (str_contains($fc, $rc) || str_contains($rc, $fc)) $score += 8;
            }

            // 4. Brand match (15 pts)
            if ($f['brand'] && $r['brand']) {
                $fb = strtolower(trim($f['brand']));
                $rb = strtolower(trim($r['brand']));
                if ($fb === $rb) $score += 15;
                elseif (str_contains($fb, $rb) || str_contains($rb, $fb)) $score += 8;
            }

            // 5. Same branch (10 pts)
            if ($f['branch_id'] == $r['branch_id']) {
                $score += 10;
            }

            // 6. Date sanity: found_date >= lost_date (5 pts bonus, -10 if violated)
            if ($f['found_date'] && $r['lost_date']) {
                if ($f['found_date'] >= $r['lost_date']) $score += 5;
                else $score -= 10;
            }

            $score = max(0, min(100, $score));

            // Only create match if score >= 40
            if ($score < 40) continue;

            // Determine auto status
            $matchStatus = $score >= 90 ? 'confirmed' : 'pending';

            $stmt = $db->prepare("
                INSERT INTO item_matches (found_item_id, lost_report_id, matched_by, match_score, auto_matched, status, notes)
                VALUES (?, ?, ?, ?, 1, ?, ?)
            ");
            $note = "Auto-matched with {$score}% confidence.";
            $stmt->bind_param('iiiiss', $f['id'], $r['id'], $systemUserId, $score, $matchStatus, $note);
            $stmt->execute();
            $matchId = $db->insert_id;

            // Update statuses
            $db->query("UPDATE found_items SET status='matched' WHERE id={$f['id']}");
            $db->query("UPDATE lost_reports SET status='matched' WHERE id={$r['id']}");

            logActivity('AUTO_MATCH', "Auto-matched found #{$f['id']} + lost #{$r['id']} — score: {$score}%");

            $created[] = ['found_id'=>$f['id'], 'lost_id'=>$r['id'], 'score'=>$score, 'match_id'=>$matchId];
        }
    }

    return $created;
}
