const db = require('../config/db');

const list = async (req, res, next) => {
  try {
    const { module, user_id, limit = 50 } = req.query;

    let query = db('audit_logs')
      .leftJoin('users', 'audit_logs.user_id', 'users.id')
      .select(
        'audit_logs.*',
        'users.name as user_full_name',
        'users.email as user_email'
      );

    if (module) {
      query = query.where('audit_logs.module', module);
    }
    if (user_id) {
      query = query.where('audit_logs.user_id', user_id);
    }

    const logs = await query.orderBy('audit_logs.created_at', 'desc').limit(Number(limit));

    res.json({
      success: true,
      count: logs.length,
      data: logs
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  list
};
