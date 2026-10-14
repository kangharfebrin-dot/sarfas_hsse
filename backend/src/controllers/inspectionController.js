const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const ALLOWED_ANSWERS = ['BAIK', 'TIDAK_BAIK', 'NA'];

/**
 * Calculate next inspection date accurately based on template frequency
 */
function calculateNextInspectionDate(baseDate, frequency) {
  const d = new Date(baseDate);
  switch (frequency) {
    case 'HARIAN':
      d.setDate(d.getDate() + 1);
      break;
    case 'MINGGUAN':
      d.setDate(d.getDate() + 7);
      break;
    case 'BULANAN': {
      const currentDay = d.getDate();
      d.setMonth(d.getMonth() + 1);
      // Handle end of month rollover (e.g. Jan 31 -> Feb 28)
      if (d.getDate() < currentDay) {
        d.setDate(0);
      }
      break;
    }
    case 'TRIWULAN': {
      const currentDay = d.getDate();
      d.setMonth(d.getMonth() + 3);
      if (d.getDate() < currentDay) {
        d.setDate(0);
      }
      break;
    }
    case 'SEMESTER': {
      const currentDay = d.getDate();
      d.setMonth(d.getMonth() + 6);
      if (d.getDate() < currentDay) {
        d.setDate(0);
      }
      break;
    }
    case 'TAHUNAN':
      d.setFullYear(d.getFullYear() + 1);
      break;
    default:
      d.setMonth(d.getMonth() + 1);
      break;
  }
  return d;
}

const list = async (req, res, next) => {
  try {
    const { status, sarfas_id, inspector_id, start_date, end_date } = req.query;

    let query = db('inspections')
      .join('sarfas_assets', 'inspections.sarfas_id', 'sarfas_assets.id')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('checklist_templates', 'inspections.template_id', 'checklist_templates.id')
      .join('users as inspector', 'inspections.inspector_id', 'inspector.id')
      .leftJoin('users as verifier', 'inspections.verified_by', 'verifier.id')
      .select(
        'inspections.*',
        'sarfas_assets.name as sarfas_name',
        'sarfas_assets.asset_code',
        'sarfas_categories.name as category_name',
        'locations.name as location_name',
        'checklist_templates.title as template_title',
        'inspector.name as inspector_name',
        'verifier.name as verifier_name'
      );

    // Role-based scoping: Inspectors see their own inspections unless elevated
    if (req.user && req.user.role_slug === 'inspektor' && !inspector_id) {
      query = query.where('inspections.inspector_id', req.user.id);
    } else if (inspector_id) {
      query = query.where('inspections.inspector_id', inspector_id);
    }

    if (status) {
      query = query.where('inspections.status', status);
    }
    if (sarfas_id) {
      query = query.where('inspections.sarfas_id', sarfas_id);
    }
    if (start_date) {
      query = query.where('inspections.inspection_date', '>=', start_date);
    }
    if (end_date) {
      query = query.where('inspections.inspection_date', '<=', end_date);
    }

    const inspections = await query.orderBy('inspections.inspection_date', 'desc');

    res.json({
      success: true,
      count: inspections.length,
      data: inspections
    });
  } catch (error) {
    next(error);
  }
};

const getOne = async (req, res, next) => {
  try {
    const { id } = req.params;

    const inspection = await db('inspections')
      .join('sarfas_assets', 'inspections.sarfas_id', 'sarfas_assets.id')
      .join('sarfas_categories', 'sarfas_assets.category_id', 'sarfas_categories.id')
      .join('locations', 'sarfas_assets.location_id', 'locations.id')
      .join('checklist_templates', 'inspections.template_id', 'checklist_templates.id')
      .join('users as inspector', 'inspections.inspector_id', 'inspector.id')
      .leftJoin('users as verifier', 'inspections.verified_by', 'verifier.id')
      .where('inspections.id', id)
      .select(
        'inspections.*',
        'sarfas_assets.name as sarfas_name',
        'sarfas_assets.asset_code',
        'sarfas_assets.brand_model',
        'sarfas_assets.serial_number',
        'sarfas_assets.capacity_specs',
        'sarfas_categories.name as category_name',
        'locations.name as location_name',
        'checklist_templates.title as template_title',
        'checklist_templates.version as template_version',
        'checklist_templates.inspection_frequency',
        'inspector.name as inspector_name',
        'verifier.name as verifier_name'
      )
      .first();

    if (!inspection) {
      return res.status(404).json({ success: false, message: 'Inspeksi tidak ditemukan.' });
    }

    // Ownership check on drafts: only creator or admin can view draft
    if (
      inspection.status === 'DRAFT' &&
      req.user.role_slug === 'inspektor' &&
      inspection.inspector_id !== req.user.id
    ) {
      return res.status(403).json({
        success: false,
        message: 'Akses ditolak: Draft inspeksi ini milik petugas lain.'
      });
    }

    // Answers with items & section info
    const answers = await db('inspection_answers')
      .join('checklist_items', 'inspection_answers.checklist_item_id', 'checklist_items.id')
      .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
      .where('inspection_answers.inspection_id', id)
      .select(
        'inspection_answers.*',
        'checklist_items.item_code',
        'checklist_items.question_text',
        'checklist_items.standard_criteria',
        'checklist_items.is_critical',
        'checklist_items.requires_photo',
        'checklist_sections.title as section_title',
        'checklist_sections.id as section_id'
      )
      .orderBy(['checklist_sections.sort_order', 'checklist_items.sort_order']);

    // Related findings created by this inspection
    const findings = await db('findings')
      .where('inspection_id', id)
      .select('*');

    inspection.answers = answers;
    inspection.findings = findings;

    res.json({
      success: true,
      data: inspection
    });
  } catch (error) {
    next(error);
  }
};

/**
 * Save draft inspection or create initial session
 */
const saveDraft = async (req, res, next) => {
  const trx = await db.transaction();
  try {
    const { id } = req.params; // If updating existing draft
    const { sarfas_id, template_id, inspection_date, general_notes, answers } = req.body;
    const inspectorId = req.user.id;

    let inspectionId = id;
    let templateIdToCheck = template_id;

    if (!inspectionId) {
      if (!sarfas_id || !template_id) {
        await trx.rollback();
        return res.status(400).json({
          success: false,
          message: 'sarfas_id dan template_id wajib diisi untuk membuat draft inspeksi.'
        });
      }

      // Check if sarfas is active
      const sarfas = await trx('sarfas_assets').where('id', sarfas_id).first();
      if (!sarfas || !sarfas.is_active) {
        await trx.rollback();
        return res.status(400).json({
          success: false,
          message: 'Inspeksi tidak diizinkan pada sarfas non-aktif.'
        });
      }

      // Create new draft
      const inspectionNo = `INSP-${new Date().getFullYear()}${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(Date.now()).slice(-4)}`;
      const [newId] = await trx('inspections').insert({
        inspection_no: inspectionNo,
        sarfas_id,
        template_id,
        inspector_id: inspectorId,
        inspection_date: inspection_date || new Date().toISOString().split('T')[0],
        status: 'DRAFT',
        summary_condition: 'BAIK',
        general_notes: general_notes || null
      });
      inspectionId = newId;
    } else {
      // Update existing draft
      const existing = await trx('inspections').where('id', inspectionId).first();
      if (!existing) {
        await trx.rollback();
        return res.status(404).json({ success: false, message: 'Inspeksi draft tidak ditemukan.' });
      }

      // IDOR & Ownership Check
      if (req.user.role_slug === 'inspektor' && existing.inspector_id !== req.user.id) {
        await trx.rollback();
        return res.status(403).json({
          success: false,
          message: 'Akses ditolak: Anda tidak memiliki wewenang untuk menyunting draft inspeksi petugas lain.'
        });
      }

      if (existing.status !== 'DRAFT') {
        await trx.rollback();
        return res.status(400).json({
          success: false,
          message: 'Hanya inspeksi berstatus DRAFT yang dapat disunting.'
        });
      }

      templateIdToCheck = existing.template_id;

      await trx('inspections').where('id', inspectionId).update({
        general_notes: general_notes !== undefined ? general_notes : existing.general_notes,
        updated_at: db.fn.now()
      });
    }

    // Validate and save answers
    if (answers && Array.isArray(answers)) {
      // Query valid items that strictly belong to this template
      const validTemplateItems = await trx('checklist_items')
        .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
        .where('checklist_sections.template_id', templateIdToCheck)
        .select('checklist_items.id');

      const validItemIds = new Set(validTemplateItems.map(i => i.id));

      for (let ans of answers) {
        if (!ans.checklist_item_id) continue;

        // Reject item that is not part of this template
        if (!validItemIds.has(Number(ans.checklist_item_id))) {
          await trx.rollback();
          return res.status(400).json({
            success: false,
            message: `Validasi gagal: Item checklist ID ${ans.checklist_item_id} bukan merupakan bagian dari template inspeksi ini.`
          });
        }

        const validAnswer = ALLOWED_ANSWERS.includes(ans.answer) ? ans.answer : 'BAIK';

        const existingAns = await trx('inspection_answers')
          .where('inspection_id', inspectionId)
          .where('checklist_item_id', ans.checklist_item_id)
          .first();

        if (existingAns) {
          await trx('inspection_answers')
            .where('id', existingAns.id)
            .update({
              answer: validAnswer,
              notes: ans.notes || null,
              photo_url: ans.photo_url || null,
              risk_level: ans.risk_level || null,
              updated_at: db.fn.now()
            });
        } else {
          await trx('inspection_answers').insert({
            inspection_id: inspectionId,
            checklist_item_id: ans.checklist_item_id,
            answer: validAnswer,
            notes: ans.notes || null,
            photo_url: ans.photo_url || null,
            risk_level: ans.risk_level || null
          });
        }
      }
    }

    await trx.commit();
    await logAudit(req, 'SAVE_DRAFT_INSPECTION', 'INSPEKSI', inspectionId, `Menyimpan draft inspeksi #${inspectionId}`);

    res.json({
      success: true,
      message: 'Draft inspeksi berhasil disimpan.',
      data: { id: inspectionId, inspection_id: inspectionId, status: 'DRAFT' }
    });
  } catch (error) {
    await trx.rollback();
    next(error);
  }
};

/**
 * Submit Final Inspection
 * Validates completeness, answers whitelist, required photos, notes for TIDAK_BAIK,
 * updates sarfas last_inspection_date using template frequency, and auto-generates findings
 */
const submit = async (req, res, next) => {
  const trx = await db.transaction();
  try {
    const { id } = req.params;
    const { inspector_signature, general_notes, answers } = req.body;
    const inspectorId = req.user.id;

    const inspection = await trx('inspections').where('id', id).first();
    if (!inspection) {
      await trx.rollback();
      return res.status(404).json({ success: false, message: 'Inspeksi tidak ditemukan.' });
    }

    // Ownership check: Inspectors can only submit their own inspections
    if (req.user.role_slug === 'inspektor' && inspection.inspector_id !== inspectorId) {
      await trx.rollback();
      return res.status(403).json({
        success: false,
        message: 'Akses ditolak: Anda hanya dapat mengirimkan inspeksi yang Anda buat.'
      });
    }

    // Idempotency: Prevent double submit
    if (inspection.status !== 'DRAFT' && inspection.status !== 'SEDANG_BERLANGSUNG') {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: `Inspeksi ini telah dikirim sebelumnya dengan status: ${inspection.status}.`
      });
    }

    // Retrieve all required checklist items for this template
    const requiredItems = await trx('checklist_items')
      .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
      .where('checklist_sections.template_id', inspection.template_id)
      .select('checklist_items.*');

    const validItemMap = new Map(requiredItems.map(i => [i.id, i]));

    // Save / update answers if supplied in submit payload
    if (answers && Array.isArray(answers)) {
      for (let ans of answers) {
        if (!validItemMap.has(Number(ans.checklist_item_id))) {
          await trx.rollback();
          return res.status(400).json({
            success: false,
            message: `Validasi gagal: Item checklist ID ${ans.checklist_item_id} bukan merupakan bagian dari template inspeksi ini.`
          });
        }

        if (ans.answer && !ALLOWED_ANSWERS.includes(ans.answer)) {
          await trx.rollback();
          return res.status(400).json({
            success: false,
            message: `Nilai jawaban '${ans.answer}' tidak valid. Harus salah satu dari: ${ALLOWED_ANSWERS.join(', ')}.`
          });
        }

        const validAnswer = ans.answer || 'BAIK';
        const existingAns = await trx('inspection_answers')
          .where('inspection_id', id)
          .where('checklist_item_id', ans.checklist_item_id)
          .first();

        if (existingAns) {
          await trx('inspection_answers').where('id', existingAns.id).update({
            answer: validAnswer,
            notes: ans.notes || null,
            photo_url: ans.photo_url || null,
            risk_level: ans.risk_level || null,
            updated_at: db.fn.now()
          });
        } else {
          await trx('inspection_answers').insert({
            inspection_id: id,
            checklist_item_id: ans.checklist_item_id,
            answer: validAnswer,
            notes: ans.notes || null,
            photo_url: ans.photo_url || null,
            risk_level: ans.risk_level || null
          });
        }
      }
    }

    // Retrieve all recorded answers
    const recordedAnswers = await trx('inspection_answers').where('inspection_id', id);
    const answeredMap = new Map(recordedAnswers.map(a => [a.checklist_item_id, a]));

    const missingItems = [];
    const invalidItems = [];
    const missingPhotoItems = [];
    let hasFailures = false;
    let hasCriticalFailures = false;

    for (let item of requiredItems) {
      const recorded = answeredMap.get(item.id);
      if (!recorded) {
        missingItems.push(item.item_code);
        continue;
      }

      if (!ALLOWED_ANSWERS.includes(recorded.answer)) {
        invalidItems.push(`${item.item_code}: Nilai jawaban '${recorded.answer}' tidak valid.`);
        continue;
      }

      // Condition TIDAK_BAIK requires explanatory notes
      if (recorded.answer === 'TIDAK_BAIK') {
        hasFailures = true;
        if (item.is_critical || recorded.risk_level === 'KRITIS') {
          hasCriticalFailures = true;
        }

        if (!recorded.notes || recorded.notes.trim().length < 5) {
          invalidItems.push(`${item.item_code}: Catatan ketidaksesuaian wajib diisi minimal 5 karakter.`);
        }
      }

      // Mandatory Photo Verification
      if (item.requires_photo) {
        if (!recorded.photo_url || recorded.photo_url.trim().length === 0) {
          missingPhotoItems.push(`${item.item_code}: Item ini mewajibkan unggah bukti foto lapangan.`);
        }
      }
    }

    if (missingItems.length > 0) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: `Terdapat ${missingItems.length} item checklist yang belum dijawab: ${missingItems.slice(0, 5).join(', ')}${missingItems.length > 5 ? '...' : ''}. Seluruh item wajib diisi.`,
        missing_items: missingItems
      });
    }

    if (invalidItems.length > 0) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: 'Validasi kelengkapan inspeksi gagal.',
        errors: invalidItems
      });
    }

    if (missingPhotoItems.length > 0) {
      await trx.rollback();
      return res.status(400).json({
        success: false,
        message: 'Validasi bukti fisik gagal: Beberapa item wajib melampirkan foto.',
        errors: missingPhotoItems
      });
    }

    // Determine summary condition
    let summaryCondition = 'BAIK';
    if (hasCriticalFailures) {
      summaryCondition = 'KRITIS';
    } else if (hasFailures) {
      summaryCondition = 'PERLU_TINDAK_LANJUT';
    }

    // Retrieve template for accurate inspection frequency
    const template = await trx('checklist_templates').where('id', inspection.template_id).first();
    const frequency = template ? template.inspection_frequency : 'BULANAN';
    const nextDate = calculateNextInspectionDate(inspection.inspection_date || new Date(), frequency);

    // Update inspection status to MENUNGGU_REVIEW
    const finalStatus = 'MENUNGGU_REVIEW';
    const signatureRecord = inspector_signature || `KONFIRMASI_DIGITAL_DEMO_${inspectorId}_${Date.now()}`;

    await trx('inspections').where('id', id).update({
      status: finalStatus,
      summary_condition: summaryCondition,
      inspector_signature: signatureRecord,
      general_notes: general_notes || inspection.general_notes,
      updated_at: db.fn.now()
    });

    // Update sarfas asset condition and next inspection date
    let assetCondition = 'BAIK';
    if (hasCriticalFailures) assetCondition = 'RUSAK_BERAT';
    else if (hasFailures) assetCondition = 'PERLU_PERHATIAN';

    await trx('sarfas_assets').where('id', inspection.sarfas_id).update({
      last_inspection_date: db.fn.now(),
      next_inspection_date: nextDate,
      condition_status: assetCondition,
      updated_at: db.fn.now()
    });

    // Auto-create findings for TIDAK_BAIK items idempotently
    const failedAnswers = recordedAnswers.filter(a => a.answer === 'TIDAK_BAIK');
    let findingsCreated = 0;

    for (let fAns of failedAnswers) {
      // Check if finding already exists for this inspection & item to avoid duplicates
      const existingFinding = await trx('findings')
        .where('inspection_id', id)
        .where('checklist_item_id', fAns.checklist_item_id)
        .first();

      if (existingFinding) {
        continue;
      }

      const itemInfo = requiredItems.find(i => i.id === fAns.checklist_item_id);
      const findingNo = `FIND-${new Date().getFullYear()}${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(Date.now()).slice(-4)}${Math.floor(Math.random() * 10)}`;

      const riskLevel = fAns.risk_level || (itemInfo && itemInfo.is_critical ? 'KRITIS' : 'SEDANG');
      const dueDate = new Date();
      dueDate.setDate(dueDate.getDate() + (riskLevel === 'KRITIS' ? 3 : 14));

      const [newFindingId] = await trx('findings').insert({
        finding_no: findingNo,
        inspection_id: id,
        sarfas_id: inspection.sarfas_id,
        checklist_item_id: fAns.checklist_item_id,
        title: `Ketidaksesuaian ${itemInfo ? itemInfo.item_code : 'Inspeksi'}: ${itemInfo ? itemInfo.question_text.slice(0, 80) : 'Temuan sarfas'}`,
        description: fAns.notes || 'Ditemukan item checklist tidak memenuhi standar HSSE.',
        risk_level: riskLevel,
        status: 'BARU',
        reported_by: inspectorId,
        due_date: dueDate.toISOString().split('T')[0],
        photo_before: fAns.photo_url || null
      });

      // Log finding action
      await trx('finding_actions').insert({
        finding_id: newFindingId,
        user_id: inspectorId,
        action_type: 'CREATED',
        notes: `Temuan otomatis dibuat dari hasil submit inspeksi ${inspection.inspection_no}.`,
        photo_proof: fAns.photo_url || null
      });

      findingsCreated++;
    }

    await trx.commit();
    await logAudit(req, 'SUBMIT_INSPECTION', 'INSPEKSI', id, `Mengirim hasil inspeksi ${inspection.inspection_no} dengan status ${summaryCondition}`);

    res.json({
      success: true,
      message: 'Hasil inspeksi berhasil dikirim dan diverifikasi ke sistem HSSE.',
      data: {
        inspection_id: id,
        status: finalStatus,
        summary_condition: summaryCondition,
        findings_created: findingsCreated,
        next_inspection_date: nextDate.toISOString().split('T')[0]
      }
    });
  } catch (error) {
    await trx.rollback();
    next(error);
  }
};

/**
 * Review / Approve inspection (Approver / Admin HSSE role)
 */
const review = async (req, res, next) => {
  try {
    const { id } = req.params;
    const { action, verification_notes } = req.body; // 'APPROVE' or 'REJECT'
    const verifierId = req.user.id;

    if (!action || !['APPROVE', 'REJECT'].includes(action)) {
      return res.status(400).json({
        success: false,
        message: "Tindakan review tidak valid. Gunakan 'APPROVE' atau 'REJECT'."
      });
    }

    const inspection = await db('inspections').where('id', id).first();
    if (!inspection) {
      return res.status(404).json({ success: false, message: 'Inspeksi tidak ditemukan.' });
    }

    if (inspection.status === 'DRAFT') {
      return res.status(400).json({
        success: false,
        message: 'Inspeksi berstatus DRAFT belum dapat di-review sebelum dikirim.'
      });
    }

    const newStatus = action === 'APPROVE' ? 'SELESAI' : 'DITOLAK';

    await db('inspections').where('id', id).update({
      status: newStatus,
      verified_by: verifierId,
      verified_at: db.fn.now(),
      verification_notes: verification_notes || (action === 'APPROVE' ? 'Hasil inspeksi disetujui.' : 'Hasil inspeksi dikembalikan/ditolak.'),
      updated_at: db.fn.now()
    });

    await logAudit(req, 'REVIEW_INSPECTION', 'INSPEKSI', id, `Verifikator ${req.user.name} mengubah status inspeksi menjadi ${newStatus}`);

    res.json({
      success: true,
      message: `Inspeksi berhasil ${action === 'APPROVE' ? 'disetujui' : 'ditolak/dikembalikan'}.`,
      data: { id, status: newStatus }
    });
  } catch (error) {
    next(error);
  }
};

module.exports = {
  list,
  getOne,
  saveDraft,
  submit,
  review
};
