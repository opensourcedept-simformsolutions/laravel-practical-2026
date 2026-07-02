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
        DROP PROCEDURE IF EXISTS sp_gatekeeper_dashboard;

        CREATE PROCEDURE sp_gatekeeper_dashboard(
            IN p_society_id BIGINT
        )
        BEGIN

            SELECT

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                    ON f.id=vl.flat_id
                    WHERE f.society_id=p_society_id
                    AND DATE(vl.visit_date)=CURDATE()
                ) AS visitors_expected_today,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                    ON f.id=vl.flat_id
                    WHERE f.society_id=p_society_id
                    AND DATE(vl.visit_date)=CURDATE()
                    AND vl.status='entered'
                ) AS entries_today,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                    ON f.id=vl.flat_id
                    WHERE f.society_id=p_society_id
                    AND DATE(vl.visit_date)=CURDATE()
                    AND vl.status='exited'
                ) AS exits_today,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                    ON f.id=vl.flat_id
                    WHERE f.society_id=p_society_id
                    AND vl.status='entered'
                    AND vl.exit_time IS NULL
                ) AS visitors_inside_now,

                (
                    SELECT COUNT(*)
                    FROM deliveries d
                    INNER JOIN flats f
                    ON f.id=d.flat_id
                    WHERE f.society_id=p_society_id
                    AND d.status='pending'
                ) AS pending_deliveries,

                (
                    SELECT COUNT(*)
                    FROM deliveries d
                    INNER JOIN flats f
                    ON f.id=d.flat_id
                    WHERE f.society_id=p_society_id
                    AND DATE(d.created_at)=CURDATE()
                ) AS deliveries_received,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                    ON f.id=vl.flat_id
                    WHERE f.society_id=p_society_id
                    AND vl.status='entered'
                ) AS passes_verified,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                    ON f.id=vl.flat_id
                    WHERE f.society_id=p_society_id
                    AND vl.status='rejected'
                ) AS rejected_entries;

        END;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_gatekeeper_dashboard;');
    }
};
