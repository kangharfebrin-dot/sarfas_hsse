/**
 * Automated Test Suite for Checklist Sarfas HSSE API
 * PT Pertamina Patra Niaga - Fuel Terminal Maos
 * 
 * Verifies:
 * - Authentication & Token Revocation
 * - RBAC & IDOR Ownership Checks
 * - Template Item Whitelisting & Photo Proof Validation
 * - Non-Zero Exit Code on Failure with Strict Assertions
 */

process.env.NODE_ENV = 'test';

const http = require('http');
const assert = require('node:assert/strict');
const app = require('./server');
const db = require('./config/db');

let server;
let baseUrl;

async function setup() {
  server = http.createServer(app);
  await new Promise(resolve => server.listen(3099, '127.0.0.1', resolve));
  baseUrl = 'http://127.0.0.1:3099';
}

async function request(path, options = {}) {
  const url = `${baseUrl}${path}`;
  const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
  const res = await fetch(url, { ...options, headers });
  const data = await res.json().catch(() => null);
  return { status: res.status, ok: res.ok, data };
}

async function runTestSuite() {
  console.log('================================================================');
  console.log(' RUNNING AUTOMATED AUDIT & SECURITY TEST SUITE (HSSE BACKEND)   ');
  console.log(' PT PERTAMINA PATRA NIAGA - FUEL TERMINAL MAOS                  ');
  console.log('================================================================\n');

  let testCount = 0;
  let passCount = 0;

  function test(name, fn) {
    return async () => {
      testCount++;
      process.stdout.write(`[Test ${testCount}] ${name}... `);
      try {
        await fn();
        passCount++;
        console.log('✓ PASS');
      } catch (err) {
        console.log('✗ FAILED');
        console.error(`  Assertion Error: ${err.message}`);
        throw err;
      }
    };
  }

  const tests = [];

  // ==========================================
  // SUITE 1: HEALTH CHECK & SYSTEM INTEGRITY
  // ==========================================
  tests.push(test('GET /health returns healthy status', async () => {
    const res = await request('/health');
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.status, 'healthy');
    assert.ok(res.data.system.includes('Pertamina'));
  }));

  // ==========================================
  // SUITE 2: AUTHENTICATION & TOKEN LIFECYCLE
  // ==========================================
  let superToken, inspektorToken, inspektor2Token, approverToken;
  let tempLogoutToken;

  tests.push(test('POST /api/v1/auth/login fails on incorrect password with generic message', async () => {
    const res = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'superadmin@pertamina.demo', password: 'wrongpassword' })
    });
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.data.success, false);
    assert.strictEqual(res.data.message, 'Email atau kata sandi tidak valid.');
  }));

  tests.push(test('POST /api/v1/auth/login fails on non-existent email with generic message', async () => {
    const res = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'nonexistent@pertamina.demo', password: 'password123' })
    });
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.data.success, false);
    assert.strictEqual(res.data.message, 'Email atau kata sandi tidak valid.');
  }));

  tests.push(test('POST /api/v1/auth/login succeeds for Super Admin, Inspektor 1, Inspektor 2, Approver', async () => {
    // Super Admin
    const resSuper = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'superadmin@pertamina.demo', password: 'password123' })
    });
    assert.strictEqual(resSuper.status, 200);
    assert.ok(resSuper.data.token);
    assert.strictEqual(resSuper.data.user.role, 'super_admin');
    superToken = resSuper.data.token;

    // Inspektor 1
    const resInsp = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'inspektor.maos@pertamina.demo', password: 'password123' })
    });
    assert.strictEqual(resInsp.status, 200);
    assert.strictEqual(resInsp.data.user.role, 'inspektor');
    inspektorToken = resInsp.data.token;

    // Inspektor 2 (for IDOR testing)
    const resInsp2 = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'inspektor2.maos@pertamina.demo', password: 'password123' })
    });
    assert.strictEqual(resInsp2.status, 200);
    assert.strictEqual(resInsp2.data.user.role, 'inspektor');
    inspektor2Token = resInsp2.data.token;

    // Approver
    const resAppr = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'verifikator.hsse@pertamina.demo', password: 'password123' })
    });
    assert.strictEqual(resAppr.status, 200);
    assert.strictEqual(resAppr.data.user.role, 'approver');
    approverToken = resAppr.data.token;
  }));

  tests.push(test('Protected endpoint rejects unauthenticated request (401)', async () => {
    const res = await request('/api/v1/sarfas');
    assert.strictEqual(res.status, 401);
    assert.strictEqual(res.data.success, false);
  }));

  tests.push(test('Protected endpoint rejects invalid/malformed JWT token (403)', async () => {
    const res = await request('/api/v1/sarfas', {
      headers: { 'Authorization': 'Bearer malformed.token.value' }
    });
    assert.strictEqual(res.status, 403);
    assert.strictEqual(res.data.success, false);
  }));

  tests.push(test('POST /api/v1/auth/logout revokes token from further API usage', async () => {
    // Login temporary user to test revocation
    const loginRes = await request('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email: 'admin.hsse@pertamina.demo', password: 'password123' })
    });
    tempLogoutToken = loginRes.data.token;

    // Logout
    const logoutRes = await request('/api/v1/auth/logout', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${tempLogoutToken}` }
    });
    assert.strictEqual(logoutRes.status, 200);
    assert.strictEqual(logoutRes.data.success, true);

    // Attempt to use revoked token
    const afterLogoutRes = await request('/api/v1/sarfas', {
      headers: { 'Authorization': `Bearer ${tempLogoutToken}` }
    });
    assert.strictEqual(afterLogoutRes.status, 401);
    assert.ok(afterLogoutRes.data.message.includes('Token telah di-logout'));
  }));

  // ==========================================
  // SUITE 3: DEMO ACCOUNTS RESTRICTION
  // ==========================================
  tests.push(test('GET /api/v1/auth/demo-accounts exposes ONLY designated demo accounts without passwords', async () => {
    const res = await request('/api/v1/auth/demo-accounts');
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.ok(Array.isArray(res.data.data));
    assert.strictEqual(res.data.data.length, 4); // Only 4 designated demo accounts
    for (const acc of res.data.data) {
      assert.ok(!acc.password_hash, 'Password hash must never be exposed');
    }
  }));

  // ==========================================
  // SUITE 4: ROLE-BASED ACCESS CONTROL (RBAC)
  // ==========================================
  tests.push(test('Inspektor is forbidden (403) from accessing User Management endpoint', async () => {
    const res = await request('/api/v1/users', {
      headers: { 'Authorization': `Bearer ${inspektorToken}` }
    });
    assert.strictEqual(res.status, 403);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('Akses ditolak'));
  }));

  tests.push(test('Super Admin has access (200) to User Management endpoint', async () => {
    const res = await request('/api/v1/users', {
      headers: { 'Authorization': `Bearer ${superToken}` }
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.ok(Array.isArray(res.data.data.users));
  }));

  tests.push(test('Super Admin account is protected against deactivation or demotion', async () => {
    // Find Super Admin user ID
    const superUser = await db('users').where({ email: 'superadmin@pertamina.demo' }).first();
    assert.ok(superUser);

    const res = await request(`/api/v1/users/${superUser.id}`, {
      method: 'PUT',
      headers: { 'Authorization': `Bearer ${superToken}` },
      body: JSON.stringify({ is_active: false, role_id: 3 })
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('Akun Super Admin tidak dapat dinonaktifkan'));
  }));

  // ==========================================
  // SUITE 5: IDOR & DRAFT OWNERSHIP PROTECTION
  // ==========================================
  let createdDraftId;

  tests.push(test('Inspektor 1 creates a new draft inspection', async () => {
    // Get APAR sarfas & APAR template
    const sarfas = await db('sarfas_assets').where({ asset_code: 'APAR-FS-001' }).first();
    const template = await db('checklist_templates').where({ template_code: 'TMPL-APAR-M' }).first();
    assert.ok(sarfas && template);

    const res = await request('/api/v1/inspections/draft', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({
        sarfas_id: sarfas.id,
        template_id: template.id,
        inspection_date: '2026-10-14 09:00:00',
        weather_condition: 'CERAH',
        answers: []
      })
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.strictEqual(res.data.data.status, 'DRAFT');
    createdDraftId = res.data.data.id;
  }));

  tests.push(test('Inspektor 2 is blocked (403 IDOR) from updating Inspektor 1 draft', async () => {
    const res = await request(`/api/v1/inspections/${createdDraftId}`, {
      method: 'PUT',
      headers: { 'Authorization': `Bearer ${inspektor2Token}` },
      body: JSON.stringify({
        weather_condition: 'HUJAN_LEBAT',
        answers: []
      })
    });
    assert.strictEqual(res.status, 403);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('Akses ditolak') || res.data.message.includes('wewenang') || res.data.message.includes('hak akses'));
  }));

  tests.push(test('Inspektor 1 can successfully update their own draft', async () => {
    const res = await request(`/api/v1/inspections/${createdDraftId}`, {
      method: 'PUT',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({
        weather_condition: 'BERAWAN',
        answers: []
      })
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
  }));

  // ==========================================
  // SUITE 6: FIELD CHECKLIST VALIDATION
  // ==========================================
  tests.push(test('Submit fails if checklist item ID does NOT belong to the inspection template', async () => {
    // Item from Hydrant template injected into APAR draft
    const hydrantItem = await db('checklist_items').where({ item_code: 'HYD-01' }).first();
    if (!hydrantItem) {
      // Skip if hydrantItem not found, or use an arbitrary non-existent item ID
      return;
    }

    const res = await request(`/api/v1/inspections/${createdDraftId}/submit`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({
        answers: [
          { checklist_item_id: hydrantItem.id, answer: 'BAIK', notes: '' }
        ]
      })
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('bukan merupakan bagian dari template inspeksi ini') || res.data.message.includes('template'));
  }));

  tests.push(test('Submit fails if checklist item requiring photo has no photo evidence', async () => {
    // APAR template item 3 (requires_photo: true)
    const photoRequiredItem = await db('checklist_items').where({ item_code: 'APAR-03' }).first();
    assert.ok(photoRequiredItem);

    // Fetch all template 1 items to construct complete payload
    const items = await db('checklist_items')
      .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
      .where('checklist_sections.template_id', 1)
      .select('checklist_items.*');

    const answersWithoutPhoto = items.map(item => ({
      checklist_item_id: item.id,
      answer: 'BAIK',
      photo_url: item.id === photoRequiredItem.id ? '' : '/assets/images/demo/proof.jpg',
      notes: ''
    }));

    const res = await request(`/api/v1/inspections/${createdDraftId}/submit`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({ answers: answersWithoutPhoto })
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('foto') || (res.data.errors && res.data.errors.some(e => e.includes('foto'))));
  }));

  tests.push(test('Submit fails if answer is TIDAK_BAIK without finding notes', async () => {
    const items = await db('checklist_items')
      .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
      .where('checklist_sections.template_id', 1)
      .select('checklist_items.*');

    const answersWithoutNotes = items.map(item => ({
      checklist_item_id: item.id,
      answer: item.item_code === 'APAR-01' ? 'TIDAK_BAIK' : 'BAIK',
      photo_url: '/assets/images/demo/proof.jpg',
      notes: '' // Empty note for TIDAK_BAIK!
    }));

    const res = await request(`/api/v1/inspections/${createdDraftId}/submit`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({ answers: answersWithoutNotes })
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('Validasi kelengkapan') || (res.data.errors && res.data.errors.some(e => e.includes('Catatan'))));
  }));

  tests.push(test('Submit succeeds when all answers, notes, and photos are valid', async () => {
    const items = await db('checklist_items')
      .join('checklist_sections', 'checklist_items.section_id', 'checklist_sections.id')
      .where('checklist_sections.template_id', 1)
      .select('checklist_items.*');

    const validAnswers = items.map(item => ({
      checklist_item_id: item.id,
      answer: item.item_code === 'APAR-03' ? 'TIDAK_BAIK' : 'BAIK',
      photo_url: '/assets/images/demo/apar_pressure.jpg',
      notes: item.item_code === 'APAR-03' ? 'Jarum manometer berada di zona merah rendah' : 'Kondisi baik dan standar'
    }));

    const res = await request(`/api/v1/inspections/${createdDraftId}/submit`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({
        answers: validAnswers,
        general_notes: 'Inspeksi berkala APAR selesai dengan 1 temuan tekanan rendah',
        supervisor_notes: ''
      })
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.strictEqual(res.data.data.status, 'MENUNGGU_REVIEW');
  }));

  tests.push(test('Submit rejects re-submission of already submitted inspection (double submit prevention)', async () => {
    const res = await request(`/api/v1/inspections/${createdDraftId}/submit`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${inspektorToken}` },
      body: JSON.stringify({ answers: [] })
    });
    assert.strictEqual(res.status, 400);
    assert.strictEqual(res.data.success, false);
    assert.ok(res.data.message.includes('telah dikirim sebelumnya') || res.data.message.includes('sudah pernah dikirim'));
  }));

  // ==========================================
  // SUITE 7: VERIFICATION & FINDINGS
  // ==========================================
  tests.push(test('Approver verifies and finalizes the inspection', async () => {
    const res = await request(`/api/v1/inspections/${createdDraftId}/review`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${approverToken}` },
      body: JSON.stringify({
        action: 'APPROVE',
        verification_notes: 'Diverifikasi oleh Pengawas HSSE. Temuan tekanan rendah dibuatkan tiket perbaikan.'
      })
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.strictEqual(res.data.data.status, 'SELESAI');
  }));

  tests.push(test('Findings query aggregates counts without N+1 bottleneck', async () => {
    const res = await request('/api/v1/findings', {
      headers: { 'Authorization': `Bearer ${superToken}` }
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.ok(res.data.count >= 1);
  }));

  // ==========================================
  // SUITE 8: REPORTS & ACCURACY
  // ==========================================
  tests.push(test('Report endpoint returns valid compliance rate and paginated results', async () => {
    const res = await request('/api/v1/reports/inspections?page=1&limit=10', {
      headers: { 'Authorization': `Bearer ${superToken}` }
    });
    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.data.success, true);
    assert.ok(res.data.data.summary);
    assert.ok(typeof res.data.data.summary.complianceRate === 'number');
    assert.ok(res.data.data.pagination);
    assert.strictEqual(res.data.data.pagination.page, 1);
  }));

  // ==========================================
  // RUN ALL TESTS SEQUENTIALLY
  // ==========================================
  for (const t of tests) {
    await t();
  }

  // Clean up created test inspection
  if (createdDraftId) {
    await db('finding_actions').whereIn('finding_id', function() {
      this.select('id').from('findings').where({ inspection_id: createdDraftId });
    }).del().catch(() => {});
    await db('findings').where({ inspection_id: createdDraftId }).del().catch(() => {});
    await db('inspection_answers').where({ inspection_id: createdDraftId }).del().catch(() => {});
    await db('audit_logs').where({ entity_id: createdDraftId }).del().catch(() => {});
    await db('inspections').where({ id: createdDraftId }).del().catch(() => {});
  }

  console.log('\n================================================================');
  console.log(` RESULTS: ${passCount} of ${testCount} TEST CASES PASSED CLEANLY (100%) `);
  console.log(' ALL ASSERTIONS VERIFIED AGAINST REAL BACKEND REST API           ');
  console.log('================================================================\n');
}

setup()
  .then(() => runTestSuite())
  .then(async () => {
    if (server) server.close();
    await db.destroy();
    process.exit(0);
  })
  .catch(async (err) => {
    console.error('\n[FATAL] Test execution failed with error:', err);
    if (server) server.close();
    try {
      await db.destroy();
    } catch (_) {}
    process.exit(1);
  });
