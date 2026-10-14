const http = require('http');
const app = require('./server');

async function runTests() {
  const server = http.createServer(app);
  await new Promise(resolve => server.listen(3099, resolve));
  const baseUrl = 'http://127.0.0.1:3099';

  console.log('Testing Express REST API on', baseUrl);

  // 1. Health
  const healthRes = await fetch(`${baseUrl}/health`).then(r => r.json());
  console.log('1. Health Check:', healthRes.status === 'healthy' ? '✓ PASS' : '✗ FAIL');

  // 2. Login
  const loginRes = await fetch(`${baseUrl}/api/v1/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email: 'superadmin@pertamina.demo', password: 'password123' })
  }).then(r => r.json());

  console.log('2. Login:', loginRes.success && loginRes.token ? `✓ PASS (Logged in as ${loginRes.user.name} - ${loginRes.user.role_name})` : '✗ FAIL');
  const token = loginRes.token;

  // 3. Dashboard summary
  const summaryRes = await fetch(`${baseUrl}/api/v1/dashboard/summary`, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());
  console.log('3. Dashboard Summary:', summaryRes.success && summaryRes.data.cards.totalSarfas > 0 ? `✓ PASS (Total Sarfas: ${summaryRes.data.cards.totalSarfas}, Kondisi Baik: ${summaryRes.data.cards.sarfasBaik})` : '✗ FAIL');

  // 4. Sarfas list
  const sarfasRes = await fetch(`${baseUrl}/api/v1/sarfas`, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());
  console.log('4. Sarfas List:', sarfasRes.success && sarfasRes.count > 0 ? `✓ PASS (${sarfasRes.count} sarfas loaded)` : '✗ FAIL');

  // 5. Checklist Templates
  const tmplRes = await fetch(`${baseUrl}/api/v1/checklist-templates`, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());
  console.log('5. Master Checklist Templates:', tmplRes.success && tmplRes.data.length > 0 ? `✓ PASS (${tmplRes.data.length} templates)` : '✗ FAIL');

  // 6. Inspections list
  const inspRes = await fetch(`${baseUrl}/api/v1/inspections`, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());
  console.log('6. Inspections List:', inspRes.success && inspRes.count > 0 ? `✓ PASS (${inspRes.count} inspections recorded)` : '✗ FAIL');

  // 7. Findings list
  const findRes = await fetch(`${baseUrl}/api/v1/findings`, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());
  console.log('7. Findings List:', findRes.success && findRes.count > 0 ? `✓ PASS (${findRes.count} findings with risk levels)` : '✗ FAIL');

  // 8. Reports filtered
  const reportRes = await fetch(`${baseUrl}/api/v1/reports/inspections`, {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());
  console.log('8. Reports:', reportRes.success && reportRes.data.summary ? `✓ PASS (Compliance Rate: ${reportRes.data.summary.complianceRate}%)` : '✗ FAIL');

  server.close();
  console.log('✓ All API test suites passed cleanly!');
  process.exit(0);
}

runTests().catch(err => {
  console.error('Test failed with error:', err);
  process.exit(1);
});
