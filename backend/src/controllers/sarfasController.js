const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const list = async (req, res, next) => {
  try {
    const { search, category_id, location_id, condition_status, is_active } = req.query;

    let query = db('sarfas_assets')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .select(
        'sarfas_assets.*',
        'sarfas_categories.name as category_name',
        'sarfas_categories.code as category_code',
        'locations.name as location_name',
        'locations.code as location_code'
      );

    if (search) {
      query = query.where(function() {
        this.where('sarfas_assets.name', 'like', `%${search}%`)
          .orWhere('sarfas_assets.asset_code', 'like', `%${search}%`)
          .orWhere('sarfas_assets.brand_model', 'like', `%${search}%`)
          .orWhere('sarfas_assets.serial_number', 'like', `%${search}%`);
      });
    }

    if (category_id) {
      query = query.where('sarfas_assets.category_id', category_id);
    }

    if (location_id) {
      query = query.where('sarfas_assets.location_id', location_id);
    }

    if (condition_status) {
      query = query.where('sarfas_assets.condition_status', condition_status);
    }

    if (is_active !== undefined) {
      query = query.where('sarfas_assets.is_active', is_active === 'true' || is_active === true || is_active === '1');
    }

    const assets = await query.orderBy('sarfas_assets.asset_code', 'asc');

    res.json({
      success: true,
      count: assets.length,
      data: assets
    });
  } catch (error) {
    next(error);
  }
};

const getOne = async (req, res, next) => {
  try {
    const { id } = req.params;

    const asset = await db('sarfas_assets')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .where('sarfas_assets.id', id)
      .select(
        'sarfas_assets.*',
        'sarfas_categories.name as category_name',
        'sarfas_categories.code as category_code',
        'locations.name as location_name',
        'locations.code as location_code'
      )
      .first();

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: 'Data Sarfas tidak ditemukan.'
      });
    }

    // Riwayat inspeksi
    const inspections = await db('inspections')
      .join('checklist_templates', 'inspections.template_id', 'checklist_templates.id')
      .join('users', 'inspections.inspector_id', 'users.id')
      .where('inspections.sarfas_id', id)
      .select(
        'inspections.id',
        'inspections.inspection_no',
        'inspections.inspection_date',
        'inspections.status',
        'inspections.summary_condition',
        'checklist_templates.title as template_title',
        'users.name as inspector_name'
      )
      .orderBy('inspections.inspection_date', 'desc');

    // Riwayat temuan
    const findings = await db('findings')
      .leftJoin('users', 'findings.assigned_to', 'users.id')
      .where('findings.sarfas_id', id)
      .select(
        'findings.id',
        'findings.finding_no',
        'findings.title',
        'findings.risk_level',
        'findings.status',
        'findings.due_date',
        'users.name as assigned_to_name'
      )
      .orderBy('findings.created_at', 'desc');

    res.json({
      success: true,
      data: {
        ...asset,
        inspection_history: inspections,
        findings_history: findings
      }
    });
  } catch (error) {
    next(error);
  }
};

const create = async (req, res, next) => {
  try {
    const {
      asset_code,
      name,
      category_id,
      location_id,
      serial_number,
      brand_model,
      capacity_specs,
      installation_date,
      condition_status,
      notes,
      photo_url
    } = req.body;

    if (!asset_code || !name || !category_id || !location_id) {
      return res.status(400).json({
        success: false,
        message: 'Kode Sarfas, Nama, Kategori, dan Lokasi wajib diisi.'
      });
    }

    // Check duplicate code
    const existing = await db('sarfas_assets').where('asset_code', asset_code).first();
    if (existing) {
      return res.status(400).json({
        success: false,
        message: `Kode Sarfas '${asset_code}' sudah digunakan oleh aset lain.`
      });
    }

    const [newId] = await db('sarfas_assets').insert({
      asset_code,
      name,
      category_id,
      location_id,
      serial_number: serial_number || null,
      brand_model: brand_model || null,
      capacity_specs: capacity_specs || null,
      installation_date: installation_date || null,
      condition_status: condition_status || 'BAIK',
      notes: notes || null,
      photo_url: photo_url || null,
      is_active: true
    });

    await logAudit(req, 'CREATE_SARFAS', 'SARFAS', newId, `Menambah sarfas baru ${asset_code} - ${name}`);

    res.status(201).json({
      success: true,
      message: 'Sarfas berhasil didaftarkan.',
      data: { id: newId, asset_code, name }
    });
  } catch (error) {
    next(error);
  }
};

const update = async (req, res, next) => {
  try {
    const { id } = req.params;
    const {
      name,
      category_id,
      location_id,
      serial_number,
      brand_model,
      capacity_specs,
      installation_date,
      condition_status,
      is_active,
      notes,
      photo_url
    } = req.body;

    const existing = await db('sarfas_assets').where('id', id).first();
    if (!existing) {
      return res.status(404).json({ success: false, message: 'Sarfas tidak ditemukan.' });
    }

    await db('sarfas_assets').where('id', id).update({
      ...(name && { name }),
      ...(category_id && { category_id }),
      ...(location_id && { location_id }),
      serial_number: serial_number !== undefined ? serial_number : existing.serial_number,
      brand_model: brand_model !== undefined ? brand_model : existing.brand_model,
      capacity_specs: capacity_specs !== undefined ? capacity_specs : existing.capacity_specs,
      installation_date: installation_date !== undefined ? installation_date : existing.installation_date,
      condition_status: condition_status !== undefined ? condition_status : existing.condition_status,
      is_active: is_active !== undefined ? is_active : existing.is_active,
      notes: notes !== undefined ? notes : existing.notes,
      photo_url: photo_url !== undefined ? photo_url : existing.photo_url,
      updated_at: db.fn.now()
    });

    await logAudit(req, 'UPDATE_SARFAS', 'SARFAS', id, `Memperbarui data sarfas ${existing.asset_code}`);

    res.json({
      success: true,
      message: 'Data Sarfas berhasil diperbarui.'
    });
  } catch (error) {
    next(error);
  }
};

const getMeta = async (req, res, next) => {
  try {
    const categories = await db('sarfas_categories').where('is_active', true).orderBy('name', 'asc');
    const locations = await db('locations').where('is_active', true).orderBy('name', 'asc');

    res.json({
      success: true,
      data: {
        categories,
        locations
      }
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  list,
  getOne,
  create,
  update,
  getMeta
};
