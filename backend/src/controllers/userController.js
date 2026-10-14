const bcrypt = require('bcryptjs');
const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const list = async (req, res, next) => {
  try {
    const users = await db('users')
      .join('roles', 'users.role_id', 'roles.id')
      .select(
        'users.id',
        'users.name',
        'users.email',
        'users.role_id',
        'users.phone',
        'users.jabatan',
        'users.avatar',
        'users.is_active',
        'users.created_at',
        'roles.name as role_name',
        'roles.slug as role_slug'
      )
      .orderBy('users.id', 'asc');

    const roles = await db('roles').select('*');

    res.json({
      success: true,
      data: {
        users,
        roles
      }
    });
  } catch (error) {
    next(error);
  }
};

const create = async (req, res, next) => {
  try {
    const { name, email, password, role_id, phone, jabatan } = req.body;

    if (!name || !email || !password || !role_id) {
      return res.status(400).json({ success: false, message: 'Nama, email, password, dan peran wajib diisi.' });
    }

    const existing = await db('users').where('email', email).first();
    if (existing) {
      return res.status(400).json({ success: false, message: 'Email sudah terdaftar.' });
    }

    const password_hash = await bcrypt.hash(password, 10);
    const [newId] = await db('users').insert({
      name,
      email,
      password_hash,
      role_id,
      phone: phone || null,
      jabatan: jabatan || null,
      is_active: true
    });

    await logAudit(req, 'CREATE_USER', 'USER_MGMT', newId, `Menambah akun user baru: ${email}`);

    res.status(201).json({
      success: true,
      message: 'Akun pengguna berhasil dibuat.',
      data: { id: newId, name, email }
    });
  } catch (error) {
    next(error);
  }
};

const update = async (req, res, next) => {
  try {
    const { id } = req.params;
    const { name, role_id, phone, jabatan, is_active, password } = req.body;

    const user = await db('users').where('id', id).first();
    if (!user) {
      return res.status(404).json({ success: false, message: 'Pengguna tidak ditemukan.' });
    }

    const updates = {
      ...(name && { name }),
      ...(role_id && { role_id }),
      ...(phone !== undefined && { phone }),
      ...(jabatan !== undefined && { jabatan }),
      ...(is_active !== undefined && { is_active }),
      updated_at: db.fn.now()
    };

    if (password) {
      updates.password_hash = await bcrypt.hash(password, 10);
    }

    await db('users').where('id', id).update(updates);
    await logAudit(req, 'UPDATE_USER', 'USER_MGMT', id, `Memperbarui akun user #${id} (${user.email})`);

    res.json({
      success: true,
      message: 'Data pengguna berhasil diperbarui.'
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
