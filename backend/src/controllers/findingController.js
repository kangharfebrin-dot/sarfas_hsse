const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const list = async (req, res, next) => {
  try {
    const { status, risk_level, sarfas_id, search } = req.query;

    let query = db('findings')
      .join('sarfas_assets', 'findings.sarfas_id', 'sarfas_assets.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('users as reporter', 'findings.reported_by', 'reporter.id')
      .leftJoin('users as assigned', 'findings.assigned_to', 'assigned.id')
      .leftJoin('users as verifier', 'findings.verified_by', 'verifier.id')
      .select(
        'findings.*',
        'sarfas_assets.asset_code',
        'sarfas_assets.name as sarfas_name',
        'locations.name as location_name',
        'reporter.name as reported_by_name',
        'assigned.name as assigned_to_name',
        'verifier.name as verified_by_name'
      );

    if (status) {
      if (status === 'OPEN') {
        query = query.whereIn('findings.status', ['BARU', 'DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI']);
      } else {
        query = query.where('findings.status', status);
      }
    }

    if (risk_level) {
      query = query.where('findings.risk_level', risk_level);
    }

    if (sarfas_id) {
      query = query.where('findings.sarfas_id', sarfas_id);
    }

    if (search) {
      query = query.where(function() {
        this.where('findings.finding_no', 'like', `%${search}%`)
          .orWhere('findings.title', 'like', `%${search}%`)
          .orWhere('findings.description', 'like', `%${search}%`)
          .orWhere('sarfas_assets.name', 'like', `%${search}%`)
          .orWhere('sarfas_assets.asset_code', 'like', `%${search}%`);
      });
    }

    const findings = await query.orderByRaw(
      "CASE findings.risk_level WHEN 'KRITIS' THEN 1 WHEN 'TINGGI' THEN 2 WHEN 'SEDANG' THEN 3 ELSE 4 END, findings.created_at DESC"
    );

    res.json({
      success: true,
      count: findings.length,
      data: findings
    });
  } catch (error) {
    next(error);
  }
};

const getOne = async (req, res, next) => {
  try {
    const { id } = req.params;

    const finding = await db('findings')
      .join('sarfas_assets', 'findings.sarfas_id', 'sarfas_assets.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('users as reporter', 'findings.reported_by', 'reporter.id')
      .leftJoin('users as assigned', 'findings.assigned_to', 'assigned.id')
      .leftJoin('users as verifier', 'findings.verified_by', 'verifier.id')
      .leftJoin('checklist_items', 'findings.checklist_item_id', 'checklist_items.id')
      .where('findings.id', id)
      .select(
        'findings.*',
        'sarfas_assets.asset_code',
        'sarfas_assets.name as sarfas_name',
        'sarfas_categories.name as category_name',
        'locations.name as location_name',
        'reporter.name as reported_by_name',
        'assigned.name as assigned_to_name',
        'verifier.name as verified_by_name',
        'checklist_items.item_code',
        'checklist_items.question_text'
      )
      .first();

    if (!finding) {
      return res.status(404).json({ success: false, message: 'Temuan tidak ditemukan.' });
    }

    // Riwayat tindakan/timeline
    const actions = await db('finding_actions')
      .join('users', 'finding_actions.user_id', 'users.id')
      .where('finding_actions.finding_id', id)
      .select(
        'finding_actions.*',
        'users.name as user_name',
        'users.jabatan'
      )
      .orderBy('finding_actions.created_at', 'asc');

    finding.timeline = actions;

    res.json({
      success: true,
      data: finding
    });
  } catch (error) {
    next(error);
  }
};

/**
 * Update Corrective Action / Progress
 */
const updateAction = async (req, res, next) => {
  const trx = await db.transaction();
  try {
    const { id } = req.params;
    const {
      status, // 'DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI'
      assigned_to,
      due_date,
      corrective_action_plan,
      corrective_action_notes,
      photo_after,
      action_note
    } = req.body;
    const userId = req.user.id;

    const finding = await trx('findings').where('id', id).first();
    if (!finding) {
      await trx.rollback();
      return res.status(404).json({ success: false, message: 'Temuan tidak ditemukan.' });
    }

    if (finding.status === 'SELESAI') {
      await trx.rollback();
      return res.status(400).json({ success: false, message: 'Temuan yang telah berstatus SELESAI tidak dapat diubah lagi.' });
    }

    const updates = {
      updated_at: db.fn.now()
    };

    if (assigned_to !== undefined) updates.assigned_to = assigned_to;
    if (due_date !== undefined) updates.due_date = due_date;
    if (corrective_action_plan !== undefined) updates.corrective_action_plan = corrective_action_plan;
    if (corrective_action_notes !== undefined) updates.corrective_action_notes = corrective_action_notes;
    if (photo_after !== undefined) updates.photo_after = photo_after;
    if (status && ['DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI'].includes(status)) {
      updates.status = status;
    }

    await trx('findings').where('id', id).update(updates);

    // Record action log
    let actionType = 'IN_PROGRESS';
    if (status === 'MENUNGGU_VERIFIKASI') actionType = 'SUBMITTED_RESOLUTION';
    else if (assigned_to && assigned_to !== finding.assigned_to) actionType = 'ASSIGNED';

    await trx('finding_actions').insert({
      finding_id: id,
      user_id: userId,
      action_type: actionType,
      notes: action_note || corrective_action_notes || 'Pembaruan tindakan tindak lanjut.',
      photo_proof: photo_after || null
    });

    await trx.commit();
    await logAudit(req, 'UPDATE_FINDING_ACTION', 'TEMUAN', id, `Memperbarui progres temuan ${finding.finding_no} -> ${updates.status || finding.status}`);

    res.json({
      success: true,
      message: 'Progres tindak lanjut berhasil diperbarui.',
      data: { id, status: updates.status || finding.status }
    });
  } catch (error) {
    await trx.rollback();
    next(error);
  }
};

/**
 * Verify and close finding (Only Approver or Admin HSSE)
 */
const verifyClose = async (req, res, next) => {
  const trx = await db.transaction();
  try {
    const { id } = req.params;
    const { action, verification_notes, photo_proof } = req.body; // 'VERIFY_CLOSE' or 'REJECT'
    const verifierId = req.user.id;

    const finding = await trx('findings').where('id', id).first();
    if (!finding) {
      await trx.rollback();
      return res.status(404).json({ success: false, message: 'Temuan tidak ditemukan.' });
    }

    if (action === 'VERIFY_CLOSE') {
      await trx('findings').where('id', id).update({
        status: 'SELESAI',
        verified_by: verifierId,
        verified_at: db.fn.now(),
        verification_notes: verification_notes || 'Verifikasi lapangan telah disetujui. Kondisi sarfas kembali normal.',
        updated_at: db.fn.now()
      });

      await trx('finding_actions').insert({
        finding_id: id,
        user_id: verifierId,
        action_type: 'VERIFIED_CLOSED',
        notes: verification_notes || 'Temuan diverifikasi dan resmi ditutup.',
        photo_proof: photo_proof || null
      });

      // If no other open findings for this sarfas, restore sarfas condition to BAIK
      const openCount = await trx('findings')
        .where('sarfas_id', finding.sarfas_id)
        .whereNot('id', id)
        .whereIn('status', ['BARU', 'DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI'])
        .count('id as total')
        .first();

      if (Number(openCount.total) === 0) {
        await trx('sarfas_assets').where('id', finding.sarfas_id).update({
          condition_status: 'BAIK',
          updated_at: db.fn.now()
        });
      }
    } else {
      // Reject resolution, return to DALAM_TINDAK_LANJUT
      await trx('findings').where('id', id).update({
        status: 'DALAM_TINDAK_LANJUT',
        verification_notes: verification_notes || 'Hasil perbaikan belum memenuhi standar. Harap perbaiki ulang.',
        updated_at: db.fn.now()
      });

      await trx('finding_actions').insert({
        finding_id: id,
        user_id: verifierId,
        action_type: 'REJECTED',
        notes: verification_notes || 'Verifikasi perbaikan ditolak. Butuh perbaikan ulang.',
        photo_proof: null
      });
    }

    await trx.commit();
    await logAudit(
      req,
      action === 'VERIFY_CLOSE' ? 'VERIFY_CLOSE_FINDING' : 'REJECT_FINDING_RESOLUTION',
      'TEMUAN',
      id,
      `Verifikator ${req.user.name} memproses temuan ${finding.finding_no} -> ${action === 'VERIFY_CLOSE' ? 'SELESAI' : 'DIKEMBALIKAN'}`
    );

    res.json({
      success: true,
      message: action === 'VERIFY_CLOSE' ? 'Temuan berhasil diverifikasi dan ditutup.' : 'Tindak lanjut dikembalikan untuk perbaikan ulang.',
      data: { id, status: action === 'VERIFY_CLOSE' ? 'SELESAI' : 'DALAM_TINDAK_LANJUT' }
    });
  } catch (error) {
    await trx.rollback();
    next(error);
  }
};

module.exports = {
  list,
  getOne,
  updateAction,
  verifyClose
};
