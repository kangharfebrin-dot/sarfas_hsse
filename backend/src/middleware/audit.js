const db = require('../config/db');

/**
 * Log an action to audit_logs
 */
const logAudit = async (req, action, moduleName, recordId = null, details = null) => {
  try {
    const userId = req.user ? req.user.id : null;
    const userName = req.user ? req.user.name : 'System';
    const ip = req.ip || req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    await db('audit_logs').insert({
      user_id: userId,
      user_name: userName,
      action: action,
      module: moduleName,
      record_id: recordId ? String(recordId) : null,
      details: typeof details === 'object' ? JSON.stringify(details) : details,
      ip_address: ip
    });
  } catch (err) {
    console.error('Failed to write audit log:', err.message);
  }
};

module.exports = {
  logAudit
};
