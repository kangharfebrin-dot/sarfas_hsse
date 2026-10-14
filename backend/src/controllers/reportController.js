const db = require('../config/db');

const getReportData = async (req, res, next) => {
  try {
    const { start_date, end_date, category_id, location_id, sarfas_id, inspector_id, status } = req.query;

    let query = db('inspections')
      .join('sarfas_assets', 'inspections.sarfas_id', 'sarfas_assets.id')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('checklist_templates', 'inspections.template_id', 'checklist_templates.id')
      .join('users as inspector', 'inspections.inspector_id', 'inspector.id')
      .leftJoin('users as verifier', 'inspections.verified_by', 'verifier.id')
      .select(
        'inspections.*',
        'sarfas_assets.asset_code',
        'sarfas_assets.name as sarfas_name',
        'sarfas_categories.name as category_name',
        'locations.name as location_name',
        'checklist_templates.title as template_title',
        'inspector.name as inspector_name',
        'verifier.name as verifier_name'
      );

    if (start_date) {
      query = query.where('inspections.inspection_date', '>=', start_date);
    }
    if (end_date) {
      query = query.where('inspections.inspection_date', '<=', end_date);
    }
    if (category_id) {
      query = query.where('sarfas_assets.category_id', category_id);
    }
    if (location_id) {
      query = query.where('sarfas_assets.location_id', location_id);
    }
    if (sarfas_id) {
      query = query.where('inspections.sarfas_id', sarfas_id);
    }
    if (inspector_id) {
      query = query.where('inspections.inspector_id', inspector_id);
    }
    if (status) {
      query = query.where('inspections.status', status);
    }

    const records = await query.orderBy('inspections.inspection_date', 'desc');

    // Attach finding counts for each inspection
    for (let rec of records) {
      const findings = await db('findings').where('inspection_id', rec.id);
      rec.total_findings = findings.length;
      rec.critical_findings = findings.filter(f => f.risk_level === 'KRITIS').length;
    }

    // Summary calculation based STRICTLY on active filter
    const totalInspections = records.length;
    const completedInspections = records.filter(r => r.status === 'SELESAI').length;
    const goodConditionCount = records.filter(r => r.summary_condition === 'BAIK').length;
    const actionNeededCount = records.filter(r => r.summary_condition === 'PERLU_TINDAK_LANJUT').length;
    const criticalCount = records.filter(r => r.summary_condition === 'KRITIS').length;

    const complianceRate = totalInspections > 0 ? Math.round((goodConditionCount / totalInspections) * 100) : 100;

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
          status: status || 'Semua'
        },
        summary: {
          totalInspections,
          completedInspections,
          goodConditionCount,
          actionNeededCount,
          criticalCount,
          complianceRate
        },
        records
      }
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  getReportData
};
