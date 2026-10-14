/**
 * @param { import("knex").Knex } knex
 * @returns { Promise<void> }
 */
exports.up = async function(knex) {
  // 1. Roles table
  await knex.schema.createTable('roles', (table) => {
    table.increments('id').primary();
    table.string('name', 50).notNullable();
    table.string('slug', 50).notNullable().unique();
    table.text('description').nullable();
    table.timestamps(true, true);
  });

  // 2. Users table
  await knex.schema.createTable('users', (table) => {
    table.increments('id').primary();
    table.string('name', 100).notNullable();
    table.string('email', 100).notNullable().unique();
    table.string('password_hash', 255).notNullable();
    table.integer('role_id').unsigned().notNullable().references('id').inTable('roles').onDelete('RESTRICT');
    table.string('phone', 30).nullable();
    table.string('jabatan', 100).nullable();
    table.string('avatar', 255).nullable();
    table.boolean('is_active').defaultTo(true);
    table.timestamps(true, true);
  });

  // 3. Locations table (Fuel Terminal Maos areas)
  await knex.schema.createTable('locations', (table) => {
    table.increments('id').primary();
    table.string('code', 20).notNullable().unique();
    table.string('name', 100).notNullable();
    table.text('description').nullable();
    table.boolean('is_active').defaultTo(true);
    table.timestamps(true, true);
  });

  // 4. Sarfas Categories table
  await knex.schema.createTable('sarfas_categories', (table) => {
    table.increments('id').primary();
    table.string('code', 20).notNullable().unique();
    table.string('name', 100).notNullable();
    table.text('description').nullable();
    table.string('icon', 50).nullable();
    table.boolean('is_active').defaultTo(true);
    table.timestamps(true, true);
  });

  // 5. Sarfas Assets table
  await knex.schema.createTable('sarfas_assets', (table) => {
    table.increments('id').primary();
    table.string('asset_code', 50).notNullable().unique();
    table.string('name', 150).notNullable();
    table.integer('category_id').unsigned().notNullable().references('id').inTable('sarfas_categories').onDelete('RESTRICT');
    table.integer('location_id').unsigned().notNullable().references('id').inTable('locations').onDelete('RESTRICT');
    table.string('serial_number', 100).nullable();
    table.string('brand_model', 100).nullable();
    table.string('capacity_specs', 100).nullable();
    table.date('installation_date').nullable();
    table.datetime('last_inspection_date').nullable();
    table.datetime('next_inspection_date').nullable();
    table.enum('condition_status', ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT', 'PERLU_PERHATIAN']).defaultTo('BAIK');
    table.boolean('is_active').defaultTo(true);
    table.string('photo_url', 255).nullable();
    table.text('notes').nullable();
    table.timestamps(true, true);

    table.index(['asset_code', 'category_id', 'location_id', 'condition_status']);
  });

  // 6. Checklist Templates table
  await knex.schema.createTable('checklist_templates', (table) => {
    table.increments('id').primary();
    table.string('template_code', 50).notNullable().unique();
    table.string('title', 150).notNullable();
    table.integer('category_id').unsigned().notNullable().references('id').inTable('sarfas_categories').onDelete('RESTRICT');
    table.enum('inspection_frequency', ['HARIAN', 'MINGGUAN', 'BULANAN', 'TRIWULAN', 'SEMESTER', 'TAHUNAN']).defaultTo('BULANAN');
    table.text('description').nullable();
    table.string('version', 20).defaultTo('1.0');
    table.boolean('is_active').defaultTo(true);
    table.timestamps(true, true);
  });

  // 7. Checklist Sections table
  await knex.schema.createTable('checklist_sections', (table) => {
    table.increments('id').primary();
    table.integer('template_id').unsigned().notNullable().references('id').inTable('checklist_templates').onDelete('CASCADE');
    table.string('title', 150).notNullable();
    table.integer('sort_order').defaultTo(1);
    table.text('description').nullable();
    table.timestamps(true, true);
  });

  // 8. Checklist Items table
  await knex.schema.createTable('checklist_items', (table) => {
    table.increments('id').primary();
    table.integer('section_id').unsigned().notNullable().references('id').inTable('checklist_sections').onDelete('CASCADE');
    table.string('item_code', 30).notNullable();
    table.text('question_text').notNullable();
    table.text('standard_criteria').nullable();
    table.boolean('is_critical').defaultTo(false);
    table.boolean('requires_photo').defaultTo(false);
    table.integer('sort_order').defaultTo(1);
    table.timestamps(true, true);
  });

  // 9. Inspection Schedules table
  await knex.schema.createTable('inspection_schedules', (table) => {
    table.increments('id').primary();
    table.string('title', 150).notNullable();
    table.integer('sarfas_id').unsigned().notNullable().references('id').inTable('sarfas_assets').onDelete('CASCADE');
    table.integer('template_id').unsigned().notNullable().references('id').inTable('checklist_templates').onDelete('CASCADE');
    table.integer('assigned_user_id').unsigned().notNullable().references('id').inTable('users').onDelete('RESTRICT');
    table.date('scheduled_date').notNullable();
    table.enum('frequency', ['HARIAN', 'MINGGUAN', 'BULANAN', 'TRIWULAN', 'SEMESTER', 'TAHUNAN']).defaultTo('BULANAN');
    table.enum('status', ['TERJADWAL', 'SELESAI', 'TERLAMBAT', 'DIBATALKAN']).defaultTo('TERJADWAL');
    table.text('notes').nullable();
    table.timestamps(true, true);
  });

  // 10. Inspections table
  await knex.schema.createTable('inspections', (table) => {
    table.increments('id').primary();
    table.string('inspection_no', 50).notNullable().unique();
    table.integer('sarfas_id').unsigned().notNullable().references('id').inTable('sarfas_assets').onDelete('RESTRICT');
    table.integer('template_id').unsigned().notNullable().references('id').inTable('checklist_templates').onDelete('RESTRICT');
    table.integer('inspector_id').unsigned().notNullable().references('id').inTable('users').onDelete('RESTRICT');
    table.date('inspection_date').notNullable();
    table.enum('status', ['DRAFT', 'SEDANG_BERLANGSUNG', 'MENUNGGU_REVIEW', 'SELESAI', 'DITOLAK']).defaultTo('DRAFT');
    table.enum('summary_condition', ['BAIK', 'PERLU_TINDAK_LANJUT', 'KRITIS']).defaultTo('BAIK');
    table.text('general_notes').nullable();
    table.text('inspector_signature').nullable();
    table.integer('verified_by').unsigned().nullable().references('id').inTable('users').onDelete('SET NULL');
    table.datetime('verified_at').nullable();
    table.text('verification_notes').nullable();
    table.timestamps(true, true);

    table.index(['inspection_no', 'status', 'inspection_date']);
  });

  // 11. Inspection Answers table
  await knex.schema.createTable('inspection_answers', (table) => {
    table.increments('id').primary();
    table.integer('inspection_id').unsigned().notNullable().references('id').inTable('inspections').onDelete('CASCADE');
    table.integer('checklist_item_id').unsigned().notNullable().references('id').inTable('checklist_items').onDelete('RESTRICT');
    table.enum('answer', ['BAIK', 'TIDAK_BAIK', 'NA']).defaultTo('BAIK');
    table.text('notes').nullable();
    table.string('photo_url', 255).nullable();
    table.enum('risk_level', ['RENDAH', 'SEDANG', 'TINGGI', 'KRITIS']).nullable();
    table.timestamps(true, true);

    table.unique(['inspection_id', 'checklist_item_id']);
  });

  // 12. Findings table
  await knex.schema.createTable('findings', (table) => {
    table.increments('id').primary();
    table.string('finding_no', 50).notNullable().unique();
    table.integer('inspection_id').unsigned().nullable().references('id').inTable('inspections').onDelete('SET NULL');
    table.integer('sarfas_id').unsigned().notNullable().references('id').inTable('sarfas_assets').onDelete('RESTRICT');
    table.integer('checklist_item_id').unsigned().nullable().references('id').inTable('checklist_items').onDelete('SET NULL');
    table.string('title', 200).notNullable();
    table.text('description').notNullable();
    table.enum('risk_level', ['RENDAH', 'SEDANG', 'TINGGI', 'KRITIS']).defaultTo('SEDANG');
    table.enum('status', ['BARU', 'DALAM_TINDAK_LANJUT', 'MENUNGGU_VERIFIKASI', 'SELESAI', 'DITOLAK']).defaultTo('BARU');
    table.integer('reported_by').unsigned().notNullable().references('id').inTable('users').onDelete('RESTRICT');
    table.integer('assigned_to').unsigned().nullable().references('id').inTable('users').onDelete('SET NULL');
    table.date('due_date').nullable();
    table.string('photo_before', 255).nullable();
    table.string('photo_after', 255).nullable();
    table.text('corrective_action_plan').nullable();
    table.text('corrective_action_notes').nullable();
    table.integer('verified_by').unsigned().nullable().references('id').inTable('users').onDelete('SET NULL');
    table.datetime('verified_at').nullable();
    table.text('verification_notes').nullable();
    table.timestamps(true, true);

    table.index(['finding_no', 'risk_level', 'status']);
  });

  // 13. Finding Actions Log table
  await knex.schema.createTable('finding_actions', (table) => {
    table.increments('id').primary();
    table.integer('finding_id').unsigned().notNullable().references('id').inTable('findings').onDelete('CASCADE');
    table.integer('user_id').unsigned().notNullable().references('id').inTable('users').onDelete('RESTRICT');
    table.string('action_type', 50).notNullable();
    table.text('notes').nullable();
    table.string('photo_proof', 255).nullable();
    table.datetime('created_at').defaultTo(knex.fn.now());
  });

  // 14. Audit Logs table
  await knex.schema.createTable('audit_logs', (table) => {
    table.increments('id').primary();
    table.integer('user_id').unsigned().nullable().references('id').inTable('users').onDelete('SET NULL');
    table.string('user_name', 100).nullable();
    table.string('action', 100).notNullable();
    table.string('module', 50).notNullable();
    table.string('record_id', 50).nullable();
    table.text('details').nullable();
    table.string('ip_address', 50).nullable();
    table.datetime('created_at').defaultTo(knex.fn.now());
  });
};

/**
 * @param { import("knex").Knex } knex
 * @returns { Promise<void> }
 */
exports.down = async function(knex) {
  await knex.schema.dropTableIfExists('audit_logs');
  await knex.schema.dropTableIfExists('finding_actions');
  await knex.schema.dropTableIfExists('findings');
  await knex.schema.dropTableIfExists('inspection_answers');
  await knex.schema.dropTableIfExists('inspections');
  await knex.schema.dropTableIfExists('inspection_schedules');
  await knex.schema.dropTableIfExists('checklist_items');
  await knex.schema.dropTableIfExists('checklist_sections');
  await knex.schema.dropTableIfExists('checklist_templates');
  await knex.schema.dropTableIfExists('sarfas_assets');
  await knex.schema.dropTableIfExists('sarfas_categories');
  await knex.schema.dropTableIfExists('locations');
  await knex.schema.dropTableIfExists('users');
  await knex.schema.dropTableIfExists('roles');
};
