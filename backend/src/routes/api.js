const express = require('express');
const router = express.Router();

const { authenticateToken, authorize } = require('../middleware/auth');

const authController = require('../controllers/authController');
const dashboardController = require('../controllers/dashboardController');
const sarfasController = require('../controllers/sarfasController');
const checklistTemplateController = require('../controllers/checklistTemplateController');
const inspectionController = require('../controllers/inspectionController');
const findingController = require('../controllers/findingController');
const scheduleController = require('../controllers/scheduleController');
const reportController = require('../controllers/reportController');
const userController = require('../controllers/userController');
const auditLogController = require('../controllers/auditLogController');

// 1. Authentication Endpoints
router.post('/auth/login', authController.login);
router.post('/auth/logout', authenticateToken, authController.logout);
router.get('/auth/me', authenticateToken, authController.me);
router.get('/auth/demo-accounts', authController.getDemoAccounts);

// 2. Dashboard Endpoints
router.get('/dashboard/summary', authenticateToken, dashboardController.getSummary);
router.get('/dashboard/inspection-trends', authenticateToken, dashboardController.getTrends);

// 3. Sarfas Endpoints
router.get('/sarfas/meta', authenticateToken, sarfasController.getMeta);
router.get('/sarfas', authenticateToken, sarfasController.list);
router.get('/sarfas/:id', authenticateToken, sarfasController.getOne);
router.post('/sarfas', authenticateToken, authorize(['super_admin', 'admin_hsse']), sarfasController.create);
router.patch('/sarfas/:id', authenticateToken, authorize(['super_admin', 'admin_hsse']), sarfasController.update);

// 4. Master Checklist Templates Endpoints
router.get('/checklist-templates', authenticateToken, checklistTemplateController.list);
router.get('/checklist-templates/:id', authenticateToken, checklistTemplateController.getOne);
router.post('/checklist-templates', authenticateToken, authorize(['super_admin', 'admin_hsse']), checklistTemplateController.create);

// 5. Inspection Endpoints
router.get('/inspections', authenticateToken, inspectionController.list);
router.get('/inspections/:id', authenticateToken, inspectionController.getOne);
router.post('/inspections/draft', authenticateToken, authorize(['super_admin', 'admin_hsse', 'inspektor']), inspectionController.saveDraft);
router.post('/inspections/:id/draft', authenticateToken, authorize(['super_admin', 'admin_hsse', 'inspektor']), inspectionController.saveDraft);
router.put('/inspections/:id', authenticateToken, authorize(['super_admin', 'admin_hsse', 'inspektor']), inspectionController.saveDraft);
router.post('/inspections/:id/submit', authenticateToken, authorize(['super_admin', 'admin_hsse', 'inspektor']), inspectionController.submit);
router.post('/inspections/:id/review', authenticateToken, authorize(['super_admin', 'admin_hsse', 'approver']), inspectionController.review);

// 6. Findings & Follow-up Endpoints
router.get('/findings', authenticateToken, findingController.list);
router.get('/findings/:id', authenticateToken, findingController.getOne);
router.post('/findings/:id/actions', authenticateToken, findingController.updateAction);
router.post('/findings/:id/verify', authenticateToken, authorize(['super_admin', 'admin_hsse', 'approver']), findingController.verifyClose);

// 7. Inspection Schedules Endpoints
router.get('/inspection-schedules', authenticateToken, scheduleController.list);
router.post('/inspection-schedules', authenticateToken, authorize(['super_admin', 'admin_hsse']), scheduleController.create);
router.patch('/inspection-schedules/:id', authenticateToken, authorize(['super_admin', 'admin_hsse']), scheduleController.update);

// 8. Reports Endpoints
router.get('/reports/inspections', authenticateToken, reportController.getReportData);

// 9. User Management Endpoints
router.get('/users', authenticateToken, authorize(['super_admin', 'admin_hsse']), userController.list);
router.post('/users', authenticateToken, authorize(['super_admin']), userController.create);
router.patch('/users/:id', authenticateToken, authorize(['super_admin']), userController.update);
router.put('/users/:id', authenticateToken, authorize(['super_admin']), userController.update);

// 10. Audit Logs Endpoints
router.get('/audit-logs', authenticateToken, authorize(['super_admin', 'admin_hsse']), auditLogController.list);

module.exports = router;
