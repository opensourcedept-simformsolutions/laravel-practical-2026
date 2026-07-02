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
        DROP PROCEDURE IF EXISTS sp_resident_dashboard;

        CREATE PROCEDURE sp_resident_dashboard(
            IN p_user_id BIGINT,
            IN p_resident_id BIGINT
        )
        BEGIN

            SELECT

                (
                    SELECT COUNT(*)
                    FROM visitor_logs
                    WHERE created_by = p_user_id
                ) AS my_visitor_passes,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs
                    WHERE created_by = p_user_id
                    AND DATE(visit_date)=CURDATE()
                ) AS visitors_expected_today,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs
                    WHERE created_by = p_user_id
                    AND status='entered'
                    AND exit_time IS NULL
                ) AS active_visitors,

                (
                    SELECT COUNT(*)
                    FROM deliveries
                    WHERE resident_id=p_resident_id
                ) AS my_deliveries,

                (
                    SELECT COUNT(*)
                    FROM deliveries
                    WHERE resident_id=p_resident_id
                    AND status='received'
                ) AS pending_deliveries,

                (
                    SELECT COUNT(*)
                    FROM complaints
                    WHERE user_id=p_user_id
                ) AS my_complaints,

                (
                    SELECT COUNT(*)
                    FROM complaints
                    WHERE user_id=p_user_id
                    AND status IN ('open','in_progress')
                ) AS pending_complaints,

                (
                    SELECT COUNT(*)
                    FROM complaints
                    WHERE user_id=p_user_id
                    AND status='resolved'
                ) AS resolved_complaints;

        END;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_resident_dashboard;');
    }
};
