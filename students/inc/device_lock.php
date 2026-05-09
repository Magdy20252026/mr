<?php
// students/inc/device_lock.php
// ✅ Device lock using FingerprintJS visitorId with transaction and row locking
// Prevents race conditions that allowed multiple devices to register simultaneously

function device_lock_check_and_register(PDO $pdo, int $studentId, string $deviceId, string $deviceName): array {
    if ($deviceId === '') {
        return ['ok' => false, 'reason' => 'missing_device_id'];
    }

    try {
        $pdo->beginTransaction();

        // Lock all device records for this student to prevent race conditions
        $stmt = $pdo->prepare("
            SELECT id, device_hash, device_name, is_active
            FROM student_devices
            WHERE student_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$studentId]);
        $allDevices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Find the first active device (should be only one, but we'll clean up if needed)
        $activeDevice = null;
        $activeCount = 0;
        foreach ($allDevices as $dev) {
            if ((int)$dev['is_active'] === 1) {
                $activeCount++;
                if ($activeDevice === null) {
                    $activeDevice = $dev;
                }
            }
        }

        // Cleanup: if there are multiple active devices (from previous race condition),
        // disable all except the earliest one
        if ($activeCount > 1) {
            $keepId = $activeDevice['id'];
            $disableStmt = $pdo->prepare("
                UPDATE student_devices
                SET is_active = 0
                WHERE student_id = ? AND is_active = 1 AND id != ?
            ");
            $disableStmt->execute([$studentId, $keepId]);
            // Re-fetch active device (only one left)
            $activeDevice = $activeDevice; // still valid
        }

        // Case 1: No active device → register this device as the only one
        if (!$activeDevice) {
            $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $ins = $pdo->prepare("
                INSERT INTO student_devices
                    (student_id, device_hash, device_label, device_name, user_agent, ip_first, first_login_at, last_login_at, is_active)
                VALUES
                    (?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)
            ");
            $ins->execute([
                $studentId,
                $deviceId,
                $deviceName,
                $deviceName,
                mb_substr($ua, 0, 255),
                mb_substr($ip, 0, 64),
            ]);

            $pdo->commit();
            return ['ok' => true, 'reason' => 'first_device_registered'];
        }

        // Case 2: Active device exists → compare device_hash
        if ((string)$activeDevice['device_hash'] !== $deviceId) {
            $pdo->rollBack();
            return ['ok' => false, 'reason' => 'device_not_allowed'];
        }

        // Case 3: Same device → update last login and metadata
        $up = $pdo->prepare("
            UPDATE student_devices
            SET last_login_at = NOW(),
                device_name = ?,
                user_agent = ?
            WHERE id = ?
        ");
        $up->execute([
            $deviceName,
            mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            (int)$activeDevice['id']
        ]);

        $pdo->commit();
        return ['ok' => true, 'reason' => 'device_allowed'];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Device lock error for student $studentId: " . $e->getMessage());
        return ['ok' => false, 'reason' => 'internal_error'];
    }
}