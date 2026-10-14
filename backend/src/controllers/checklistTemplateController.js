const db = require('../config/db');
const { logAudit } = require('../middleware/audit');

const list = async (req, res, next) => {
  try {
    const { category_id, is_active } = req.query;

    let query = db('checklist_templates')
      .join('sarfas_categories', 'checklist_templates.category_id', 'sarfas_categories.id')
      .select(
        'checklist_templates.*',
        'sarfas_categories.name as category_name',
        'sarfas_categories.code as category_code'
      );

    if (category_id) {
      query = query.where('checklist_templates.category_id', category_id);
    }

    if (is_active !== undefined) {
      query = query.where('checklist_templates.is_active', is_active === 'true' || is_active === true || is_active === '1');
    }

    const templates = await query.orderBy('checklist_templates.title', 'asc');

    // Attach item counts
    for (let tmpl of templates) {
      const itemsCount = await db('checklist_items')
        .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
        .where('checklist_sections.template_id', tmpl.id)
        .count('checklist_items.id as total')
        .first();
      tmpl.total_items = Number(itemsCount.total);
    }

    res.json({
      success: true,
      data: templates
    });
  } catch (error) {
    next(error);
  }
};

const getOne = async (req, res, next) => {
  try {
    const { id } = req.params;

    const template = await db('checklist_templates')
      .join('sarfas_categories', 'checklist_templates.category_id', 'sarfas_categories.id')
      .where('checklist_templates.id', id)
      .select(
        'checklist_templates.*',
        'sarfas_categories.name as category_name',
        'sarfas_categories.code as category_code'
      )
      .first();

    if (!template) {
      return res.status(404).json({ success: false, message: 'Template checklist tidak ditemukan.' });
    }

    const sections = await db('checklist_sections')
      .where('template_id', id)
      .orderBy('sort_order', 'asc');

    for (let sec of sections) {
      sec.items = await db('checklist_items')
        .where('section_id', sec.id)
        .orderBy('sort_order', 'asc');
    }

    template.sections = sections;

    res.json({
      success: true,
      data: template
    });
  } catch (error) {
    next(error);
  }
};

const create = async (req, res, next) => {
  const trx = await db.transaction();
  try {
    const { template_code, title, category_id, inspection_frequency, description, sections } = req.body;

    if (!template_code || !title || !category_id) {
      await trx.rollback();
      return res.status(400).json({ success: false, message: 'Kode, Judul, dan Kategori Template wajib diisi.' });
    }

    const [tmplId] = await trx('checklist_templates').insert({
      template_code,
      title,
      category_id,
      inspection_frequency: inspection_frequency || 'BULANAN',
      description: description || null,
      version: '1.0',
      is_active: true
    });

    if (sections && Array.isArray(sections)) {
      for (let sIdx = 0; sIdx < sections.length; sIdx++) {
        const sec = sections[sIdx];
        const [secId] = await trx('checklist_sections').insert({
          template_id: tmplId,
          title: sec.title,
          sort_order: sIdx + 1,
          description: sec.description || null
        });

        if (sec.items && Array.isArray(sec.items)) {
          for (let iIdx = 0; iIdx < sec.items.length; iIdx++) {
            const item = sec.items[iIdx];
            await trx('checklist_items').insert({
              section_id: secId,
              item_code: item.item_code || `ITM-${sIdx+1}-${iIdx+1}`,
              question_text: item.question_text,
              standard_criteria: item.standard_criteria || null,
              is_critical: Boolean(item.is_critical),
              requires_photo: Boolean(item.requires_photo),
              sort_order: iIdx + 1
            });
          }
        }
      }
    }

    await trx.commit();
    await logAudit(req, 'CREATE_CHECKLIST_TEMPLATE', 'MASTER_CHECKLIST', tmplId, `Membuat master template checklist ${template_code}`);

    res.status(201).json({
      success: true,
      message: 'Template checklist berhasil dibuat.',
      data: { id: tmplId }
    });
  } catch (error) {
    await trx.rollback();
    next(error);
  }
};

module.exports = {
  list,
  getOne,
  create
};
