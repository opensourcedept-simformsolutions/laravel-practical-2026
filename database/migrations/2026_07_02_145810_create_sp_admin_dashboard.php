<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        DROP PROCEDURE IF EXISTS sp_admin_dashboard;

        CREATE PROCEDURE sp_admin_dashboard(
            IN p_society_id BIGINT
        )
        BEGIN

            SELECT

                (
                    SELECT COUNT(*)
                    FROM residents r
                    INNER JOIN users u
                        ON u.id = r.user_id
                    WHERE u.society_id = p_society_id
                ) AS residents,

                (
                    SELECT COUNT(*)
                    FROM flats
                    WHERE society_id = p_society_id
                ) AS flats,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                        ON f.id = vl.flat_id
                    WHERE f.society_id = p_society_id
                    AND DATE(vl.visit_date) = CURDATE()
                ) AS visitors_today,

                (
                    SELECT COUNT(*)
                    FROM visitor_logs vl
                    INNER JOIN flats f
                        ON f.id = vl.flat_id
                    WHERE f.society_id = p_society_id
                    AND vl.status = 'entered'
                    AND vl.exit_time IS NULL
                ) AS visitors_inside,

                (
                    SELECT COUNT(*)
                    FROM deliveries d
                    INNER JOIN flats f
                        ON f.id = d.flat_id
                    WHERE f.society_id = p_society_id
                    AND DATE(d.created_at) = CURDATE()
                ) AS deliveries_today,

                (
                    SELECT COUNT(*)
                    FROM deliveries d
                    INNER JOIN flats f
                        ON f.id = d.flat_id
                    WHERE f.society_id = p_society_id
                    AND d.status = 'pending'
                ) AS pending_deliveries,

                (
                    SELECT COUNT(*)
                    FROM complaints c
                    INNER JOIN users u
                        ON u.id = c.user_id
                    WHERE u.society_id = p_society_id
                    AND c.status IN ('open','in_progress')
                ) AS open_complaints,

                (
                    SELECT COUNT(*)
                    FROM complaints c
                    INNER JOIN users u
                        ON u.id = c.user_id
                    WHERE u.society_id = p_society_id
                    AND c.status = 'resolved'
                ) AS resolved_complaints;

        END;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_admin_dashboard;
        ');
    }
};
