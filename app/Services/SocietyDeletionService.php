<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Models\Wing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SocietyDeletionService
{
    public function preview(Society $society): array
    {
        $flatIds = Flat::withTrashed()
            ->where('society_id', $society->id)
            ->pluck('id');

        $residents = Resident::withTrashed()
            ->whereIn('flat_id', $flatIds)
            ->get(['id', 'user_id']);

        $residentIds = $residents->pluck('id');

        $residentUserIds = $residents->pluck('user_id');

        $roles = Role::whereIn('name', [
            'admin',
            'gatekeeper',
        ])->pluck('id', 'name');

        $adminRoleId = $roles['admin'];

        $gatekeeperRoleId = $roles['gatekeeper'];

        $visitorSummary = $this->getVisitorSummary($flatIds);

        $summary = [

            'society' => 1,

            'admins' => $this->countAdmins($society, $adminRoleId),

            'gatekeepers' => $this->countGatekeepers($society, $gatekeeperRoleId),

            'flats' => $flatIds->count(),

            'residents' => $residentIds->count(),

            'resident_users' => $residentUserIds->count(),

            'complaints' => $this->countComplaints($residentUserIds),

            'deliveries' => $this->countDeliveries($residentIds),

            'visitor_logs' => $this->countVisitorLogs($flatIds),

            'visitors_to_delete' => $visitorSummary['delete'],

            'visitors_to_keep' => $visitorSummary['keep'],

            'activity_logs_kept' => ActivityLog::count(),

            'total_records' => 0,
        ];

        $summary['wings'] = Wing::withTrashed()
            ->where('society_id', $society->id)
            ->count();

        $summary['total_records'] =
            $summary['society']
            + $summary['admins']
            + $summary['gatekeepers']
            + $summary['wings']
            + $summary['flats']
            + $summary['residents']
            + $summary['resident_users']
            + $summary['complaints']
            + $summary['deliveries']
            + $summary['visitor_logs']
            + $summary['visitors_to_delete'];

        return $summary;
    }

    public function softDelete(Society $society): void
    {
        DB::transaction(function () use ($society) {
            $flatIds = Flat::withTrashed()
                ->where('society_id', $society->id)
                ->pluck('id');

            $residents = Resident::withTrashed()
                ->whereIn('flat_id', $flatIds)
                ->get(['id', 'user_id']);

            $residentIds = $residents->pluck('id');
            $residentUserIds = $residents->pluck('user_id')->filter();

            $visitorIdsInSociety = VisitorLog::withTrashed()
                ->whereIn('flat_id', $flatIds)
                ->distinct()
                ->pluck('visitor_id');

            $visitorIdsOutsideSociety = VisitorLog::withTrashed()
                ->whereIn('visitor_id', $visitorIdsInSociety)
                ->whereNotIn('flat_id', $flatIds)
                ->distinct()
                ->pluck('visitor_id');

            $visitorIdsToDelete = $visitorIdsInSociety
                ->diff($visitorIdsOutsideSociety);

            VisitorLog::whereIn('flat_id', $flatIds)->delete();
            Visitor::withTrashed()->whereIn('id', $visitorIdsToDelete)->delete();
            Complaint::whereIn('user_id', $residentUserIds)->delete();
            Delivery::whereIn('resident_id', $residentIds)->delete();
            Resident::whereIn('flat_id', $flatIds)->delete();
            Flat::whereIn('id', $flatIds)->delete();
            Wing::where('society_id', $society->id)->delete();
            User::where('society_id', $society->id)->delete();
            $society->delete();
        });
    }

    public function forceDelete(Society $society): void
    {
        DB::transaction(function () use ($society) {
            $flatIds = Flat::withTrashed()
                ->where('society_id', $society->id)
                ->pluck('id');

            $residents = Resident::withTrashed()
                ->whereIn('flat_id', $flatIds)
                ->get(['id', 'user_id']);

            $residentIds = $residents->pluck('id');
            $residentUserIds = $residents->pluck('user_id')->filter();

            $visitorIdsInSociety = VisitorLog::withTrashed()
                ->whereIn('flat_id', $flatIds)
                ->distinct()
                ->pluck('visitor_id');

            $visitorIdsOutsideSociety = VisitorLog::withTrashed()
                ->whereIn('visitor_id', $visitorIdsInSociety)
                ->whereNotIn('flat_id', $flatIds)
                ->distinct()
                ->pluck('visitor_id');

            $visitorIdsToDelete = $visitorIdsInSociety
                ->diff($visitorIdsOutsideSociety);

            VisitorLog::withTrashed()->whereIn('flat_id', $flatIds)->forceDelete();
            Visitor::withTrashed()->whereIn('id', $visitorIdsToDelete)->forceDelete();
            Complaint::withTrashed()->whereIn('user_id', $residentUserIds)->forceDelete();
            Delivery::withTrashed()->whereIn('resident_id', $residentIds)->forceDelete();
            Resident::withTrashed()->whereIn('id', $residentIds)->forceDelete();
            Flat::withTrashed()->whereIn('id', $flatIds)->forceDelete();
            Wing::withTrashed()->where('society_id', $society->id)->forceDelete();
            User::withTrashed()->where('society_id', $society->id)->forceDelete();
            Society::withTrashed()->where('id', $society->id)->forceDelete();
        });
    }

    private function countAdmins(Society $society, int $roleId): int
    {
        return User::withTrashed()
            ->where('society_id', $society->id)
            ->where('role_id', $roleId)
            ->count();
    }

    private function countGatekeepers(Society $society, int $roleId): int
    {
        return User::withTrashed()
            ->where('society_id', $society->id)
            ->where('role_id', $roleId)
            ->count();
    }

    private function countDeliveries(Collection $residentIds): int
    {
        return Delivery::withTrashed()
            ->whereIn('resident_id', $residentIds)
            ->count();
    }

    private function countComplaints($userIds): int
    {
        return Complaint::withTrashed()
            ->whereIn('user_id', $userIds)
            ->count();
    }

    private function countVisitorLogs($flatIds): int
    {
        return VisitorLog::withTrashed()
            ->whereIn('flat_id', $flatIds)
            ->count();
    }

    private function getVisitorSummary($flatIds): array
    {
        $visitorIdsInSociety = VisitorLog::withTrashed()
            ->whereIn('flat_id', $flatIds)
            ->distinct()
            ->pluck('visitor_id');

        $visitorIdsOutsideSociety = VisitorLog::withTrashed()
            ->whereIn('visitor_id', $visitorIdsInSociety)
            ->whereNotIn('flat_id', $flatIds)
            ->distinct()
            ->pluck('visitor_id');

        return [

            'delete' => $visitorIdsInSociety
                ->diff($visitorIdsOutsideSociety)
                ->count(),

            'keep' => $visitorIdsOutsideSociety
                ->count(),
        ];
    }
}
