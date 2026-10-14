const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const VALID_FREQUENCIES = ['HARIAN', 'MINGGUAN', 'BULANAN', 'TRIWULAN', 'SEMESTER', 'TAHUNAN'];
const VALID_STATUSES = ['TERJADWAL', 'SELESAI', 'TERLAMBAT', 'DIBATALKAN'];

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
      const m = parseInt(month, 10);
      const y = parseInt(year, 10);
      if (m >= 1 && m <= 12 && y >= 2000) {
        // Accurately determine the last day of month (including February leap year)
        const lastDay = new Date(y, m, 0).getDate();
        const start = `${y}-${String(m).padStart(2, '0')}-01`;
        const end = `${y}-${String(m).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
        query = query.whereBetween('inspection_schedules.scheduled_date', [start, end]);
      }
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
  const trx = await db.transaction();
  try {
    const { title, sarfas_id, template_id, assigned_user_id, scheduled_date, frequency, notes } = req.body;

    if (!title || !sarfas_id || !template_id || !assigned_user_id || !scheduled_date) {
      await trx.rollback();
      return res.status(400).json({ success: false, message: 'Harap lengkapi seluruh field jadwal yang wajib diisi.' });
    }

    // Validate assigned user exists, is active, and has appropriate role
    const assignee = await trx('users')
      .join('roles', 'users.role_id', 'roles.id')
      .where('users.id', assigned_user_id)
      .where('users.is_active', true)
      .first();

    if (!assignee) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: 'Petugas yang ditugaskan tidak ditemukan atau berstatus nonaktif.'
      });
    }

    // Validate Sarfas exists and is active
    const sarfas = await trx('sarfas_assets').where('id', sarfas_id).first();
    if (!sarfas || !sarfas.is_active) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: 'Sarfas tidak ditemukan atau berstatus nonaktif.'
      });
    }

    // Validate Checklist Template exists and is active
    const template = await trx('checklist_templates').where('id', template_id).first();
    if (!template || !template.is_active) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: 'Template checklist tidak ditemukan atau berstatus nonaktif.'
      });
    }

    // Check conflict: Same sarfas on same date (concurrency protected inside transaction)
    const conflict = await trx('inspection_schedules')
      .where('sarfas_id', sarfas_id)
      .where('scheduled_date', scheduled_date)
      .whereNot('status', 'DIBATALKAN')
      .first();

    if (conflict) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: `Konflik Jadwal: Sarfas ini telah memiliki jadwal aktif pada tanggal ${scheduled_date}.`
      });
    }

    const freqValue = VALID_FREQUENCIES.includes(frequency) ? frequency : (template.inspection_frequency || 'BULANAN');

    const [newId] = await trx('inspection_schedules').insert({
      title,
      sarfas_id,
      template_id,
      assigned_user_id,
      scheduled_date,
      frequency: freqValue,
      status: 'TERJADWAL',
      notes: notes || null
    });

    await trx.commit();
    await logAudit(req, 'CREATE_SCHEDULE', 'JADWAL', newId, `Membuat jadwal inspeksi: ${title}`);

    res.status(201).json({
      success: true,
      message: 'Jadwal inspeksi berhasil ditambahkan.',
      data: { id: newId }
    });
  } catch (error) {
    await trx.rollback();
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

    if (status && !VALID_STATUSES.includes(status)) {
      return res.status(400).json({
        success: false,
        message: `Status jadwal tidak valid. Nilai yang diperbolehkan: ${VALID_STATUSES.join(', ')}.`
      });
    }

    if (assigned_user_id) {
      const assignee = await db('users').where('id', assigned_user_id).where('is_active', true).first();
      if (!assignee) {
        return res.status(400).json({
          success: false,
          message: 'Petugas yang ditugaskan tidak valid atau berstatus nonaktif.'
        });
      }
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
