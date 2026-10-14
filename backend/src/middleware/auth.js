const jwt = require('jsonwebtoken');
const db = require('../config/db');

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

    const secret = process.env.JWT_SECRET || 'pertamina_hsse_ft_maos_secret_token_jwt_2026';
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
        message: 'Pengguna tidak ditemukan atau dinonaktifkan.'
      });
    }

    req.user = user;
    next();
  } catch (error) {
    return res.status(403).json({
      success: false,
      message: 'Token tidak valid atau sudah kedaluwarsa.',
      error: error.message
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
        message: `Akses ditolak: Peran '${req.user.role_name}' tidak memiliki wewenang untuk tindakan ini.`
      });
    }

    next();
  };
};

module.exports = {
  authenticateToken,
  authorize
};
