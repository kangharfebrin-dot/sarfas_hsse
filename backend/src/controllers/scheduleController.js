const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const list = async (req, res, next) => {
  try {
    const { status, sarfas_id, assigned_user_id, month, year } = req.query;

    let query = db('inspection_schedules')
      .join('sarfas_assets', 'inspection_schedules.sarfas_id', 'sarfas_assets.id')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('checklist_templates', 'inspection_schedules.template_id', 'checklist_templates.id')
      .join('users as assignee', 'inspection_schedules.assigned_user_id', 'assignee.id')
      .select(
        'inspection_schedules.*',
        'sarfas_assets.asset_code',
        'sarfas_assets.name as sarfas_name',
        'sarfas_categories.name as category_name',
        'locations.name as location_name',
        'checklist_templates.title as template_title',
        'assignee.name as assigned_user_name'
      );

    if (status) {
      query = query.where('inspection_schedules.status', status);
    }
    if (sarfas_id) {
      query = query.where('inspection_schedules.sarfas_id', sarfas_id);
    }
    if (assigned_user_id) {
      query = query.where('inspection_schedules.assigned_user_id', assigned_user_id);
    }
    if (month && year) {
      const start = `${year}-${String(month).padStart(2, '0')}-01`;
      const end = `${year}-${String(month).padStart(2, '0')}-31`;
      query = query.whereBetween('inspection_schedules.scheduled_date', [start, end]);
    }

    const schedules = await query.orderBy('inspection_schedules.scheduled_date', 'asc');

    res.json({
      success: true,
      count: schedules.length,
      data: schedules
    });
  } catch (error) {
    next(error);
  }
};

const create = async (req, res, next) => {
  try {
    const { title, sarfas_id, template_id, assigned_user_id, scheduled_date, frequency, notes } = req.body;

    if (!title || !sarfas_id || !template_id || !assigned_user_id || !scheduled_date) {
      return res.status(400).json({ success: false, message: 'Harap lengkapi semua field jadwal yang wajib.' });
    }

    // Check conflict: Same sarfas on same date
    const conflict = await db('inspection_schedules')
      .where('sarfas_id', sarfas_id)
      .where('scheduled_date', scheduled_date)
      .whereNot('status', 'DIBATALKAN')
      .first();

    if (conflict) {
      return res.status(400).json({
        success: false,
        message: 'Peringatan: Jadwal inspeksi untuk sarfas ini pada tanggal tersebut sudah ada.'
      });
    }

    const [newId] = await db('inspection_schedules').insert({
      title,
      sarfas_id,
      template_id,
      assigned_user_id,
      scheduled_date,
      frequency: frequency || 'BULANAN',
      status: 'TERJADWAL',
      notes: notes || null
    });

    await logAudit(req, 'CREATE_SCHEDULE', 'JADWAL', newId, `Membuat jadwal inspeksi: ${title}`);

    res.status(201).json({
      success: true,
      message: 'Jadwal inspeksi berhasil ditambahkan.',
      data: { id: newId }
    });
  } catch (error) {
    next(error);
  }
};

const update = async (req, res, next) => {
  try {
    const { id } = req.params;
    const { status, scheduled_date, assigned_user_id, notes } = req.body;

    const schedule = await db('inspection_schedules').where('id', id).first();
    if (!schedule) {
      return res.status(404).json({ success: false, message: 'Jadwal tidak ditemukan.' });
    }

    await db('inspection_schedules').where('id', id).update({
      ...(status && { status }),
      ...(scheduled_date && { scheduled_date }),
      ...(assigned_user_id && { assigned_user_id }),
      ...(notes !== undefined && { notes }),
      updated_at: db.fn.now()
    });

    await logAudit(req, 'UPDATE_SCHEDULE', 'JADWAL', id, `Memperbarui jadwal #${id}`);

    res.json({
      success: true,
      message: 'Jadwal berhasil diperbarui.'
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  list,
  create,
  update
};
