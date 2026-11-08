<?php

namespace App\Services\Amadeus;

use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\TerminalActivityLog;
use App\Models\User;
use Illuminate\Support\Collection;

class QueueService
{
    /**
     * Standard Amadeus Queue Definitions
     */
    public const QUEUE_DEFINITIONS = [
        1 => [
            'name' => 'Ticketing Time Limit (TTL)',
            'category' => 'TICKETING',
            'description' => 'PNRs approaching ticketing deadline or awaiting manual ticket issue (TTP)',
        ],
        7 => [
            'name' => 'Schedule Changes',
            'category' => 'SCHEDULE_CHANGE',
            'description' => 'Airline flight rescheduling, cancellations, and timing amendments',
        ],
        8 => [
            'name' => 'Waitlist Confirmations',
            'category' => 'WAITLIST',
            'description' => 'Previously waitlisted (HL/HN) seats confirmed to active (HK) status',
        ],
        9 => [
            'name' => 'General Messages / OTH',
            'category' => 'GENERAL_MESSAGE',
            'description' => 'Airline operational messages, special service requests, and OSI replies',
        ],
        87 => [
            'name' => 'Schedule Exchange & Reissue',
            'category' => 'EXCHANGE',
            'description' => 'Re-validation, involuntary ticket reroutes, and fare calculation changes',
        ],
    ];

    /**
     * Get summary counts for all active queues.
     *
     * @return array<int, array{queueNumber: int, name: string, category: string, count: int, description: string}>
     */
    public function getSummary(string $officeId = 'LON1A0980', ?string $userId = null): array
    {
        $summary = [];

        foreach (self::QUEUE_DEFINITIONS as $qNum => $meta) {
            $query = QueueItem::pending()
                ->where('queue_number', $qNum)
                ->where('office_id', $officeId);

            if ($userId) {
                $query->where(function ($q) use ($userId): void {
                    $q->whereNull('target_user_id')
                        ->orWhere('target_user_id', $userId);
                });
            }

            $count = $query->count();

            $summary[] = [
                'queueNumber' => $qNum,
                'name' => $meta['name'],
                'category' => $meta['category'],
                'count' => $count,
                'description' => $meta['description'],
            ];
        }

        return $summary;
    }

    /**
     * Generate Amadeus QS (Queue Summary) terminal screen lines.
     *
     * @return array<int, string>
     */
    public function formatQueueSummaryLines(string $officeId = 'LON1A0980', ?string $userId = null): array
    {
        $summary = $this->getSummary($officeId, $userId);
        $totalItems = array_sum(array_column($summary, 'count'));

        $lines = [
            "--- AMADEUS QUEUE BANK SUMMARY : {$officeId} ---",
            'DATE: '.strtoupper(now()->format('dMY')).'   AGENT: '.($userId ?: '1234AB')."   TOTAL PNRS ON QUEUE: {$totalItems}",
            'Q   CAT    COUNT  DESCRIPTION',
            '-------------------------------------------------------------',
        ];

        foreach ($summary as $item) {
            $qStr = str_pad((string) $item['queueNumber'], 3, ' ', STR_PAD_RIGHT);
            $catStr = str_pad(substr($item['category'], 0, 6), 6, ' ', STR_PAD_RIGHT);
            $countStr = str_pad((string) $item['count'], 6, ' ', STR_PAD_LEFT);
            $nameStr = $item['name'];
            $lines[] = "{$qStr} {$catStr} {$countStr}  {$nameStr}";
        }

        $lines[] = '-------------------------------------------------------------';
        $lines[] = "USE 'QD <Q-NUMBER>' TO DISPLAY (E.G. QD 1 OR QD 7)";
        $lines[] = "USE 'QI' OR 'IG' TO EXIT QUEUE MODE";

        return $lines;
    }

    /**
     * Display entries in a specific queue (Amadeus QD command).
     *
     * @return array{lines: array<int, string>, items: Collection}
     */
    public function displayQueue(int $queueNumber, string $officeId = 'LON1A0980', ?string $userId = null): array
    {
        $def = self::QUEUE_DEFINITIONS[$queueNumber] ?? [
            'name' => "Queue {$queueNumber}",
            'category' => 'GENERAL',
            'description' => 'Custom Agency Queue',
        ];

        $query = QueueItem::pending()
            ->where('queue_number', $queueNumber)
            ->where('office_id', $officeId);

        if ($userId === null || $userId === '') {
            return [
                'lines' => ["Q{$queueNumber} QUEUE ACCESS REQUIRES AN AUTHENTICATED AGENT"],
                'items' => collect(),
            ];
        }

        $query->where(function ($q) use ($userId): void {
            $q->whereNull('target_user_id')
                ->orWhere('target_user_id', $userId);
        });

        $items = $query->orderBy('created_at', 'asc')->get();

        if ($items->isEmpty()) {
            return [
                'lines' => [
                    "Q{$queueNumber} {$def['category']} - QUEUE EMPTY FOR OFFICE {$officeId}",
                    'NO PNRS REQUIRE ACTION IN THIS QUEUE CATEGORY',
                ],
                'items' => collect(),
            ];
        }

        $lines = [
            "--- QUEUE DISPLAY: Q{$queueNumber} ({$def['name']}) - TOTAL: ".$items->count().' ---',
            'ITEM  LOCATOR  TRAINEE      URGENCY  DETAILS',
            '-------------------------------------------------------------',
        ];

        foreach ($items as $idx => $item) {
            $itemNum = str_pad((string) ($idx + 1), 4, ' ', STR_PAD_RIGHT);
            $loc = str_pad($item->pnr_locator, 8, ' ', STR_PAD_RIGHT);
            $user = str_pad($item->target_user_id ?: 'ALL', 12, ' ', STR_PAD_RIGHT);
            $urgency = str_pad($item->pnr_summary['urgency'] ?? 'NORMAL', 8, ' ', STR_PAD_RIGHT);
            $pax = $item->pnr_summary['passenger'] ?? 'PASSENGER';
            $lines[] = "{$itemNum}  {$loc} {$user} {$urgency} {$pax} - {$item->message}";
        }

        $lines[] = '-------------------------------------------------------------';
        $lines[] = "TYPE 'RT <LOCATOR>' TO RETRIEVE AND WORK ON PNR";

        return [
            'lines' => $lines,
            'items' => $items,
        ];
    }

    /**
     * Display specific queue with category and date range (QS2C30D1).
     *
     * @return array<int, string>
     */
    public function displaySpecificQueue(int $qNum, int $cat, int $dateRange, string $officeId = 'LON1A0980', ?string $userId = null): array
    {
        $res = $this->displayQueue($qNum, $officeId, $userId);
        if (empty($res['items']) || $res['items']->isEmpty()) {
            return [
                "--- QUEUE {$qNum} CATEGORY {$cat} DATE RANGE {$dateRange} : {$officeId} ---",
                'NO PNRS REQUIRING IMMEDIATE ATTENTION IN THIS CATEGORY/RANGE',
            ];
        }

        $lines = [
            "--- QUEUE {$qNum} CATEGORY {$cat} DATE RANGE {$dateRange} : {$officeId} ---",
            'ITEM  LOCATOR  TRAINEE      URGENCY  DETAILS',
            '-------------------------------------------------------------',
        ];

        foreach ($res['items'] as $idx => $item) {
            $itemNum = str_pad((string) ($idx + 1), 4, ' ', STR_PAD_RIGHT);
            $loc = str_pad($item->pnr_locator, 8, ' ', STR_PAD_RIGHT);
            $user = str_pad($item->target_user_id ?: 'ALL', 12, ' ', STR_PAD_RIGHT);
            $urgency = str_pad($item->pnr_summary['urgency'] ?? 'NORMAL', 8, ' ', STR_PAD_RIGHT);
            $pax = $item->pnr_summary['passenger'] ?? 'PASSENGER';
            $lines[] = "{$itemNum}  {$loc} {$user} {$urgency} {$pax} - {$item->message}";
        }

        return $lines;
    }

    /**
     * Place a PNR on a specific queue (QE command).
     *
     * @return array<int, string>
     */
    public function placeOnQueue(int $qNum, array $pnr, string $officeId = 'LON1A0980', ?string $userId = null): array
    {
        $locator = $pnr['recordLocator'] ?? 'PENDNG';
        $pax = ! empty($pnr['passengers']) ? ($pnr['passengers'][0]['lastName'].'/'.$pnr['passengers'][0]['firstName']) : 'PASSENGER';

        $this->broadcastToQueue(
            queueNumber: $qNum,
            category: 'GENERAL',
            pnrLocator: $locator,
            pnrSummary: ['passenger' => $pax, 'urgency' => 'NORMAL'],
            message: 'PLACED VIA QE COMMAND',
            targetUserId: $userId,
            officeId: $officeId
        );

        return [
            "OK PLACED ON QUEUE {$qNum} / C0 / {$officeId}",
        ];
    }

    /**
     * Broadcast or send a test PNR to a queue.
     */
    public function broadcastToQueue(
        int $queueNumber,
        string $category,
        string $pnrLocator,
        array $pnrSummary,
        string $message,
        ?string $targetUserId = null,
        string $officeId = 'LON1A0980'
    ): QueueItem {
        return QueueItem::create([
            'queue_number' => $queueNumber,
            'category' => $category,
            'office_id' => $officeId,
            'target_user_id' => $targetUserId,
            'pnr_locator' => strtoupper($pnrLocator),
            'pnr_summary' => $pnrSummary,
            'message' => $message,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Generate Amadeus QTQ (Queue Total Queues) screen lines.
     *
     * @return array<int, string>
     */
    public function formatQueueListLines(string $officeId = 'LON1A0980'): array
    {
        $lines = [
            "--- AMADEUS CONFIGURED QUEUES FOR OFFICE: {$officeId} (QTQ) ---",
            'QNUM  CAT  DESCRIPTION',
            '-----------------------------------------------------------------',
            ' 01   C0   TICKETING TIME LIMIT (TTL) MONITORING',
            ' 03   C0   OPTION ELEMENT (OP) TIME LIMITS',
            ' 07   C0   AIRLINE SCHEDULE CHANGES (TK/UN)',
            ' 08   C1   CONFIRMED WAITLIST NOTIFICATIONS (HK)',
            ' 08   C3   DOMESTIC TICKETING QUEUE (TKDO)',
            ' 08   C5   INTERNATIONAL TICKETING QUEUE (TKIN)',
            ' 09   C0   GENERAL AIRLINE OTH / OSI / SSR MESSAGES',
            ' 87   C0   EXCHANGE, REISSUE & REVALIDATION',
            ' 97   C0   FREE-FLOW AGENCY COMMUNICATION QUEUE',
            '-----------------------------------------------------------------',
            'USE "QS<Q>C<CAT>D1" TO START PROCESSING (E.G. QS97C0D1)',
        ];

        return $lines;
    }

    /**
     * Place PNR on specific queue and category, with optional target office.
     *
     * @return array<int, string>
     */
    public function placeOnOfficeQueue(int $qNum, int $cat, array $pnr, string $targetOffice = 'LON1A0980', ?string $userId = null): array
    {
        $locator = $pnr['recordLocator'] ?? 'PENDNG';
        $pax = ! empty($pnr['passengers']) ? ($pnr['passengers'][0]['lastName'].'/'.$pnr['passengers'][0]['firstName']) : 'PASSENGER';

        $this->broadcastToQueue(
            queueNumber: $qNum,
            category: "C{$cat}",
            pnrLocator: $locator,
            pnrSummary: ['passenger' => $pax, 'urgency' => 'NORMAL'],
            message: "PLACED ON Q{$qNum} C{$cat} FOR {$targetOffice}",
            targetUserId: $userId,
            officeId: $targetOffice
        );

        return [
            "OK PLACED ON QUEUE {$qNum} / C{$cat} / OFFICE {$targetOffice}",
        ];
    }

    /**
     * Reset student sandbox or all sandbox states.
     *
     * @return array{clearedQueues: int, clearedLogs: int, resetPnrs: int}
     */
    public function resetSandbox(?string $targetUserId = null, string $officeId = 'LON1A0980'): array
    {
        $queueQuery = QueueItem::where('office_id', $officeId);
        // Activity logs carry no office column, so scope them through the
        // usernames that belong to this office. A classroom reset must never
        // delete another office's training records or audit trail.
        $logQuery = TerminalActivityLog::whereIn(
            'user_id',
            User::where('office_id', $officeId)->pluck('username')
        );
        $pnrQuery = Pnr::where('office_id', $officeId);

        if ($targetUserId) {
            $queueQuery->where('target_user_id', $targetUserId);
            $logQuery->where('user_id', $targetUserId);
            $pnrQuery->where('user_id', $targetUserId);
        }

        $clearedQueues = $queueQuery->delete();
        $clearedLogs = $logQuery->delete();
        $resetPnrs = $pnrQuery->delete();

        return [
            'clearedQueues' => $clearedQueues,
            'clearedLogs' => $clearedLogs,
            'resetPnrs' => $resetPnrs,
        ];
    }
}
