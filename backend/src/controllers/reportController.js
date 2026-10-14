const db = require('../config/db');

const getReportData = async (req, res, next) => {
  try {
    const {
      start_date,
      end_date,
      category_id,
      location_id,
      sarfas_id,
      inspector_id,
      status,
      page,
      limit
    } = req.query;

    let baseQuery = db('inspections')
      .join('sarfas_assets', 'inspections.sarfas_id', 'sarfas_assets.id')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('checklist_templates', 'inspections.template_id', 'checklist_templates.id')
      .join('users as inspector', 'inspections.inspector_id', 'inspector.id')
      .leftJoin('users as verifier', 'inspections.verified_by', 'verifier.id')
      .leftJoin('findings', 'inspections.id', 'findings.inspection_id');

    if (start_date) {
      baseQuery = baseQuery.where('inspections.inspection_date', '>=', start_date);
    }
    if (end_date) {
      baseQuery = baseQuery.where('inspections.inspection_date', '<=', end_date);
    }
    if (category_id) {
      baseQuery = baseQuery.where('sarfas_assets.category_id', category_id);
    }
    if (location_id) {
      baseQuery = baseQuery.where('sarfas_assets.location_id', location_id);
    }
    if (sarfas_id) {
      baseQuery = baseQuery.where('inspections.sarfas_id', sarfas_id);
    }
    if (inspector_id) {
      baseQuery = baseQuery.where('inspections.inspector_id', inspector_id);
    }
    if (status) {
      baseQuery = baseQuery.where('inspections.status', status);
    }

    // Single aggregated query to eliminate N+1 finding count queries
    const records = await baseQuery
      .select(
        'inspections.*',
        'sarfas_assets.asset_code',
        'sarfas_assets.name as sarfas_name',
        'sarfas_categories.name as category_name',
        'locations.name as location_name',
        'checklist_templates.title as template_title',
        'inspector.name as inspector_name',
        'verifier.name as verifier_name',
        db.raw('COUNT(findings.id) as total_findings'),
        db.raw("COUNT(CASE WHEN findings.risk_level = 'KRITIS' THEN 1 END) as critical_findings")
      )
      .groupBy(
        'inspections.id',
        'sarfas_assets.asset_code',
        'sarfas_assets.name',
        'sarfas_categories.name',
        'locations.name',
        'checklist_templates.title',
        'inspector.name',
        'verifier.name'
      )
      .orderBy('inspections.inspection_date', 'desc');

    // Convert aggregated counts to integers
    for (let r of records) {
      r.total_findings = Number(r.total_findings) || 0;
      r.critical_findings = Number(r.critical_findings) || 0;
    }

    // Accurate summary calculation based strictly on the active filtered records
    const totalInspections = records.length;
    const completedInspections = records.filter(r => r.status === 'SELESAI').length;
    const goodConditionCount = records.filter(r => r.summary_condition === 'BAIK').length;
    const actionNeededCount = records.filter(r => r.summary_condition === 'PERLU_TINDAK_LANJUT').length;
    const criticalCount = records.filter(r => r.summary_condition === 'KRITIS').length;

    // Do NOT produce 100% compliance when total inspections is 0
    const complianceRate = totalInspections > 0 ? Math.round((goodConditionCount / totalInspections) * 100) : 0;
    const complianceStatus = totalInspections > 0 ? `${complianceRate}%` : 'Belum Ada Data';

    // Pagination support if requested
    let paginatedRecords = records;
    let paginationMeta = null;

    if (page && limit) {
      const pageNum = Math.max(1, parseInt(page, 10));
      const limitNum = Math.max(1, parseInt(limit, 10));
      const offset = (pageNum - 1) * limitNum;
      paginatedRecords = records.slice(offset, offset + limitNum);
      paginationMeta = {
        currentPage: pageNum,
        page: pageNum,
        perPage: limitNum,
        limit: limitNum,
        totalItems: totalInspections,
        total: totalInspections,
        totalPages: Math.ceil(totalInspections / limitNum)
      };
    }

    res.json({
      success: true,
      data: {
        generated_at: new Date().toISOString(),
        generated_by: req.user ? req.user.name : 'System User',
        filters: {
          start_date: start_date || 'Semua',
          end_date: end_date || 'Semua',
          category_id: category_id || 'Semua',
          location_id: location_id || 'Semua',
          sarfas_id: sarfas_id || 'Semua',
          status: status || 'Semua'
        },
        summary: {
          totalInspections,
          completedInspections,
          goodConditionCount,
          actionNeededCount,
          criticalCount,
          complianceRate,
          complianceStatus
        },
        pagination: paginationMeta,
        records: paginatedRecords
      }
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  getReportData
};
