<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        DROP PROCEDURE IF EXISTS sp_super_admin_dashboard;

        CREATE PROCEDURE sp_super_admin_dashboard()
        BEGIN

            SELECT

                (SELECT COUNT(*) FROM societies) AS total_societies,

                (SELECT COUNT(*) FROM users) AS total_users,

                (SELECT COUNT(*) FROM residents) AS total_residents,

                (
                    SELECT COUNT(*)
                    FROM users u
                    INNER JOIN roles r ON r.id = u.role_id
                    WHERE r.name = 'gatekeeper'
                ) AS total_gatekeepers,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs
                    WHERE DATE(visit_date) = CURDATE()
                ) AS visitors_today,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs
                    WHERE status = 'entered'
                    AND exit_time IS NULL
                ) AS active_visitors,

                (
                    SELECT COUNT(*)
                    FROM complaints
                    WHERE status IN ('open','in_progress')
                ) AS open_complaints,

                (
                    SELECT COUNT(*)
                    FROM deliveries
                    WHERE status = 'received'
                ) AS pending_deliveries;

        END;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_super_admin_dashboard;');
    }
};
