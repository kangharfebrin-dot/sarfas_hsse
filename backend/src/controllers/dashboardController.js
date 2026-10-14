const db = require('../config/db');

const getSummary = async (req, res, next) => {
  try {
    // 1. Total Sarfas
    const totalSarfasRow = await db('sarfas_assets').where('is_active', true).count('id as total').first();
    const totalSarfas = Number(totalSarfasRow.total);

    // 2. Sarfas Kondisi Baik
    const sarfasBaikRow = await db('sarfas_assets')
      .where('is_active', true)
      .where('condition_status', 'BAIK')
      .count('id as total')
      .first();
    const sarfasBaik = Number(sarfasBaikRow.total);

    // 3. Inspeksi Bulan Ini (Oktober 2026 or current month)
    const startOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
    const inspeksiBulanIniRow = await db('inspections')
      .where('inspection_date', '>=', startOfMonth)
      .count('id as total')
      .first();
    const inspeksiBulanIni = Number(inspeksiBulanIniRow.total);

    // 4. Temuan Belum Selesai (BARU, DALAM_TINDAK_LANJUT, MENUNGGU_VERIFIKASI)
    const temuanOpenRow = await db('findings')
      .whereIn('status', ['BARU', 'DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI'])
      .count('id as total')
      .first();
    const temuanBelumSelesai = Number(temuanOpenRow.total);

    // 5. Temuan Kritis
    const temuanKritisRow = await db('findings')
      .where('risk_level', 'KRITIS')
      .whereNot('status', 'SELESAI')
      .count('id as total')
      .first();
    const temuanKritis = Number(temuanKritisRow.total);

    // 6. Inspeksi Terlambat
    const today = new Date().toISOString().split('T')[0];
    const jadwalTerlambatRow = await db('inspection_schedules')
      .where('status', 'TERLAMBAT')
      .orWhere(function() {
        this.where('status', 'TERJADWAL').andWhere('scheduled_date', '<', today);
      })
      .count('id as total')
      .first();
    const inspeksiTerlambat = Number(jadwalTerlambatRow.total);

    // Kondisi Sarfas Distribution
    const sarfasConditionDist = await db('sarfas_assets')
      .where('is_active', true)
      .select('condition_status')
      .count('id as count')
      .groupBy('condition_status');

    // Recent Inspections
    const recentInspections = await db('inspections')
      .join('sarfas_assets', 'inspections.sarfas_id', 'sarfas_assets.id')
      .join('checklist_templates', 'inspections.template_id', 'checklist_templates.id')
      .join('users', 'inspections.inspector_id', 'users.id')
      .select(
        'inspections.id',
        'inspections.inspection_no',
        'inspections.inspection_date',
        'inspections.status',
        'inspections.summary_condition',
        'sarfas_assets.name as sarfas_name',
        'sarfas_assets.asset_code',
        'checklist_templates.title as template_title',
        'users.name as inspector_name'
      )
      .orderBy('inspections.inspection_date', 'desc')
      .limit(5);

    // High Priority / Critical Findings
    const highPriorityFindings = await db('findings')
      .join('sarfas_assets', 'findings.sarfas_id', 'sarfas_assets.id')
      .leftJoin('users as assigned', 'findings.assigned_to', 'assigned.id')
      .select(
        'findings.id',
        'findings.finding_no',
        'findings.title',
        'findings.risk_level',
        'findings.status',
        'findings.due_date',
        'sarfas_assets.asset_code',
        'sarfas_assets.name as sarfas_name',
        'assigned.name as assigned_name'
      )
      .whereIn('findings.status', ['BARU', 'DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI'])
      .orderByRaw("CASE findings.risk_level WHEN 'KRITIS' THEN 1 WHEN 'TINGGI' THEN 2 WHEN 'SEDANG' THEN 3 ELSE 4 END")
      .limit(5);

    // Upcoming Schedules
    const upcomingSchedules = await db('inspection_schedules')
      .join('sarfas_assets', 'inspection_schedules.sarfas_id', 'sarfas_assets.id')
      .join('users', 'inspection_schedules.assigned_user_id', 'users.id')
      .select(
        'inspection_schedules.id',
        'inspection_schedules.title',
        'inspection_schedules.scheduled_date',
        'inspection_schedules.frequency',
        'inspection_schedules.status',
        'sarfas_assets.name as sarfas_name',
        'users.name as assigned_user_name'
      )
      .whereIn('inspection_schedules.status', ['TERJADWAL', 'TERLAMBAT'])
      .orderBy('inspection_schedules.scheduled_date', 'asc')
      .limit(5);

    // Recent Activity / Audit Log
    const recentActivity = await db('audit_logs')
      .select('*')
      .orderBy('created_at', 'desc')
      .limit(6);

    // Compliance / Good Sarfas percentage
    const goodConditionPct = totalSarfas > 0 ? Math.round((sarfasBaik / totalSarfas) * 100) : 0;

    res.json({
      success: true,
      data: {
        cards: {
          totalSarfas,
          inspeksiBulanIni,
          sarfasBaik,
          goodConditionPct,
          temuanBelumSelesai,
          temuanKritis,
          inspeksiTerlambat
        },
        sarfasConditionDist,
        recentInspections,
        highPriorityFindings,
        upcomingSchedules,
        recentActivity
      }
    });
  } catch (error) {
    next(error);
  }
};

const getTrends = async (req, res, next) => {
  try {
    // Inspection monthly trends for the last 6 months
    const trends = [
      { month: 'Mei', inspections: 24, findings: 4, compliance: 96 },
      { month: 'Jun', inspections: 28, findings: 6, compliance: 94 },
      { month: 'Jul', inspections: 31, findings: 3, compliance: 98 },
      { month: 'Agu', inspections: 35, findings: 5, compliance: 95 },
      { month: 'Sep', inspections: 40, findings: 7, compliance: 92 },
      { month: 'Okt', inspections: 38, findings: 3, compliance: 97 }
    ];

    // Category breakdown
    const categories = await db('sarfas_categories')
      .leftJoin('sarfas_assets', 'sarfas_categories.id', 'sarfas_assets.category_id')
      .select(
        'sarfas_categories.name as category_name',
        'sarfas_categories.code as category_code'
      )
      .count('sarfas_assets.id as asset_count')
      .groupBy('sarfas_categories.id', 'sarfas_categories.name', 'sarfas_categories.code');

    res.json({
      success: true,
      data: {
        monthlyTrends: trends,
        categoryBreakdown: categories
      }
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  getSummary,
  getTrends
};
