const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const login = async (req, res, next) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Email dan password wajib diisi.'
      });
    }

    const user = await db('users')
      .leftJoin('roles', 'users.role_id', 'roles.id')
      .where('users.email', email)
      .select(
        'users.id',
        'users.name',
        'users.email',
        'users.password_hash',
        'users.role_id',
        'users.phone',
        'users.jabatan',
        'users.avatar',
        'users.is_active',
        db.raw("COALESCE(roles.name, 'Pengguna') as role_name"),
        db.raw("COALESCE(roles.slug, 'super_admin') as role_slug")
      )
      .first();

    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Kredensial tidak valid: Akun tidak ditemukan.'
      });
    }

    if (!user.is_active) {
      return res.status(403).json({
        success: false,
        message: 'Akun Anda dinonaktifkan. Hubungi Administrator HSSE.'
      });
    }

    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      return res.status(401).json({
        success: false,
        message: 'Kredensial tidak valid: Password salah.'
      });
    }

    const secret = process.env.JWT_SECRET || 'pertamina_hsse_ft_maos_secret_token_jwt_2026';
    const token = jwt.sign(
      {
        id: user.id,
        email: user.email,
        role: user.role_slug
      },
      secret,
      { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
    );

    // Audit log
    await logAudit(
      { user, ip: req.ip, headers: req.headers, socket: req.socket },
      'LOGIN',
      'AUTH',
      user.id,
      `User ${user.name} (${user.role_name}) berhasil login.`
    );

    // Remove password_hash from response
    delete user.password_hash;

    res.json({
      success: true,
      message: 'Login berhasil.',
      token,
      user
    });
  } catch (error) {
    next(error);
  }
};

const me = async (req, res, next) => {
  try {
    res.json({
      success: true,
      user: req.user
    });
  } catch (error) {
    next(error);
  }
};

const logout = async (req, res, next) => {
  try {
    if (req.user) {
      await logAudit(req, 'LOGOUT', 'AUTH', req.user.id, `User ${req.user.name} keluar sistem.`);
    }
    res.json({
      success: true,
      message: 'Logout berhasil.'
    });
  } catch (error) {
    next(error);
  }
};

const getDemoAccounts = async (req, res, next) => {
  try {
    const users = await db('users')
      .leftJoin('roles', 'users.role_id', 'roles.id')
      .where('users.is_active', true)
      .select(
        'users.id',
        'users.name',
        'users.email',
        'users.jabatan',
        db.raw("COALESCE(roles.name, 'Pengguna') as role_name"),
        db.raw("COALESCE(roles.slug, 'super_admin') as role_slug")
      );

    res.json({
      success: true,
      data: users.map(u => ({ ...u, default_password: 'password123' }))
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  login,
  me,
  logout,
  getDemoAccounts
};
