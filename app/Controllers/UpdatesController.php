<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\RealtimeUpdates;
use App\Models\SystemPollingSettings;

class UpdatesController extends Controller {
    public function poll(): void {
        Auth::middleware();

        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];
        $role = (string)($currentUser['role'] ?? 'user');

        $since = trim((string)($_GET['since'] ?? ''));
        $context = trim((string)($_GET['context'] ?? 'global'));
        if (!in_array($context, ['global', 'dashboard', 'jobs', 'job'], true)) {
            $context = 'global';
        }

        $jobIds = array_values(array_unique(array_filter(
            array_map('intval', explode(',', (string)($_GET['job_ids'] ?? '')))
        )));
        $jobRef = trim((string)($_GET['job_ref'] ?? ''));
        $jobId = RealtimeUpdates::resolveJobIdFromRef($jobRef);
        $afterCommentId = max(0, (int)($_GET['after_comment_id'] ?? 0));

        $options = [
            'job_ids' => $jobIds,
            'job_ref' => $jobRef,
            'job_id' => $jobId,
            'after_comment_id' => $afterCommentId,
        ];

        $version = RealtimeUpdates::computeVersion($userId, $role, $context, $options);
        $polling = SystemPollingSettings::get()->intervalsMs();

        if ($since !== '' && hash_equals($since, $version)) {
            $this->json([
                'changed' => false,
                'version' => $version,
                'polling' => $polling,
                'notifications' => RealtimeUpdates::notificationPayload($userId),
            ]);
            return;
        }

        $payload = RealtimeUpdates::buildPayload($userId, $role, $context, $options);
        $payload['changed'] = true;
        $payload['version'] = $version;
        $payload['polling'] = $polling;

        $this->json($payload);
    }
}
