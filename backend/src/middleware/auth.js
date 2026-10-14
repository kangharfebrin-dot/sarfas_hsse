const jwt = require('jsonwebtoken');
const db = require('../config/db');

const revokedTokens = new Set();

/**
 * Revoke an active JWT token upon user logout
 */
const revokeToken = (token) => {
  if (token) {
    revokedTokens.add(token);
    // Cleanup token after 24 hours to prevent memory leak
    setTimeout(() => {
      revokedTokens.delete(token);
    }, 24 * 60 * 60 * 1000).unref();
  }
};

/**
 * Verify JWT token from Authorization header (Bearer token)
 */
const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.startsWith('Bearer ') ? authHeader.split(' ')[1] : null;

    if (!token) {
      return res.status(401).json({
        success: false,
        message: 'Akses ditolak: Token otentikasi tidak ditemukan.'
      });
    }

    if (revokedTokens.has(token)) {
      return res.status(401).json({
        success: false,
        message: 'Sesi telah berakhir: Token telah di-logout. Silakan login kembali.'
      });
    }

    const secret = process.env.JWT_SECRET;
    if (!secret) {
      return res.status(500).json({
        success: false,
        message: 'Konfigurasi server belum lengkap: JWT_SECRET tidak terdefinisi.'
      });
    }

    const decoded = jwt.verify(token, secret);

    const user = await db('users')
      .join('roles', 'users.role_id', 'roles.id')
      .where('users.id', decoded.id)
      .where('users.is_active', true)
      .select(
        'users.id',
        'users.name',
        'users.email',
        'users.role_id',
        'users.phone',
        'users.jabatan',
        'users.avatar',
        'roles.name as role_name',
        'roles.slug as role_slug'
      )
      .first();

    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Pengguna tidak ditemukan atau telah dinonaktifkan.'
      });
    }

    req.user = user;
    req.token = token;
    next();
  } catch (error) {
    return res.status(403).json({
      success: false,
      message: 'Token otentikasi tidak valid atau sudah kedaluwarsa.'
    });
  }
};

/**
 * Authorize specific roles
 * @param {string[]} allowedRoles - Array of role slugs, e.g. ['super_admin', 'admin_hsse']
 */
const authorize = (allowedRoles = []) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ success: false, message: 'Tidak terotentikasi.' });
    }

    if (allowedRoles.length && !allowedRoles.includes(req.user.role_slug)) {
      return res.status(403).json({
        success: false,
        message: `Akses ditolak: Peran '${req.user.role_name || req.user.role_slug}' tidak memiliki wewenang untuk tindakan ini.`
      });
    }

    next();
  };
};

module.exports = {
  authenticateToken,
  authorize,
  revokeToken
};
