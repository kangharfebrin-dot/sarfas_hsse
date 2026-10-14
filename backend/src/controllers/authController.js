const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const db = require('../config/db');
const { logAudit } = require('../middleware/audit');
const { revokeToken } = require('../middleware/auth');

const login = async (req, res, next) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Email dan password wajib diisi.'
      });
    }

    const cleanEmail = String(email).trim().toLowerCase();

    // Query with innerJoin to ensure user has a valid, existing role
    const user = await db('users')
      .innerJoin('roles', 'users.role_id', 'roles.id')
      .where('users.email', cleanEmail)
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
        'roles.name as role_name',
        'roles.slug as role_slug'
      )
      .first();

    // Generic error message to prevent user enumeration
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Email atau kata sandi tidak valid.'
      });
    }

    if (!user.is_active) {
      return res.status(403).json({
        success: false,
        message: 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator HSSE.'
      });
    }

    const isMatch = await bcrypt.compare(password, user.password_hash);
    if (!isMatch) {
      return res.status(401).json({
        success: false,
        message: 'Email atau kata sandi tidak valid.'
      });
    }

    const secret = process.env.JWT_SECRET;
    if (!secret) {
      return res.status(500).json({
        success: false,
        message: 'Konfigurasi keamanan server belum lengkap: JWT_SECRET tidak terdefinisi.'
      });
    }

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
    user.role = user.role_slug;

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
    if (req.token) {
      revokeToken(req.token);
    }
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

/**
 * Return only designated demo accounts when explicitly enabled
 */
const getDemoAccounts = async (req, res, next) => {
  try {
    const isDemoAllowed = process.env.DEMO_MODE === 'true' && process.env.NODE_ENV !== 'production';
    if (!isDemoAllowed) {
      return res.status(403).json({
        success: false,
        message: 'Fitur akun demo dinonaktifkan pada lingkungan ini.'
      });
    }

    const DEMO_EMAILS = [
      'superadmin@pertamina.demo',
      'admin.hsse@pertamina.demo',
      'inspektor.maos@pertamina.demo',
      'verifikator.hsse@pertamina.demo'
    ];

    const users = await db('users')
      .innerJoin('roles', 'users.role_id', 'roles.id')
      .whereIn('users.email', DEMO_EMAILS)
      .where('users.is_active', true)
      .select(
        'users.id',
        'users.name',
        'users.email',
        'users.jabatan',
        'roles.name as role_name',
        'roles.slug as role_slug'
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
